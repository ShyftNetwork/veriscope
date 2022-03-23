<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\{ContractsInstantiate, ShyftSmartContractEvent};
use App\{User, TrustAnchor,TrustAnchorUser, TrustAnchorUserAttestation, SmartContractEvent, TrustAnchorSetAttestationEvent, TrustAnchorAssociationCrypto, TrustAnchorUserCryptoAddress, SmartContractTransaction, SmartContractAttestation, Country, CryptoWalletAddress, CryptoWalletType, TrustAnchorExtraData, TrustAnchorExtraDataUnique, VerifiedTrustAnchor, TrustAnchorExtraDataUniqueValidation};
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

    function Hex2String($hex){
        $string='';
        for ($i=2; $i < strlen($hex)-1; $i+=2){
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

        if ($data['message'] === 'ta-set-unique-address') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'ta-get-unique-address') {
            broadcast(new ContractsInstantiate($data));
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
            if($data_local['event'] === 'EVT_setAttestation') {
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

                $attestation = new SmartContractAttestation();
                $attestation->ta_account = $msg_sender_address;
                $attestation->jurisdiction = $jurisdiction;
                $attestation->effective_time = $_data['returnValues']['_effectiveTime'];
                $attestation->expiry_time = $_data['returnValues']['_expiryTime'];
                $attestation->public_data = $public_data;
                $attestation->documents_matrix_encrypted = $documents_matrix_encrypted;
                $attestation->availability_address_encrypted = $availability_address_encrypted;
                $attestation->is_managed = $_data['returnValues']['_isManaged'];
                $attestation->attestation_hash = $attestation_hash;
                $attestation->transaction_hash = $_data['transactionHash'];
                $attestation->user_account = $identified_address;

                $attestation->save();

                $url = $this->helper_url.'/ta-get-attestation-components?attestation_hash='.$attestation_hash;
                Log::debug('WebhookController EVT_setAttestation url');
                Log::debug($url);

                $client = new Client();
                $res = $client->request('GET', $url);
                if($res->getStatusCode() == 200) {

                    $response = json_decode($res->getBody());
                    Log::debug('WebhookController EVT_setAttestation ta-get-attestation-components');
                    Log::debug($response);


                } else {
                    Log::error('WebhookController EVT_setAttestation ta-get-attestation-components: ' . $res->getStatusCode());
                }
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
            if($data_local['event'] === 'EVT_verifyTrustAnchor') {
                $returnValues = $data_local['returnValues'];
                $account_address = $returnValues['trustAnchorAddress'];
                
                $ta = VerifiedTrustAnchor::firstOrCreate(['account_address' => $account_address]);
                $ta->save();
            }

            broadcast(new ShyftSmartContractEvent($data));
        }

        if ($data['message'] === 'create-new-user-account') {

            // var obj = { user_id: user_id, message: "create-new-user-account", data: data };
            $input['user_id'] = $data['user_id'];

            $ta = TrustAnchor::firstOrCreate(['user_id' => $input['user_id']]);

            $account = $data['data']['account'];
            $ta->ta_prefname = $account['prefname'];
            $ta->account_address = $account['address'];

            $private_key = $account['private_key'];
            $eloquent_encryption = new EloquentEncryption();
            $encrypted = $eloquent_encryption->encrypt($private_key);
            $ta->private_key_encrypt = bin2hex($encrypted);
            $ta->signature_hash = $account['signature_hash']['SignatureHash'];
            $ta->signature = $account['signature_hash']['Signature'];
            $ta->public_key = $account['public_key'];
            $ta->save();

            $user = User::findOrFail($input['user_id']);
            $user->trustAnchor()->save($ta);

            $data['data'] = $ta;
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
                $extra_data->trust_anchor_address = $data_local['returnValues']['_trustAnchorAddress'];
                $extra_data->endpoint_name = $data_local['returnValues']['_endpointName'];
                $extra_data->ipv4_address = $data_local['ipv4_address'];
                $extra_data->save();
            }
            // $account = $data_local['returnValues']['_trustAnchorAddress'];
            // $endpoint_hash = $data_local['returnValues']['_endpointName'];
            // $id = 1;
            // $url = $this->helper_url.'/ta-get-endpoint-name?user_id='.$id.'&account='.$account.'&endpoint_hash='.$endpoint_hash;
            //   $client = new Client();
            //   $res = $client->request('GET', $url);
            //   if($res->getStatusCode() == 200) {

            //     $response = json_decode($res->getBody());
            //     Log::debug('ContractsController ta_get_endpoint_name');
            //     Log::debug($response);


            //   } else {
            //       Log::error('ContractsController ta_get_endpoint_name: ' . $res->getStatusCode());
            //   }

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
                $extra_data = new TrustAnchorExtraDataUnique();

                $extra_data->transaction_hash = $data_local['transactionHash'];
                $extra_data->trust_anchor_address = $data_local['returnValues']['_trustAnchorAddress'];
                $extra_data->key_value_pair_name = $data_local['returnValues']['_keyValuePairName'];
                $extra_data->key_value_pair_value = $data_local['returnValues']['_keyValuePairValue'];
                $extra_data->save();
            }

            if ($data_local['event'] === "EVT_setTrustAnchorKeyValuePairUpdated") {

                $extra_data = TrustAnchorExtraDataUnique::firstOrNew(['key_value_pair_name' => $data_local['returnValues']['_keyValuePairName'], 'trust_anchor_address' => $data_local['returnValues']['_trustAnchorAddress']]);

                $extra_data->transaction_hash = $data_local['transactionHash'];
                $extra_data->trust_anchor_address = $data_local['returnValues']['_trustAnchorAddress'];
                $extra_data->key_value_pair_name = $data_local['returnValues']['_keyValuePairName'];
                $extra_data->key_value_pair_value = $data_local['returnValues']['_keyValuePairValue'];
                $extra_data->save();
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
        if ($data['message'] === 'ta-set-jurisdiction') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'ta-create-user') {
            $eloquent_encryption = new EloquentEncryption();

            $trust_anchor_user_id = $data['ta_user_id'];
            $tau = TrustAnchorUser::findOrFail($trust_anchor_user_id);
            $tau->account_address = $data['data']['account']['address'];
            
            $private_key = $data['data']['account']['private_key'];
            $encrypted = $eloquent_encryption->encrypt($private_key);
            $tau->private_key_encrypt = bin2hex($encrypted);

            $tau->signature_hash = $data['data']['signature_hash']['SignatureHash'];
            $tau->signature = $data['data']['signature_hash']['Signature'];
            $tau->public_key = $data['data']['public_key'];
            $tau->save();

            #save btc and eth to user account
            $bitcoinAccount = $data['data']['bitcoinAccount'];
            $cwa = new CryptoWalletAddress();
            $cwa->address = $bitcoinAccount['address'];
            $cwa->public_key = $bitcoinAccount['public_key'];
            $private_key = $bitcoinAccount['private_key'];
            $encrypted = $eloquent_encryption->encrypt($private_key);
            $cwa->private_key_encrypt = bin2hex($encrypted);

            $cwa->trust_anchor_user_id = $tau->id;
            $cwa->trust_anchor_id = $tau->trust_anchor_id;
            $cwa->crypto_wallet_type_id = CryptoWalletType::where('wallet_type', 'BTC')->first()->id;
            $cwa->save();

            $ethereumAccount = $data['data']['ethereumAccount'];
            $cwa = new CryptoWalletAddress();
            $cwa->address = $ethereumAccount['address'];
            $cwa->public_key = $ethereumAccount['public_key'];
            $private_key = $ethereumAccount['private_key'];
            $encrypted = $eloquent_encryption->encrypt($private_key);
            $cwa->private_key_encrypt = bin2hex($encrypted);

            $cwa->trust_anchor_user_id = $tau->id;
            $cwa->trust_anchor_id = $tau->trust_anchor_id;
            $cwa->crypto_wallet_type_id = CryptoWalletType::where('wallet_type', 'ETH')->first()->id;
            $cwa->save();

            $zcashAccount = $data['data']['zcashAccount'];
            $cwa = new CryptoWalletAddress();
            $cwa->address = $zcashAccount['address'];
            $cwa->public_key = $zcashAccount['public_key'];
            $private_key = $zcashAccount['private_key'];
            $encrypted = $eloquent_encryption->encrypt($private_key);
            $cwa->private_key_encrypt = bin2hex($encrypted);

            $cwa->trust_anchor_user_id = $tau->id;
            $cwa->trust_anchor_id = $tau->trust_anchor_id;
            $cwa->crypto_wallet_type_id = CryptoWalletType::where('wallet_type', 'ZEC')->first()->id;
            $cwa->save();

            $moneroAccount = $data['data']['moneroAccount'];
            $cwa = new CryptoWalletAddress();
            $cwa->address = $moneroAccount['address'];
            $cwa->public_key = $moneroAccount['public_key'];
            $private_key = $moneroAccount['private_key'];
            $encrypted = $eloquent_encryption->encrypt($private_key);
            $cwa->private_key_encrypt = bin2hex($encrypted);
            
            $cwa->trust_anchor_user_id = $tau->id;
            $cwa->trust_anchor_id = $tau->trust_anchor_id;
            $cwa->crypto_wallet_type_id = CryptoWalletType::where('wallet_type', 'XMR')->first()->id;
            $cwa->save();
            
            $data['data']['account'] = $tau->account_address;
            broadcast(new ContractsInstantiate($data));
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

        if ($data['message'] === 'ta-register-jurisdiction') {
            broadcast(new ContractsInstantiate($data));
        }

        if ($data['message'] === 'ta-register-jurisdiction-error') {
            broadcast(new ContractsInstantiate($data));
        }

        if ($data['message'] === 'ta-get-balance') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'ta-request-tokens') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'ta-get-user-attestations') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'refresh-all-attestations') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'refresh-all-discovery-layer-key-value-pairs') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'refresh-all-verified-tas') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'get-validation-for-key-value-pair-data') {
            broadcast(new ContractsInstantiate($data));
        }
        if ($data['message'] === 'ta-get-attestation-components-in-array') {
            Log::debug('ta-get-attestation-components-in-array');
            Log::debug(print_r($data, true));
            $result = $data['data'][0];
            $country = Country::where('id', hexdec($result['jurisdiction']))->first();
            $list = [['field' => 'TA Address', 'data'  => $result['trustAnchorAddress']],
                    ['field' => 'User Address', 'data'  => $result['user_address']],
                    ['field' => 'Jurisdiction', 'data' => $country->name],
                    ['field' => 'Version Code', 'data'  => $result['version_code']],
                    ['field' => 'Coin Blockchain', 'data'  => $result['coin_blockchain']],
                    ['field' => 'Coin Token', 'data'  => $result['coin_token']],
                    ['field' => 'Coin Address', 'data'  => $result['coin_address']],
                    ['field' => 'Coin Memo', 'data'  => $result['coin_memo']],
                    ['field' => 'Public Data', 'data'  => $result['public_data']],
                    ['field' => 'Public Data Decoded', 'data'  => $result['public_data_decoded']],
                    ['field' => 'Documents Matrix Encrypted', 'data'  => $result['documents_matrix_encrypted']],
                    ['field' => 'Documents Matrix Encrypted Decoded', 'data'  => $result['documents_matrix_encrypted_decoded']],
                    ['field' => 'Availability Address Encrypted', 'data'  => $result['availability_address_encrypted']],
                    ['field' => 'Availability Address Encrypted Decoded', 'data'  => $result['availability_address_encrypted_decoded']]];
                    
            $data['data'] = $list;
            broadcast(new ContractsInstantiate($data));
        }

        if ($data['message'] === 'ta-get-attestation-components') {
            Log::debug('ta-get-attestation-components');
            Log::debug(print_r($data, true));

            $data_local = $data['data'];
            Log::debug(print_r($data_local, true));

            if ($data_local['type'] == 'WALLET') {

                $crypto_address = $data_local['coin_address'];
                Log::debug('crypto_address');
                Log::debug($crypto_address);

                $sca = SmartContractAttestation::where('attestation_hash', $data_local['attestation_hash'])->first();
               
                $sca->public_data = $data_local['public_data'];
                $sca->public_data_decoded = $data_local['public_data_decoded'];

                $sca->documents_matrix_encrypted = $data_local['documents_matrix_encrypted'];
                $sca->documents_matrix_encrypted_decoded = $data_local['documents_matrix_encrypted_decoded'];

                $sca->availability_address_encrypted = $data_local['availability_address_encrypted'];
                $sca->availability_address_encrypted_decoded = $data_local['availability_address_encrypted_decoded'];

                $sca->version_code = $data_local['version_code'];
                $sca->coin_blockchain = $data_local['coin_blockchain'];
                $sca->coin_token = $data_local['coin_token'];
                $sca->coin_address = $data_local['coin_address'];
                $sca->coin_memo = $data_local['coin_memo'];

                $sca->save();

                #trigger Kyc Template
                app('App\Http\Controllers\KycTemplateController')->attestation($data_local['attestation_hash']);
            }
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

        return response()->json(['message' => 'success'], 200);
    }
}
