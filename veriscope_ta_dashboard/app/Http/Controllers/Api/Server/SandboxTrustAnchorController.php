<?php

namespace App\Http\Controllers\Api\Server;


class SandboxTrustAnchorController extends TrustAnchorController
{

      /**
      * Create KYC Template
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function create_kyc_template(CreateKycTemplateRequest $request)
      {
          # Assumes TA is Beneficiary
          $attestation_hash = $request->get('attestation_hash', '');
          $user_account = $request->get('user_account', '');
          $user_public_key = $request->get('user_public_key', '');
          $user_signature_hash = $request->get('user_signature_hash', '');
          $user_signature = $request->get('user_signature', '');

          $coin_transaction_hash = $request->get('coin_transaction_hash', '');
          $coin_transaction_value = $request->get('coin_transaction_value', '');
          $ivms_encrypt = $request->get('ivms_encrypt', '');


          $ivms_state_code = $request->get('ivms_state_code', '');


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
                if($kt->status()->canBe('BE_KYC_UPDATE') && !$kt->status()->was('BE_KYC_ACCEPTED') ) {
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
                if($kt->status()->canBe('OR_KYC_UPDATE') && $kt->webhook_status()->was('OR_DATA_RECEIVED') && !$kt->status()->was('OR_KYC_ACCEPTED') ) {
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
              return response()->json(['error' => $throwable->errors() ], 400);
            } else {
              return response()->json(['error' => $throwable->getTrace() ], 400);
            }


          }

      }


}
