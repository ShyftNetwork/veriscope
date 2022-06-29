<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\{Constant, KycTemplate,KycTemplateState,SmartContractAttestation, TrustAnchor, TrustAnchorUser, CryptoWalletAddress, TrustAnchorExtraDataUnique};
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RichardStyles\EloquentEncryption\EloquentEncryption;
use App\Transformers\KycTemplateTransformer;
use Spatie\WebhookServer\WebhookCall;

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


    public function kyc_template_v1_request(Request $request) {

       $kycTemplateDecode = $request->get('kycTemplate','');

       $kycStateMachineDecode =  $request->get('kycStateMachine','');

       try {

         #prepare the template
         $sca = SmartContractAttestation::where('attestation_hash', $kycTemplateDecode['AttestationHash'])->firstOrFail();
         $kt = KycTemplate::firstOrCreate(['attestation_hash' => $kycTemplateDecode['AttestationHash'] ]);

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
         if($kt->status()->canBe('BE_KYC_UPDATE') && !empty($kycTemplateDecode['BeneficiaryKYC']) ){
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
          if($kt->status()->canBe('OR_KYC_UPDATE') && !empty($kycTemplateDecode['SenderKYC']) ){
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
