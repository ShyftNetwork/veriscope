<?php

namespace App\Http\Controllers\Api\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\{TrustAnchor, SandboxTrustAnchorUserCryptoAddress, VerifiedTrustAnchor, TrustAnchorExtraDataUnique, SmartContractAttestation, KycTemplate, KycTemplateState};
use App\Http\Requests\{CreateKycTemplateRequest, GetTrustAnchorApiUrlRequest, EncryptIvmsRequest, DecryptIvmsRequest};
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Throwable\ClientException;
use App\Support\EthereumToolsUtils;
use Elliptic\EC;
use kornrunner\Keccak;
use Spatie\WebhookServer\WebhookCall;
use Illuminate\Validation\ValidationException;
use App\Transformers\KycTemplateTransformer;
use App\Jobs\{DataExternalJob, DataInternalJob, DataInternalIVMSJob, DataExternalStatelessJob};
use Illuminate\Support\Facades\DB;

class TrustAnchorController extends Controller
{

      /**
     * Create a new controller instance.
     *
     * @return void
     */
      public function __construct()
      {
          $this->http_api_url = env('HTTP_API_URL');
      }

      /**
      * Delete all sandbox templates
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function delete_sandbox_templates(Request $request)
      {
          $staucas = SandboxTrustAnchorUserCryptoAddress::all();
          foreach ($staucas as $stauca) {
              $kts = KycTemplate::where('coin_address','ILIKE',$stauca->crypto_address)->where('coin_token','ILIKE',$stauca->crypto_type)->get();
              foreach ($kts as $kt) {
                 DB::table('state_histories')->where('model_id', '=', $kt->id)->where('model_type', '=','App\KycTemplate')->delete();
                 DB::table('kyc_templates')->where('id', '=', $kt->id)->delete();
              }
          }
          return response()->json([]);
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

      /**
      * Create KYC Template
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function create_kyc_template(CreateKycTemplateRequest $request)
      {
          # Assumes TA is Beneficiary
          $attestation_hash = $request->get('attestation_hash','');

          $user_account = $request->get('user_account','');
          $user_public_key = $request->get('user_public_key','');
          $user_signature_hash = $request->get('user_signature_hash','');
          $user_signature = $request->get('user_signature','');

          $coin_transaction_hash = $request->get('coin_transaction_hash','');
          $coin_transaction_value = $request->get('coin_transaction_value','');
          $ivms_encrypt = $request->get('ivms_encrypt','');


          $ivms_state_code = $request->get('ivms_state_code','');

          #Sanbox Automated Response For test_vars
          $test_vars = $request->get('test_vars','ivms_state_code=0202');


          $ta = TrustAnchor::firstOrFail();
          $sca = SmartContractAttestation::where('attestation_hash', $attestation_hash)->firstOrFail();
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
          $kt->test_vars = $test_vars;
          $kt->save();


          $trustAnchorType = $kt->getUserType();

          try {

            if($trustAnchorType === 'BENEFICIARY') {

              //Transition Step 1: BE_TA_PUBLIC_KEY
              if($kt->status()->canBe('BE_TA_PUBLIC_KEY')){
                $kt->beneficiary_ta_address = $ta->account_address;
                $kt->beneficiary_ta_public_key = $ta->public_key;
                $kt->save();
                $kt->status()->transitionTo($to = 'BE_TA_PUBLIC_KEY');
              }
              //Transition Step 2: BE_TA_SIGNATURE
              if($kt->status()->canBe('BE_TA_SIGNATURE')){
                $kt->beneficiary_ta_signature_hash = $ta->signature_hash;
                $kt->beneficiary_ta_signature = $ta->signature;
                $kt->save();
                $kt->status()->transitionTo($to = 'BE_TA_SIGNATURE');
              }

              //Transition Step 3: BE_USER_PUBLIC_KEY
              if($kt->status()->canBe('BE_USER_PUBLIC_KEY')){
                $kt->beneficiary_user_address = $user_account;
                $kt->beneficiary_user_public_key = $user_public_key;
                $kt->save();
                $kt->status()->transitionTo($to = 'BE_USER_PUBLIC_KEY');
              }

              //Transition Step 4: BE_USER_SIGNATURE
              if($kt->status()->canBe('BE_USER_SIGNATURE')){
                $kt->beneficiary_user_signature_hash = $user_signature_hash;
                $kt->beneficiary_user_signature = $user_signature;
                $kt->save();
                $kt->status()->transitionTo($to = 'BE_USER_SIGNATURE');
              }
              //Transition Step 5: BE_TA_URLS
              if($kt->status()->canBe('BE_TA_URLS')){
                $taOriginator = TrustAnchorExtraDataUnique::where('trust_anchor_address', 'ILIKE', $sca->ta_account)->where('key_value_pair_name', 'API_URL')->first();
                $taBeneficiary = TrustAnchorExtraDataUnique::where('trust_anchor_address', 'ILIKE', $kt->beneficiary_ta_address)->where('key_value_pair_name', 'API_URL')->first();
                $kt->sender_ta_url = ($taOriginator) ? $taOriginator->key_value_pair_value : '';
                $kt->beneficiary_ta_url = ($taBeneficiary) ? $taBeneficiary->key_value_pair_value : '';
                $kt->save();
                $kt->status()->transitionTo($to = 'BE_TA_URLS');
              }
              //Transition Step 6: BE_TA_URLS_VERIFIED
              if($kt->status()->canBe('BE_TA_VERIFIED')){
                $kt->status()->transitionTo($to = 'BE_TA_VERIFIED');
              }

              if (!empty($ivms_encrypt) && empty($ivms_state_code)) {
                $kt->beneficiary_kyc = $ivms_encrypt;
                $kt->save();
                //Transition Step 7: BE_KYC_UPDATE
                if($kt->status()->canBe('BE_KYC_UPDATE')) {
                  $kt->status()->transitionTo($to = 'BE_KYC_UPDATE');
                }
              }

              // Rollback Mechanism: If ivms_state_code is not empty
              if (!empty($ivms_state_code) && $kt->ivms_status()->was('OR_ENC_RECEIVED')) {
                // 0307 Temporary & 0308 Permarent
                if($ivms_state_code == "0307" || $ivms_state_code === "0308"){
                 $kt->status()->transitionTo($to = 'OR_KYC_REJECTED', ['or_ivms_state_code' => $ivms_state_code]);
                }
                // 0202 Accepted
                if($ivms_state_code == "0202"){
                 $kt->status()->transitionTo($to = 'OR_KYC_ACCEPTED', ['or_ivms_state_code' => $ivms_state_code]);
                }
              }



            }
            elseif($trustAnchorType === 'ORIGINATOR') {

              //Transition Step 1: OR_TA_PUBLIC_KEY
              if($kt->status()->canBe('OR_TA_PUBLIC_KEY')){
                $kt->sender_ta_address = $ta->account_address;
                $kt->sender_ta_public_key = $ta->public_key;
                $kt->save();
                $kt->status()->transitionTo($to = 'OR_TA_PUBLIC_KEY');
              }
              //Transition Step 2: OR_TA_SIGNATURE
              if($kt->status()->canBe('OR_TA_SIGNATURE')){
                $kt->sender_ta_signature_hash = $ta->signature_hash;
                $kt->sender_ta_signature = $ta->signature;
                $kt->save();
                $kt->status()->transitionTo($to = 'OR_TA_SIGNATURE');
              }

              //Transition Step 3: OR_USER_PUBLIC_KEY
              if($kt->status()->canBe('OR_USER_PUBLIC_KEY')){
                $kt->sender_user_address = $user_account;
                $kt->sender_user_public_key = $user_public_key;
                $kt->save();
                $kt->status()->transitionTo($to = 'OR_USER_PUBLIC_KEY');
              }
              //Transition Step 4: OR_USER_SIGNATURE
              if($kt->status()->canBe('OR_USER_SIGNATURE')){
                $kt->sender_user_signature_hash = $user_signature_hash;
                $kt->sender_user_signature = $user_signature;
                $kt->status()->transitionTo($to = 'OR_USER_SIGNATURE');
              }

              //Transition Step 5: OR_TA_URLS
              if($kt->status()->canBe('OR_TA_URLS')){
                $taOriginator = TrustAnchorExtraDataUnique::where('trust_anchor_address', 'ILIKE', $sca->ta_account)->where('key_value_pair_name', 'API_URL')->first();
                $taBeneficiary = TrustAnchorExtraDataUnique::where('trust_anchor_address', 'ILIKE', $kt->beneficiary_ta_address)->where('key_value_pair_name', 'API_URL')->first();
                $kt->sender_ta_url = ($taOriginator) ? $taOriginator->key_value_pair_value : '';
                $kt->beneficiary_ta_url = ($taBeneficiary) ? $taBeneficiary->key_value_pair_value : '';
                $kt->save();
                $kt->status()->transitionTo($to = 'OR_TA_URLS');
              }
              //Transition Step 6: OR_TA_URLS_VERIFIED
              if($kt->status()->canBe('OR_TA_VERIFIED')){
                $kt->status()->transitionTo($to = 'OR_TA_VERIFIED');
              }

              if (!empty($ivms_encrypt) && empty($ivms_state_code)) {
                $kt->sender_kyc = $ivms_encrypt;
                $kt->save();
                //Transition Step 7: OR_KYC_UPDATE
                if($kt->status()->canBe('OR_KYC_UPDATE') && $kt->webhook_status()->was('OR_DATA_RECEIVED') ) {
                  $kt->status()->transitionTo($to = 'OR_KYC_UPDATE');
                }
              }


              // Rollback Mechanism: If ivms_state_code is not empty
              if (!empty($ivms_state_code) && $kt->ivms_status()->was('BE_ENC_RECEIVED')) {
                // 0307 Temporary & 0308 Permarent
                if($ivms_state_code == "0307" || $ivms_state_code == "0308"){
                  $kt->status()->transitionTo($to = 'BE_KYC_REJECTED', ['be_ivms_state_code' => $ivms_state_code]);
                }
                // 0202 Accepted
                if($ivms_state_code == "0202"){
                  $kt->status()->transitionTo($to = 'BE_KYC_ACCEPTED', ['be_ivms_state_code' => $ivms_state_code]);
                }
              }



            }

            return response()->json($kt);


          } catch (\Throwable $throwable) {

            if($throwable instanceof ValidationException){
              return response()->json(['error' => $throwable->errors() ],400);
            } else {
              return response()->json(['error' => $throwable->getTrace() ],400);
            }


          }

      }


      /**
      * Retry KYC Template
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function retry_kyc_template(Request $request)
      {
          # Assumes TA is Beneficiary
          $attestation_hash = $request->get('attestation_hash','');
          $sca = SmartContractAttestation::where('attestation_hash', $attestation_hash)->firstOrFail();
          #prepare the template
          $kt = KycTemplate::where('attestation_hash', $attestation_hash)->firstOrFail();
          $trustAnchorType = $kt->getUserType();

          try {

            if($trustAnchorType === 'BENEFICIARY') {
              //Transition Step 1: BE_DATA_FAILED
              if($kt->webhook_status()->is('BE_DATA_FAILED') && $kt->webhook_status()->canBe('BE_DATA_SENT')){
               DataExternalJob::dispatch($kt, 'BE_DATA');
              }
              //Transition Step 2: BE_KYC_FAILED
              if($kt->webhook_status()->is('BE_KYC_FAILED') && $kt->webhook_status()->canBe('BE_KYC_SENT')){
               DataExternalJob::dispatch($kt, 'BE_KYC');
              }

            }elseif($trustAnchorType === 'ORIGINATOR') {
              //Transition Step 1: OR_DATA_FAILED
              if($kt->webhook_status()->was('OR_DATA_FAILED') && $kt->webhook_status()->canBe('OR_DATA_SENT')){
               DataExternalJob::dispatch($kt,'OR_DATA');
              }
              //Transition Step 2: OR_KYC_FAILED
              if($kt->webhook_status()->is('OR_KYC_FAILED') && $kt->webhook_status()->canBe('OR_KYC_SENT')){
               DataExternalJob::dispatch($kt,'OR_KYC');
              }

            }

            return response()->json($kt);


          } catch (\Throwable $throwable) {

            if($throwable instanceof ValidationException){
              return response()->json(['error' => $throwable->errors() ],400);
            } else {
              return response()->json(['error' => $throwable->getTrace() ],400);
            }


          }

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


          try {

            $result = EthereumToolsUtils::encryptData(
                    $public_key,
                    $ivms_json
            );

            return response()->json(['data' => $result], 200);


          } catch (\Throwable $e) {

            Log::error('TrustAnchorController encrypt_ivms_test: ' . print_r($e, true));
            return response()->json(['error' => $e->getMessage()],400);

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

          try {

            $result = EthereumToolsUtils::decryptData(
                    $private_key,
                    $kyc_data
            );

            return response()->json(['data' => $result], 200);


          } catch (\Throwable $e) {

            Log::error('TrustAnchorController decrypt_ivms_test: ' . print_r($e, true));

            return response()->json(['error' => $e->getMessage()],400);

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

        $chainId = 1;
        $returnMessage = [];
        $input = $request->all();
        $type = $input['type'];
        $template = $input['template'];
        Log::debug($type);
        Log::debug($template);


        try {

          $result = EthereumToolsUtils::ecRecoverVRS(
                  $template[$type.'SignatureHash'],
                  $template[$type.'Signature']['v'],
                  $template[$type.'Signature']['r'],
                  $template[$type.'Signature']['s'],
                  $chainId
          );

          if($result['publicKey'] == $template[$type.'PublicKey']){
            $returnMessage[$type.'PublicKey'] = 'found match';
          }else{
            $returnMessage['PublicKey'] = 'no match for type: '.$type.' public key is'.$result['publicKey'];
          }

          if($result['address'] == strtolower($template[$type.'Address'])){
            $returnMessage[$type.'Address'] = 'found match';
          }else{
            $returnMessage['Address'] = 'no match for type: '.$type.' address is'.$result['address'];
          }

        } catch (\Throwable $e) {

            return response()->json(['error' => $e->getMessage()],400);
        }

        return response()->json(['data' => $returnMessage], 200);



      }
}
