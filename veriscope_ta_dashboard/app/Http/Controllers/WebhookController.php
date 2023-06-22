<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\ContractsInstantiate;
use App\Events\ShyftSmartContractEvent;
use App\User;
use App\TrustAnchor;
use App\TrustAnchorUser;
use App\TrustAnchorUserAttestation;
use App\SmartContractEvent;
use App\TrustAnchorSetAttestationEvent;
use App\TrustAnchorAssociationCrypto;
use App\TrustAnchorUserCryptoAddress;
use App\SmartContractTransaction;
use App\SmartContractAttestation;
use App\Country;
use App\CryptoWalletAddress;
use App\CryptoWalletType;
use App\TrustAnchorExtraData;
use App\TrustAnchorExtraDataUnique;
use App\VerifiedTrustAnchor;
use App\TrustAnchorExtraDataUniqueValidation;
use App\LatestBlockEvents;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use RichardStyles\EloquentEncryption\EloquentEncryption;

class WebhookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('check.signature');
        $this->helper_url = env('HTTP_API_URL');
    }

    public function Hex2String($hex)
    {
        $string='';
        for ($i=2; $i < strlen($hex)-1; $i+=2) {
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }

    public function webhook_post_ta_data(Request $request)
    {
        Log::debug('WebhookController webhook_post_ta_data');

        $input = $request->all();
        Log::debug(print_r($input, true));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function webhook_request(Request $request)
    {
        Log::debug('WebhookController webhook_request');

        $input = $request->all();
        Log::debug(print_r($input, true));
        $data = $input['obj'];

        Log::debug($data);
        Log::debug('message');
        Log::debug($data['message']);

        Log::debug($data);

        if ($data['message'] === 'ta-ping') {
            //broadcast(new ContractsInstantiate($data));
            return response()->json(['message' => 'success'], 200);
        }

        if ($data['message'] === 'ta-set-key-value-pair') {
            broadcast(new ContractsInstantiate($data));
        }

        if ($data['message'] === 'tas-event') {
            $event = new SmartContractEvent();
            $event->event_type = 'tas-event';
            $event->payload = json_encode($data);
            $_data = $data['data'];
            $event->transaction_hash = $_data['transactionHash'];
            // $event->attestation_hash = $_data['returnValues']['attestationKeccak'];
            // $event->user_address = $_data['returnValues']['_identifiedAddress'];
            // $event->ta_address = $_data['returnValues']['msg_sender'];
            $event->event = $_data['event'];
            $event->save();

            $data_local = $data['data'];
            if ($data_local['event'] === 'EVT_setAttestation') {
                $returnValues = $data_local['returnValues'];
                $msg_sender_address = $returnValues['msg_sender'];
                $attestation_hash = $returnValues['attestationKeccak'];
                $identified_address = $returnValues['_identifiedAddress'];
                $public_data = $returnValues['_publicData_0'];
                $documents_matrix_encrypted = $returnValues['_documentsMatrixEncrypted_0'];
                $document_decrypt = $data_local['document_decrypt'];
                $availability_address_encrypted = $returnValues['_availabilityAddressEncrypted'];
                $jurisdiction = $returnValues['_jurisdiction'];

                $type = $data_local['type'];

                $attestation = SmartContractAttestation::firstOrCreate(['transaction_hash' => $_data['transactionHash']]);
                $attestation->ta_account = $msg_sender_address;
                $attestation->jurisdiction = $jurisdiction;
                $attestation->effective_time = $_data['returnValues']['_effectiveTime'];
                $attestation->expiry_time = $_data['returnValues']['_expiryTime'];
                $attestation->public_data = $public_data;
                $attestation->documents_matrix_encrypted = $documents_matrix_encrypted;
                $attestation->availability_address_encrypted = $availability_address_encrypted;
                $attestation->is_managed = $_data['returnValues']['_isManaged'];
                $attestation->attestation_hash = $attestation_hash;
                $attestation->user_account = $identified_address;
                $attestation->block_number = $data['data']['blockNumber'];



                $tracedata = $data_local['traceValues'];


                $attestation->public_data = $tracedata['public_data'];
                $attestation->public_data_decoded = $tracedata['public_data_decoded'];

                $attestation->documents_matrix_encrypted = $tracedata['documents_matrix_encrypted'];
                $attestation->documents_matrix_encrypted_decoded = $tracedata['documents_matrix_encrypted_decoded'];

                $attestation->availability_address_encrypted = $tracedata['availability_address_encrypted'];
                $attestation->availability_address_encrypted_decoded = $tracedata['availability_address_encrypted_decoded'];

                $attestation->version_code = $tracedata['version_code'];
                $attestation->coin_blockchain = $tracedata['coin_blockchain'];
                $attestation->coin_token = $tracedata['coin_token'];
                $attestation->coin_address = $tracedata['coin_address'];
                $attestation->coin_memo = $tracedata['coin_memo'];



                $attestation->save();
            }

            broadcast(new ShyftSmartContractEvent($data));
        }

        if ($data['message'] === 'tam-event') {
            $event = new SmartContractEvent();
            $event->event_type = 'tam-event';
            $event->payload = json_encode($data);
            $_data = $data['data'];
            $event->transaction_hash = $_data['transactionHash'];
            $event->event = $_data['event'];
            $event->save();

            $data_local = $data['data'];
            if ($data_local['event'] === 'EVT_verifyTrustAnchor') {
                $returnValues = $data_local['returnValues'];
                $account_address = $returnValues['trustAnchorAddress'];

                $ta = VerifiedTrustAnchor::firstOrCreate(['account_address' => $account_address]);
                $ta->block_number =  $data_local['blockNumber'];
                $ta->save();
            }

            broadcast(new ShyftSmartContractEvent($data));
        }

        if ($data['message'] === 'create-new-user-account') {

            if ($data['data'] == 'missingData') {
                broadcast(new ContractsInstantiate($data));
                return;
            }

            $userID = $data['user_id'];
            $newAccountList = array_column((array_column($data['data'], 'account')), 'address');
            $deleteResult = TrustAnchor::whereNotIn('account_address', $newAccountList)->delete();

            foreach ($data['data'] as $index => $value) {
                $accountAddress = $value['account']['address'];
                $eloquent_encryption = new EloquentEncryption();
                $encrypted = $eloquent_encryption->encrypt($value['account']['private_key']);
                $privateKeyEncrypt = bin2hex($encrypted);

                $ta = TrustAnchor::updateOrCreate([
                    'account_address' => $accountAddress
                ], [
                    'user_id' => $userID,
                    'ta_prefname' => $value['account']['prefname'],
                    'account_address' => $accountAddress,
                    'private_key_encrypt' => $privateKeyEncrypt,
                    'signature_hash' => $value['account']['signature_hash']['SignatureHash'],
                    'signature' => json_encode($value['account']['signature_hash']['Signature']),
                    'public_key' => $value['account']['public_key'],
                ]);

                $user = User::findOrFail($userID);
                $user->trustAnchor()->save($ta);
            }

            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'ta-is-verified') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'ta-reload-account') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'ta-event') {
            $event = new SmartContractEvent();
            $event->event_type = 'ta-event';
            $event->payload = json_encode($data);
            $event->save();
            broadcast(new ShyftSmartContractEvent($data));
        }
        if ($data['message'] === 'taed-event') {
            Log::debug('taed-event');
            $event = new SmartContractEvent();
            $event->event_type = 'taed-event';
            $event->payload = json_encode($data);
            $event->save();
            $data_local = $data['data'];

            if ($data_local['event'] === "EVT_setDataRetrievalParametersCreated") {
                $extra_data = new TrustAnchorExtraData();

                $extra_data->transaction_hash = $data_local['transactionHash'];
                $extra_data->block_number = $data_local['blockNumber'];
                $extra_data->trust_anchor_address = $data_local['returnValues']['_trustAnchorAddress'];
                $extra_data->endpoint_name = $data_local['returnValues']['_endpointName'];
                $extra_data->ipv4_address = $data_local['ipv4_address'];
                $extra_data->save();
            }

            broadcast(new ShyftSmartContractEvent($data));
        }
        if ($data['message'] === 'taedu-event') {
            Log::debug('taedu-event');
            $event = new SmartContractEvent();
            $event->event_type = 'taedu-event';
            $event->payload = json_encode($data);
            $event->save();
            $data_local = $data['data'];

            if ($data_local['event'] === "EVT_setTrustAnchorKeyValuePairCreated") {
                $extra_data = TrustAnchorExtraDataUnique::firstOrNew(['key_value_pair_name' => $data_local['returnValues']['_keyValuePairName'], 'trust_anchor_address' => $data_local['returnValues']['_trustAnchorAddress']]);

                if ($extra_data->block_number < $data_local['blockNumber']) {
                    $extra_data->transaction_hash = $data_local['transactionHash'];
                    $extra_data->block_number = $data_local['blockNumber'];
                    $extra_data->trust_anchor_address = $data_local['returnValues']['_trustAnchorAddress'];
                    $extra_data->key_value_pair_name = $data_local['returnValues']['_keyValuePairName'];
                    $extra_data->key_value_pair_value = $data_local['returnValues']['_keyValuePairValue'];
                    $extra_data->save();
                }
            }

            if ($data_local['event'] === "EVT_setTrustAnchorKeyValuePairUpdated") {
                $extra_data = TrustAnchorExtraDataUnique::firstOrNew(['key_value_pair_name' => $data_local['returnValues']['_keyValuePairName'], 'trust_anchor_address' => $data_local['returnValues']['_trustAnchorAddress']]);

                if ($extra_data->block_number < $data_local['blockNumber']) {
                    $extra_data->transaction_hash = $data_local['transactionHash'];
                    $extra_data->block_number = $data_local['blockNumber'];
                    $extra_data->trust_anchor_address = $data_local['returnValues']['_trustAnchorAddress'];
                    $extra_data->key_value_pair_name = $data_local['returnValues']['_keyValuePairName'];
                    $extra_data->key_value_pair_value = $data_local['returnValues']['_keyValuePairValue'];
                    $extra_data->save();
                }
            }

            if ($data_local['event'] === "EVT_setValidationForKeyValuePairData") {
                $extra_data = TrustAnchorExtraDataUniqueValidation::firstOrNew(['transaction_hash' => $data_local['transactionHash']]);

                $extra_data->transaction_hash = $data_local['transactionHash'];
                $extra_data->trust_anchor_address = $data_local['returnValues']['_trustAnchorAddress'];
                $extra_data->key_value_pair_name = $data_local['returnValues']['_keyValuePairName'];
                $extra_data->validator_address = $data_local['returnValues']['_validatorAddress'];
                $extra_data->save();
            }

            broadcast(new ShyftSmartContractEvent($data));
        }


        if ($data['message'] === 'ta-set-attestation') {
            $result = $data['data'];

            $ta = TrustAnchor::where('account_address', $result['ta_address'])->first();
            $tau = TrustAnchorUser::where('account_address', $result['user_address'])->first();

            $taua = new TrustAnchorUserAttestation();
            $taua->trust_anchor_id = $ta->id;
            $taua->trust_anchor_user_id = $tau->id;
            // resultAttestationKeccak replaced with transaction hash
            $taua->attestation_hash = $result['hash'];
            $taua->save();


            $ta->trustAnchorUserAttestation()->save($taua);
            $tau->trustAnchorUserAttestation()->save($taua);

            broadcast(new ContractsInstantiate($data));
        }

        if ($data['message'] === 'ta-set-attestation-error') {
            broadcast(new ContractsInstantiate($data));
        }

        if ($data['message'] === 'ta-get-balance') {
            broadcast(new ContractsInstantiate($data));
        }

        if ($data['message'] === 'get-validation-for-key-value-pair-data') {
            broadcast(new ContractsInstantiate($data));
        }



        if ($data['message'] === 'smart-contract-transaction') {
            Log::debug(print_r($data, true));

            $result = $data['data'];

            $transaction = SmartContractTransaction::firstOrNew(['transaction_hash' => $result['transaction']]);
            $transaction->save();
        }

        if ($data['message'] === 'get-smart-contract-transaction') {
            Log::debug('get-smart-contract-transaction');
            Log::debug(print_r($data, true));
            $result = $data['data'];
            $transaction = SmartContractTransaction::where('transaction_hash', $result['hash'])->first();
            if ($transaction) {
                $transaction->nonce = $result['nonce'];
                $transaction->block_hash = $result['blockHash'];
                $transaction->block_number = $result['blockNumber'];
                $transaction->transaction_index = $result['transactionIndex'];
                $transaction->from_address = $result['from'];
                $transaction->to_address = $result['to'];
                $transaction->value = $result['value'] / 1000000000000000000;
                $transaction->gas = $result['gas'];
                $transaction->gas_price = $result['gasPrice'];
                $transaction->payload = json_encode($result);

                $transaction->save();
            }
        }
        if ($data['message'] === 'get-latest-block-event') {
            Log::debug('get-latest-block-event');
            Log::debug(print_r($data, true));

            $latestBlockEvent = LatestBlockEvents::where('type', $data['data']['type'])->first();

            return response()->json(['message' => 'success', 'data' => $latestBlockEvent], 200);
        }

        return response()->json(['message' => 'success'], 200);
    }
}
