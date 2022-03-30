<?php

namespace App\Http\Controllers\Api\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\{TrustAnchor, VerifiedTrustAnchor, TrustAnchorExtraDataUnique, SmartContractAttestation, KycTemplate, KycTemplateState};
use App\Http\Requests\{CreateKycTemplateRequest, GetTrustAnchorApiUrlRequest, EncryptIvmsRequest, DecryptIvmsRequest};
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class TrustAnchorController extends Controller
{

      /**
     * Create a new controller instance.
     *
     * @return void
     */
      public function __construct()
      {
          $this->helper_url = env('SHYFT_TEMPLATE_HELPER_URL');
          $this->http_api_url = env('HTTP_API_URL');
      }

      /**
      * Show All Verified Trust Anchors
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function get_verified_trust_anchors(Request $request)
      {

          $trust_anchors = VerifiedTrustAnchor::orderBy('account_address')->get();

          return response()->json($trust_anchors);
      }

      public function refresh_all_verified_trust_anchors(Request $request)
      {
          Log::debug('TrustAnchorController refresh_all_verified_tas');
 
          $url = $this->http_api_url.'/refresh-all-verified-tas?user_id=1';
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('TrustAnchorController refresh_all_verified_trust_anchors');
            Log::debug($response);
           
              
          } else {
              Log::error('TrustAnchorController refresh_all_verified_trust_anchors: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }

      public function refresh_all_discovery_layer_key_value_pairs(Request $request)
      {
          Log::debug('TrustAnchorController refresh_all_discovery_layer_key_value_pairs');

          $input = $request->all();
          
          Log::debug(print_r($input, true));
          $url = $this->http_api_url.'/refresh-all-discovery-layer-key-value-pairs?user_id=1';
          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {

            $response = json_decode($res->getBody());
            Log::debug('TrustAnchorController refresh_all_discovery_layer_key_value_pairs');
            Log::debug($response);
           
              
          } else {
              Log::error('TrustAnchorController refresh_all_discovery_layer_key_value_pairs: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }
      /**
      * Get Trust Anchor Details
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function get_trust_anchor_details(Request $request, $address)
      {

          $trust_anchor_details = TrustAnchorExtraDataUnique::where('trust_anchor_address', $address)->get();

          return response()->json($trust_anchor_details);
      }

      /**
      * Verify Trust Anchor
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function verify_trust_anchor(Request $request, $address)
      {
          $isVerified = VerifiedTrustAnchor::where('account_address', $address)->exists();

          return response()->json(['address' => $address, 'verified' => $isVerified ]);

      }
      
      /**
      * Get Trust Anchor Account
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function get_trust_anchor_api_url(GetTrustAnchorApiUrlRequest $request)
      {
          $input = $request->all();
          $ta_address = $input['ta_address'];

          $taedu = TrustAnchorExtraDataUnique::where('trust_anchor_address', $ta_address)->where('key_value_pair_name', 'API_URL')->orderBy('block_number', 'DESC')->first();

          return response()->json($taedu);

      }

      /**
      * Get Trust Anchor Account
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function get_trust_anchor_account(Request $request)
      {
          $ta = TrustAnchor::first();

          return response()->json($ta);

      }

      /**
      * Get Trust Anchor Attestations
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function get_attestations(Request $request)
      {
          $input = $request->all();

          // set defaults for pagination
          $page = !empty($input['page']) ? (int)$input['page'] : 1;
          $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

          $attestations = new SmartContractAttestation;
          $paginatedAttestations = new SmartContractAttestation;

          if(!empty($input['searchTerm'])) {
            $attestations = $attestations->search($input['searchTerm']);
            $paginatedAttestations = $paginatedAttestations->search($input['searchTerm']);
          }
          else {
            return response()->json([]);
          }

          // apply pagination
          if($perPage !== -1) {
            $paginatedAttestations = $paginatedAttestations->offset(($page-1) * $perPage)->limit($perPage)->get();
          } else {
            $paginatedAttestations = $paginatedAttestations->get();
          }

          // return the current params and rows back
          return response()->json([
            'serverParams' => [
              'page' => $page,
              'perPage' => $perPage,
            ],
            'totalRecords' => $attestations->count(),
            'rows' => $paginatedAttestations,
          ]);

      }

      public function buildKycTemplate($kt) {
        $kycTemplate = array("AttestationHash"=>$kt->attestation_hash,
                        "BeneficiaryTAAddress"=>$kt->beneficiary_ta_address,
                        "BeneficiaryTAPublicKey"=>$kt->beneficiary_ta_public_key,
                        "BeneficiaryUserAddress"=>$kt->beneficiary_user_address,
                        "BeneficiaryUserPublicKey"=>$kt->beneficiary_user_public_key,
                        "BeneficiaryTASignatureHash"=>$kt->beneficiary_ta_signature_hash,
                        "BeneficiaryTASignature"=>json_decode($kt->beneficiary_ta_signature),
                        "BeneficiaryUserSignatureHash"=>$kt->beneficiary_user_signature_hash,
                        "BeneficiaryUserSignature"=>json_decode($kt->beneficiary_user_signature),

                        "CoinBlockchain"=>$kt->coin_blockchain,
                        "CoinToken"=>$kt->coin_token,
                        "CoinAddress"=>$kt->coin_address,
                        "CoinMemo"=>$kt->coin_memo,
                        "CoinTransactionHash"=>$kt->coin_transaction_hash,
                        "CoinTransactionValue"=>$kt->coin_transaction_value,
                
                        "SenderTAAddress"=>$kt->sender_ta_address,
                        "SenderTAPublicKey"=>$kt->sender_ta_public_key,
                        "SenderUserAddress"=>$kt->sender_user_address,
                        "SenderUserPublicKey"=>$kt->sender_user_public_key,
                        "SenderTASignatureHash"=>$kt->sender_ta_signature_hash,
                        "SenderTASignature"=>json_decode($kt->sender_ta_signature),
                        "SenderUserSignatureHash"=>$kt->sender_user_signature_hash,
                        "SenderUserSignature"=>json_decode($kt->sender_user_signature),
                        "BeneficiaryKYC"=>$kt->beneficiary_kyc,
                        "SenderKYC"=>$kt->sender_kyc,
                        "BeneficiaryTAUrl"=>$kt->beneficiary_ta_url,
                        "SenderTAUrl"=>$kt->sender_ta_url
                    );
        return json_encode($kycTemplate);
    }

      public function updateKycTemplateForState($kycTemplate, $state) {

        $kts = KycTemplateState::where('state', $state)->firstOrFail();

        $kycTemplate->kyc_template_state_id = $kts->id;
        $kycTemplate->save();
      }
      /**
      * Create KYC Template
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function create_kyc_template(CreateKycTemplateRequest $request)
      {
          # Assumes TA is Beneficiary
          $input = $request->all();

          $user_account = $input['user_account'];
          $user_public_key = $input['user_public_key'];
          $user_signature_hash = $input['user_signature_hash'];
          $user_signature = $input['user_signature'];
          $ivms_encrypt = "";
          $coin_transaction_hash = "";
          $coin_transaction_value = "";

          if (array_key_exists('ivms_encrypt', $input)) {
              $ivms_encrypt = $input['ivms_encrypt'];
          }

          if (array_key_exists('coin_transaction_hash', $input)) {
              $coin_transaction_hash = $input['coin_transaction_hash'];
          }

          if (array_key_exists('coin_transaction_value', $input)) {
              $coin_transaction_value = $input['coin_transaction_value'];
          }

          $ta = TrustAnchor::firstOrFail();

          $attestation_hash = $input['attestation_hash'];
          $sca = SmartContractAttestation::where('attestation_hash', $attestation_hash)->firstOrFail();

          #check if TA account is Beneficiary or Originator
          #if TA account in SmartContractAttestation matches TA account, TA account is originator
          $isBeneficiary = strcasecmp($ta->account_address, $sca->ta_account);
          Log::debug('isBeneficiary');
          Log::debug($isBeneficiary);
          #prepare the template
          $kt = KycTemplate::firstOrCreate(['attestation_hash' => $attestation_hash]);

          $kt->coin_blockchain = $sca->coin_blockchain;
          $kt->coin_token = $sca->coin_token;
          $kt->coin_address = $sca->coin_address;
          $kt->coin_memo = $sca->coin_memo;
          $kt->coin_transaction_hash = $coin_transaction_hash;
          $kt->coin_transaction_value = $coin_transaction_value;
          
          $kt->sender_ta_address = $sca->ta_account;
          $kt->sender_user_address = $sca->user_account;
          
          $this->updateKycTemplateForState($kt, 'ATTESTATION');

          if($isBeneficiary) {
            $kt->beneficiary_ta_address = $ta->account_address;
            $kt->beneficiary_user_address = $user_account;
            $kt->beneficiary_ta_public_key = $ta->public_key;
            $this->updateKycTemplateForState($kt, 'BENEFICIARY_TA_PUBLIC_KEY');
            $kt->beneficiary_user_public_key = $user_public_key;
            $this->updateKycTemplateForState($kt, 'BENEFICIARY_USER_PUBLIC_KEY');
            $kt->beneficiary_ta_signature_hash = $ta->signature_hash;
            $kt->beneficiary_ta_signature = $ta->signature;
            $this->updateKycTemplateForState($kt, 'BENEFICIARY_TA_SIGNATURE');
            $kt->beneficiary_user_signature_hash = $user_signature_hash;
            $kt->beneficiary_user_signature = $user_signature;
            $this->updateKycTemplateForState($kt, 'BENEFICIARY_USER_SIGNATURE');

            if($ivms_encrypt) {
              $kt->beneficiary_kyc = $ivms_encrypt;
              $this->updateKycTemplateForState($kt, 'BENEFICIARY_KYC');
            }
            
            $kt->save();

          }
          else {
            $kt->sender_ta_address = $ta->account_address;
            $kt->sender_user_address = $user_account;
            $kt->sender_ta_public_key = $ta->public_key;
            $this->updateKycTemplateForState($kt, 'SENDER_TA_PUBLIC_KEY');
            $kt->sender_user_public_key = $user_public_key;
            $this->updateKycTemplateForState($kt, 'SENDER_USER_PUBLIC_KEY');
            $kt->sender_ta_signature_hash = $ta->signature_hash;
            $kt->sender_ta_signature = $ta->signature;
            $this->updateKycTemplateForState($kt, 'SENDER_TA_SIGNATURE');
            $kt->sender_user_signature_hash = $user_signature_hash;
            $kt->sender_user_signature = $user_signature;
            $this->updateKycTemplateForState($kt, 'SENDER_USER_SIGNATURE');
            if($ivms_encrypt) {
              $kt->sender_kyc = $ivms_encrypt;
              $this->updateKycTemplateForState($kt, 'SENDER_KYC');
            }

            $kt->save();

          }
        
          $taedu = TrustAnchorExtraDataUnique::where('trust_anchor_address', $sca->ta_account)->where('key_value_pair_name', 'API_URL')->firstOrFail();
          $kt->sender_ta_url = $taedu->key_value_pair_value;

          $taedu = TrustAnchorExtraDataUnique::where('trust_anchor_address', $kt->beneficiary_ta_address)->where('key_value_pair_name', 'API_URL')->firstOrFail();

          $kt->beneficiary_ta_url = $taedu->key_value_pair_value; 

          $kt->save();
          
          if($isBeneficiary) {
            #POST TO ORIGINATOR URL
            $url = $kt->sender_ta_url;
          }
          else {
            #POST TO BENEFICIARY URL
            $url = $kt->beneficiary_ta_url;
          }

          Log::debug('posting url');
          Log::debug($url);
          
          $kycTemplateJSON = $this->buildKycTemplate($kt);
          
          $client = new Client();

          try {
              $res = $client->request('POST', $url, [
                  'json' => ['kycTemplate' => $kycTemplateJSON]
              ]);
          } catch (ClientException $e) {
              
          }
          
          return response()->json($kt);
      }

      /**
      * Get KYC Templates
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function get_kyc_templates(Request $request)
      {
          $input = $request->all();

          // set defaults for pagination
          $page = !empty($input['page']) ? (int)$input['page'] : 1;
          $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

          $templates = new KycTemplate;
          $paginatedTemplates= new KycTemplate;

          if(!empty($input['searchTerm'])) {
            $templates = $templates->search($input['searchTerm']);
            $paginatedTemplates = $paginatedTemplates->search($input['searchTerm']);
          }
          else {
            return response()->json([]);
          }

          // apply pagination
          if($perPage !== -1) {
            $paginatedTemplates = $paginatedTemplates->offset(($page-1) * $perPage)->limit($perPage)->get();
          } else {
            $paginatedTemplates = $paginatedTemplates->get();
          }

          // return the current params and rows back
          return response()->json([
            'serverParams' => [
              'page' => $page,
              'perPage' => $perPage,
            ],
            'totalRecords' => $templates->count(),
            'rows' => $paginatedTemplates,
          ]);

      }

      /**
      * Encrypt IVMS
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function encrypt_ivms(EncryptIvmsRequest $request)
      {
          $input = $request->all();
          $public_key = $input['public_key'];
          $ivms_json = $input['ivms_json'];
          Log::debug($public_key);
          Log::debug($ivms_json);
          $url = $this->helper_url.'/EncryptData';

          $client = new Client();
          $res = $client->request('POST', $url, [
              'json' => ['publicKey' => $public_key, 'kycJSON' => $ivms_json]
          ]);
          if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

              $response = $res->getBody();

              $kycEncrypt = json_decode($response)->kycEncrypt;
              return response()->json($kycEncrypt);

          } else {
            Log::error('TrustAnchorController encrypt_ivms: ' . print_r($res, true));
          }
      }

      /**
      * Decrypt IVMS
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function decrypt_ivms(DecryptIvmsRequest $request)
      {
          $input = $request->all();
          $private_key = $input['private_key'];
          $kyc_data = $input['kyc_data'];

          $url = $this->helper_url.'/DecryptData';

          $client = new Client();
          $res = $client->request('POST', $url, [
              'json' => ['privateKey' => $private_key, 'kycData' => $kyc_data]
          ]);
          if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

              $response = $res->getBody();
              $kycDecrypt = json_decode($response)->kycDecrypt;
              return response()->json($kycDecrypt);

          } else {
            Log::error('TrustAnchorController decrypt_ivms: ' . print_r($res, true));
          }
      }


      /**
      * Recover SignatureS
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function recover_signature(Request $request)
      {
          $input = $request->all();
          $type = $input['type'];
          $template = json_encode($input['template']);

          Log::debug($type);
          Log::debug($template);

          $url = $this->helper_url.'/TARecover';

          $client = new Client();
          $res = $client->request('POST', $url, [
            'json' => ['kycTemplate' => $template, 'type' => $type]
          ]);
          if($res->getStatusCode() == 200 || $res->getStatusCode() == 201) {

              $response = $res->getBody();
              Log::debug($response);
              return $response;

          } else {
            Log::error('TrustAnchorController recover_signature: ' . print_r($res, true));
          }
      }

}
