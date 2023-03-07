<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\{Constant,KycTemplate,KycTemplateState,SmartContractAttestation, TrustAnchor, TrustAnchorUser, CryptoWalletAddress, TrustAnchorExtraDataUnique};
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\EloquentEncryption;
use App\Transformers\{KycTemplateTransformer};
use Spatie\WebhookServer\WebhookCall;
use App\Support\EthereumToolsUtils;

class KycTemplateV1Controller extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->messageJSON = "VERISCOPE";
    }

    public function kyc_template_version()
    {
      $versionFile = json_decode(Storage::disk('local')->get('version.json'));
      echo "Veriscope version for " . $_SERVER['SERVER_NAME'] . " is '" . "<span style='color:red;font-weight: bold;'>" . $versionFile->veriscopeVersion . "</span>" . "'";
    }

    public function kyc_template_v1_reply($eventType, $attestation_hash, $test, $ta_account)
    {
      #prepare the template
      $ta = TrustAnchor::where('account_address', $ta_account)->firstOrFail();
      $taedu = TrustAnchorExtraDataUnique::where('trust_anchor_address', 'ILIKE', $ta->account_address)->where('key_value_pair_name', 'API_URL')->first();
      $url   = $taedu->key_value_pair_value;
      $sca = SmartContractAttestation::where('attestation_hash', $attestation_hash)->firstOrFail();
      $kt = new KycTemplate;
      $kt->attestation_hash = $attestation_hash;
      $kt->system_ta_account = $ta->account_address;
      $kt->owner = (substr($eventType, 0, 2) === 'BE') ? 'BENEFICIARY' : 'ORIGINATOR';
      $trustAnchorType = $kt->getUserType();

      switch ($eventType) {
        case 'NEW_ATTESTATION':
          //If trustAnchorType is ORIGINATOR and address is linked to ta_account_address which has  ta_account_type as BENEFICIARY
          if($trustAnchorType === 'ORIGINATOR' && $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_account_type === 'BENEFICIARY'){
            $kt->coin_blockchain = $sca->coin_blockchain;
            $kt->coin_token = $sca->coin_token;
            $kt->coin_address = $sca->coin_address;
            $kt->coin_memo = $sca->coin_memo;
            $kt->coin_transaction_hash = '';
            $kt->coin_transaction_value = '';
            $kt->sender_ta_address = $sca->ta_account;
            $kt->sender_user_address = $sca->user_account;
            $kt->beneficiary_ta_address = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_account_address;
            $kt->beneficiary_ta_public_key = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_public_key;
            $kt->beneficiary_ta_signature_hash = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_signature_hash;
            $kt->beneficiary_ta_signature = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_signature;
            $kt->beneficiary_user_address = $test->SandboxTrustAnchorUser->account_address;
            $kt->beneficiary_user_public_key = $test->SandboxTrustAnchorUser->public_key;
            $kt->beneficiary_user_signature_hash = $test->SandboxTrustAnchorUser->signature_hash;
            $kt->beneficiary_user_signature = $test->SandboxTrustAnchorUser->signature;
            $kt->beneficiary_user_address_crypto_proof = $test->crypto_proof;
            $kycTemplateJSON = fractal()->item($kt)->transformWith(new KycTemplateTransformer())->toArray();

            WebhookCall::create()
            ->url($url)
            ->meta(['invokedMethod' => 'BE_DATA', 'hasState' => false])
            ->payload([
              "eventType" => 'BE_DATA',
              "kycTemplate" => $kycTemplateJSON['data']
            ])
            ->doNotSign()
            ->dispatch();
          }
          break;
        case 'BE_DATA':
        //If address is linked to ta_account_address which has  ta_account_type as ORIGINATOR
        if($test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_account_type === 'ORIGINATOR'){
          $kt = KycTemplate::where(['attestation_hash' => $attestation_hash])->firstOrFail();
          $kt->sender_ta_address = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_account_address;
          $kt->sender_ta_public_key = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_public_key;
          $kt->sender_ta_signature_hash = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_signature_hash;
          $kt->sender_ta_signature = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_signature;
          $kt->sender_user_address = $test->SandboxTrustAnchorUser->account_address;
          $kt->sender_user_public_key = $test->SandboxTrustAnchorUser->public_key;
          $kt->sender_user_signature_hash = $test->SandboxTrustAnchorUser->signature_hash;
          $kt->sender_user_signature = $test->SandboxTrustAnchorUser->signature;

          $kycTemplateJSON = fractal()->item($kt)->transformWith(new KycTemplateTransformer())->toArray();

          WebhookCall::create()
          ->url($url)
          ->meta(['invokedMethod' => 'OR_DATA', 'hasState' => false])
          ->payload([
            "eventType" => 'OR_DATA',
            "kycTemplate" => $kycTemplateJSON['data']
          ])
          ->doNotSign()
          ->dispatch();
        }
        break;
        case 'OR_DATA':
        //If trustAnchorType is ORIGINATOR and address is linked to ta_account_address which has  ta_account_type as BENEFICIARY
        if($trustAnchorType === 'ORIGINATOR' && $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_account_type === 'BENEFICIARY'){
          $kt = KycTemplate::where(['attestation_hash' => $attestation_hash])->firstOrFail();
          $kt->coin_blockchain = $sca->coin_blockchain;
          $kt->coin_token = $sca->coin_token;
          $kt->coin_address = $sca->coin_address;
          $kt->coin_memo = $sca->coin_memo;
          $kt->coin_transaction_hash = '';
          $kt->coin_transaction_value = '';
          $kt->sender_ta_address = $sca->ta_account;
          $kt->sender_user_address = $sca->user_account;
          $kt->beneficiary_ta_address = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_account_address;
          $kt->beneficiary_ta_public_key = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_public_key;
          $kt->beneficiary_ta_signature_hash = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_signature_hash;
          $kt->beneficiary_ta_signature = $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_signature;
          $kt->beneficiary_user_address = $test->SandboxTrustAnchorUser->account_address;
          $kt->beneficiary_user_public_key = $test->SandboxTrustAnchorUser->public_key;
          $kt->beneficiary_user_signature_hash = $test->SandboxTrustAnchorUser->signature_hash;
          $kt->beneficiary_user_signature = $test->SandboxTrustAnchorUser->signature;
          $kt->beneficiary_user_address_crypto_proof = $test->crypto_proof;
          $beneficiaryJson = Storage::disk('local')->get('ivms_sandbox_PCF_as_beneficiaryVASP.json');
          $kt->beneficiary_kyc = EthereumToolsUtils::encryptData($kt->sender_user_public_key, $beneficiaryJson);
          $kycTemplateJSON = fractal()->item($kt)->transformWith(new KycTemplateTransformer())->toArray();

          WebhookCall::create()
          ->url($url)
          ->meta(['invokedMethod' => 'BE_KYC_UPDATE', 'hasState' => false])
          ->payload([
            "eventType" => 'BE_KYC_UPDATE',
            "kycTemplate" => $kycTemplateJSON['data']
          ])
          ->doNotSign()
          ->dispatch();

        }
        break;
        case 'OR_KYC':

        //If trustAnchorType is ORIGINATOR and address is linked to ta_account_address which has  ta_account_type as BENEFICIARY
        if($trustAnchorType === 'ORIGINATOR' && $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_account_type === 'BENEFICIARY'){
          $kt = KycTemplate::where(['attestation_hash' => $attestation_hash])->firstOrFail();
          // Reversing sides
          $kycTemplateJSON = fractal()->item($kt)->transformWith(new KycTemplateTransformer())->toArray();

          parse_str($kt->test_vars, $output);
          $kycStateMachine = $output['ivms_state_code'] ? $output['ivms_state_code'] : "0202";

          WebhookCall::create()
          ->url($url)
          ->meta(['invokedMethod' => 'OR_KYC_ACCEPTED', 'hasState' => false])
          ->payload([
            "eventType" => 'OR_KYC_ACCEPTED',
            "kycStateMachine" => ["code" => $kycStateMachine],
            "kycTemplate" => $kycTemplateJSON['data']
          ])
          ->doNotSign()
          ->dispatch();

        }
        break;
        case 'BE_KYC':
        //If trustAnchorType is BENEFICIARY and address is linked to ta_account_address which has  ta_account_type as ORIGINATOR
        if($trustAnchorType === 'BENEFICIARY' && $test->SandboxTrustAnchorUser->SandboxTrustAnchor->ta_account_type === 'ORIGINATOR'){

          $kt = KycTemplate::where(['attestation_hash' => $attestation_hash])->firstOrFail();
          $orginatorJson = Storage::disk('local')->get('ivms_sandbox_Paycase_as_originatingVASP.json');
          $kt->sender_kyc = EthereumToolsUtils::encryptData($kt->beneficiary_user_public_key, $orginatorJson);
          $kycTemplateJSON = fractal()->item($kt)->transformWith(new KycTemplateTransformer())->toArray();
          WebhookCall::create()
          ->url($url)
          ->meta(['invokedMethod' => 'OR_KYC_UPDATE', 'hasState' => false])
          ->payload([
            "eventType" => 'OR_KYC_UPDATE',
            "kycTemplate" => $kycTemplateJSON['data']
          ])
          ->doNotSign()
          ->dispatch();

          parse_str($kt->test_vars, $output);
          $kycStateMachine = $output['ivms_state_code'] ? $output['ivms_state_code'] : "0202";

          WebhookCall::create()
          ->url($url)
          ->meta(['invokedMethod' => 'BE_KYC_ACCEPTED', 'hasState' => false])
          ->payload([
            "eventType" => 'BE_KYC_ACCEPTED',
            "kycStateMachine" => ["code" => $kycStateMachine ],
            "kycTemplate" => $kycTemplateJSON['data']
          ])
          ->doNotSign()
          ->dispatch();

        }
        break;
        default:
          // code...
          //
          Log::debug("defaultState Trigger");

          break;
      }
    }


    public function kyc_template_v1_request(Request $request) {

       $kycTemplateDecode = $request->get('kycTemplate','');

       $kycStateMachineDecode =  $request->get('kycStateMachine','');

       try {

         #prepare the template
         $sca = SmartContractAttestation::where('attestation_hash', $kycTemplateDecode['AttestationHash'])->firstOrFail();

         // event type
         $eventType =  $request->get('eventType','');

         if (!empty($kycStateMachineDecode)) {
          $owner = (substr($eventType, 0, 2) === 'BE') ? 'BENEFICIARY' : 'ORIGINATOR';
         } else {
          $owner = (substr($eventType, 0, 2) === 'BE') ? 'ORIGINATOR' : 'BENEFICIARY';
         }


         // get trust anchor type
         $ta_account = ($owner  === 'ORIGINATOR') ? $sca->ta_account : $kycTemplateDecode['BeneficiaryTAAddress'];

         #prepare the template
         $kt = KycTemplate::firstOrCreate(['attestation_hash' => $kycTemplateDecode['AttestationHash'], 'owner' => $owner, 'system_ta_account' => $ta_account]);

         $kt->coin_blockchain = $sca->coin_blockchain;
         $kt->coin_token = $sca->coin_token;
         $kt->coin_address = $sca->coin_address;
         $kt->coin_memo = $sca->coin_memo;
         $kt->coin_transaction_hash = $kycTemplateDecode['CoinTransactionHash'];
         $kt->coin_transaction_value = $kycTemplateDecode['CoinTransactionValue'];
         $kt->sender_ta_address = $sca->ta_account;
         $kt->sender_user_address = $sca->user_account;
         $kt->save();


        $trustAnchorType = $kt->getUserType();

        if($trustAnchorType === 'ORIGINATOR') {

         //Transition Step 1: BE_TA_PUBLIC_KEY
         if($kt->status()->canBe('BE_TA_PUBLIC_KEY')){
           $kt->beneficiary_ta_address = $kycTemplateDecode['BeneficiaryTAAddress'];
           $kt->beneficiary_ta_public_key = $kycTemplateDecode['BeneficiaryTAPublicKey'];
           $kt->save();
           $kt->status()->transitionTo($to = 'BE_TA_PUBLIC_KEY');
         }
         //Transition Step 2: BE_TA_SIGNATURE
         if($kt->status()->canBe('BE_TA_SIGNATURE')){
           $kt->beneficiary_ta_signature_hash = $kycTemplateDecode['BeneficiaryTASignatureHash'];
           $kt->beneficiary_ta_signature = json_encode($kycTemplateDecode['BeneficiaryTASignature']);
           $kt->save();
           $kt->status()->transitionTo($to = 'BE_TA_SIGNATURE');
         }

         //Transition Step 3: BE_USER_PUBLIC_KEY
         if($kt->status()->canBe('BE_USER_PUBLIC_KEY')){
           $kt->beneficiary_user_address = $kycTemplateDecode['BeneficiaryUserAddress'];
           $kt->beneficiary_user_public_key = $kycTemplateDecode['BeneficiaryUserPublicKey'];
           $kt->save();
           $kt->status()->transitionTo($to = 'BE_USER_PUBLIC_KEY');
         }

         //Transition Step 4: BE_USER_SIGNATURE
         if($kt->status()->canBe('BE_USER_SIGNATURE')){
           $kt->beneficiary_user_signature_hash = $kycTemplateDecode['BeneficiaryUserSignatureHash'];
           $kt->beneficiary_user_signature = json_encode($kycTemplateDecode['BeneficiaryUserSignature']);
           $kt->save();
           $kt->status()->transitionTo($to = 'BE_USER_SIGNATURE');
         }


         //Transition Step 4a: BE_CRYPTO_PROOF_VERIFIED
         if($kt->status()->canBe('BE_CRYPTO_PROOF_VERIFIED')){

           $json_encode = ($kycTemplateDecode['BeneficiaryUserAddressCryptoProof']) ? json_encode($kycTemplateDecode['BeneficiaryUserAddressCryptoProof']) : null;
           $kt->beneficiary_user_address_crypto_proof = $json_encode;
           $kt->save();
           // BE_CRYPTO_PROOF_NOT_PROVIDED
           if (empty($json_encode) || $json_encode === null) {
               $kt->status()->transitionTo($to = 'BE_CRYPTO_PROOF_NOT_PROVIDED');
            }
           // BE_CRYPTO_PROOF_VERIFIED
           else {
              $kt->status()->transitionTo($to = 'BE_CRYPTO_PROOF_VERIFIED');
           }

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


         //Transition Step 6: BE_TA_VERIFIED
         if($kt->status()->canBe('BE_TA_VERIFIED')){
           $kt->status()->transitionTo($to = 'BE_TA_VERIFIED');
         }

         //Transition Step 7: BE_KYC_UPDATE
         if(empty($kycStateMachineDecode) && $kt->status()->canBe('BE_KYC_UPDATE') && !empty($kycTemplateDecode['BeneficiaryKYC']) && !$kt->status()->was('BE_KYC_ACCEPTED') ){
           $kt->beneficiary_kyc = $kycTemplateDecode['BeneficiaryKYC'];
           $kt->save();
           $kt->status()->transitionTo($to = 'BE_KYC_UPDATE');
         }


         if (!empty($kycStateMachineDecode) && $kt->status()->canBe('OR_KYC_ACCEPTED') || $kt->status()->canBe('OR_KYC_REJECTED') ) {
           if (!empty($kycStateMachineDecode['code'])) {
             $newState = ($kycStateMachineDecode['code'] == "0202") ? 'OR_KYC_ACCEPTED' : 'OR_KYC_REJECTED';
             $kt->status()->transitionTo($to = $newState, ['or_ivms_state_code' => $kycStateMachineDecode['code'] ] );
           }
         }


       } elseif($trustAnchorType == 'BENEFICIARY') {

          //Transition Step 1: OR_TA_PUBLIC_KEY
          if($kt->status()->canBe('OR_TA_PUBLIC_KEY')){
            $kt->sender_ta_address = $sca->ta_account;
            $kt->sender_ta_public_key = $kycTemplateDecode['SenderTAPublicKey'];
            $kt->save();
            $kt->status()->transitionTo($to = 'OR_TA_PUBLIC_KEY');
          }

          //Transition Step 2: OR_TA_SIGNATURE
          if($kt->status()->canBe('OR_TA_SIGNATURE')){
            $kt->sender_ta_signature_hash = $kycTemplateDecode['SenderTASignatureHash'];
            $kt->sender_ta_signature = json_encode($kycTemplateDecode['SenderTASignature']);
            $kt->save();
            $kt->status()->transitionTo($to = 'OR_TA_SIGNATURE');
          }

          //Transition Step 3: OR_USER_PUBLIC_KEY
          if($kt->status()->canBe('OR_USER_PUBLIC_KEY')){
            $kt->sender_user_address = $sca->user_account;
            $kt->sender_user_public_key = $kycTemplateDecode['SenderUserPublicKey'];
            $kt->save();
            $kt->status()->transitionTo($to = 'OR_USER_PUBLIC_KEY');
          }
          //Transition Step 4: OR_USER_SIGNATURE
          if($kt->status()->canBe('OR_USER_SIGNATURE')){
            $kt->sender_user_signature_hash = $kycTemplateDecode['SenderUserSignatureHash'];
            $kt->sender_user_signature =  json_encode($kycTemplateDecode['SenderUserSignature']);
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


          //Transition Step 9: OR_KYC_UPDATE
          if(empty($kycStateMachineDecode) && $kt->status()->canBe('OR_KYC_UPDATE') && !empty($kycTemplateDecode['SenderKYC']) && !$kt->status()->was('OR_KYC_ACCEPTED') ){
            $kt->sender_kyc = $kycTemplateDecode['SenderKYC'];
            $kt->save();
            $kt->status()->transitionTo($to = 'OR_KYC_UPDATE');
          }


          if (!empty($kycStateMachineDecode) && $kt->status()->canBe('BE_KYC_ACCEPTED') || $kt->status()->canBe('BE_KYC_REJECTED') ) {
            if (!empty($kycStateMachineDecode['code'])) {
              $newState = ($kycStateMachineDecode['code'] == "0202") ? 'BE_KYC_ACCEPTED' : 'BE_KYC_REJECTED';
              $kt->status()->transitionTo($to = $newState, ['be_ivms_state_code' => $kycStateMachineDecode['code'] ] );
            }
          }

        }


         $kycTemplateJSON = fractal()->item($kt)->transformWith(new KycTemplateTransformer())->toArray();

         return response()->json($kycTemplateJSON, 200);


       } catch (\Throwable $throwable) {

         if($throwable instanceof ValidationException){
           return response()->json($throwable->errors(),400);
         } else {
           return response()->json([
             "trace" => $throwable->getTrace(),
             "message" => $throwable->getMessage(),
             "currentState" => $kt->status,
             "beneficiary_ta_address" => $kt->beneficiary_ta_address,
             "sender_ta_address" => $kt->sender_ta_address,
             "currentWebhookState" => $kt->webhook_status
            ],400);
         }

       }

    }


}
