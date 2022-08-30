<?php

namespace App\StateMachines;

use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use App\Rules\{CryptoAddress, CryptoAddressType, CryptoPublicKey, CryptoSignature, CryptoSignatureHash};
use Spatie\WebhookServer\WebhookCall;
use App\Transformers\KycTemplateTransformer;
use App\{KycTemplate, Constant, TrustAnchor, SmartContractAttestation};
use App\Jobs\{DataExternalJob, DataInternalJob, DataInternalIVMSJob, DataExternalStatelessJob, DataInternalStatelessJob};
use Illuminate\Support\Facades\Log;

class StatusStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [
           // Step 1 && Step 2: This is when the originator vasp receives the BENEFICIARY request or when benificiary request is created via kyc_request
          'START' => ['BE_TA_PUBLIC_KEY'],
          'BE_TA_PUBLIC_KEY' => ['BE_TA_SIGNATURE'],
          'BE_TA_SIGNATURE' => ['BE_USER_PUBLIC_KEY'],
          'BE_USER_PUBLIC_KEY' => ['BE_USER_SIGNATURE'],
          'BE_USER_SIGNATURE' => ['BE_TA_URLS'],
          'BE_TA_URLS' => ['BE_TA_VERIFIED'],
          'BE_TA_VERIFIED' => [ 'OR_TA_PUBLIC_KEY'],

          'OR_TA_PUBLIC_KEY' => ['OR_TA_SIGNATURE'],
          'OR_TA_SIGNATURE' => ['OR_USER_PUBLIC_KEY'],
          'OR_USER_PUBLIC_KEY' => ['OR_USER_SIGNATURE'],
          'OR_USER_SIGNATURE' => ['OR_TA_URLS'],
          'OR_TA_URLS' => ['OR_TA_VERIFIED'],
          'OR_TA_VERIFIED' => ['BE_KYC_UPDATE','OR_KYC_UPDATE'],

          'BE_KYC_UPDATE' => ['OR_KYC_UPDATE','BE_KYC_ACCEPTED','BE_KYC_REJECTED','OR_KYC_ACCEPTED','OR_KYC_REJECTED'],
          'OR_KYC_UPDATE' => ['BE_KYC_UPDATE','BE_KYC_ACCEPTED','BE_KYC_REJECTED','OR_KYC_ACCEPTED','OR_KYC_REJECTED'],


          'BE_KYC_ACCEPTED' => ['OR_KYC_UPDATE','OR_KYC_REJECTED','OR_KYC_ACCEPTED'],
          'BE_KYC_REJECTED' => ['BE_KYC_UPDATE','OR_KYC_UPDATE','OR_KYC_REJECTED'],

          'OR_KYC_ACCEPTED' => ['BE_KYC_UPDATE','BE_KYC_REJECTED','BE_KYC_ACCEPTED'],
          'OR_KYC_REJECTED' => ['BE_KYC_UPDATE','OR_KYC_UPDATE','BE_KYC_REJECTED']


        ];
    }


    public function validatorForTransition($from, $to, $model): ?Validator
    {
        if ($from === 'START' && $to === 'BE_TA_PUBLIC_KEY') {
            return ValidatorFacade::make([
                'beneficiary_ta_address' => $model->beneficiary_ta_address,
                'beneficiary_ta_public_key' => $model->beneficiary_ta_public_key,
            ], [
                'beneficiary_ta_address' => ['required', new CryptoAddress('ETH'), new CryptoAddressType('beneficiary', $model->attestation_hash) ],
                'beneficiary_ta_public_key' => ['required', new CryptoPublicKey($model->beneficiary_ta_address)]
            ]);
        }

        if ($from === 'BE_TA_PUBLIC_KEY' && $to === 'BE_TA_SIGNATURE') {
            return ValidatorFacade::make([
                'beneficiary_ta_signature_hash' => $model->beneficiary_ta_signature_hash,
                'beneficiary_ta_signature' => $model->beneficiary_ta_signature,
            ], [
                'beneficiary_ta_signature_hash' => ['required', new CryptoSignatureHash($model->beneficiary_ta_address, $model->beneficiary_ta_public_key, $model->beneficiary_ta_signature, 'VERISCOPE_TA') ],
                'beneficiary_ta_signature' => ['required']
            ]);
        }


        if ($from === 'BE_TA_SIGNATURE' && $to === 'BE_USER_PUBLIC_KEY') {
            return ValidatorFacade::make([
                'beneficiary_user_address' => $model->beneficiary_user_address,
                'beneficiary_user_public_key' => $model->beneficiary_user_public_key,
            ], [
                'beneficiary_user_address' => ['required', new CryptoAddress('ETH')],
                'beneficiary_user_public_key' => ['required', new CryptoPublicKey($model->beneficiary_user_address)]
            ]);
        }


        if ($from === 'BE_USER_PUBLIC_KEY' && $to === 'BE_USER_SIGNATURE') {
            return ValidatorFacade::make([
                'beneficiary_user_signature_hash' => $model->beneficiary_user_signature_hash,
                'beneficiary_user_signature' => $model->beneficiary_user_signature,
            ], [
                'beneficiary_user_signature_hash' => ['required', new CryptoSignatureHash($model->beneficiary_user_address, $model->beneficiary_user_public_key, $model->beneficiary_user_signature) ],
                'beneficiary_user_signature' => ['required']
            ]);
        }

        if ($from === 'BE_USER_SIGNATURE' && $to === 'BE_TA_URLS') {
            return ValidatorFacade::make([
                'sender_ta_url' => $model->sender_ta_url,
                'beneficiary_ta_url' => $model->beneficiary_ta_url,
            ], [
                'sender_ta_url' => ['required', 'url' ],
                'beneficiary_ta_url' => ['required', 'url']
            ]);
        }

        if ($from === 'BE_TA_URLS' && $to === 'BE_TA_VERIFIED') {
            return ValidatorFacade::make([
              'beneficiary_ta_address' => $model->beneficiary_ta_address,
              'sender_ta_address' => $model->sender_ta_address,
            ], [
              'beneficiary_ta_address' => ['required', 'iexists:verified_trust_anchors,account_address' ],
              'sender_ta_address' => ['required', 'iexists:verified_trust_anchors,account_address' ],
            ]);
        }






        // ORIGINATOR
        if ($from === 'BE_TA_VERIFIED' && $to === 'OR_TA_PUBLIC_KEY') {
            return ValidatorFacade::make([
                'sender_ta_address' => $model->sender_ta_address,
                'sender_ta_public_key' => $model->sender_ta_public_key,
            ], [
                'sender_ta_address' => ['required', new CryptoAddress('ETH'), new CryptoAddressType('originator', $model->attestation_hash)],
                'sender_ta_public_key' => ['required', new CryptoPublicKey($model->sender_ta_address)]
            ]);
        }

        if ($from === 'OR_TA_PUBLIC_KEY' && $to === 'OR_TA_SIGNATURE') {
            return ValidatorFacade::make([
                'sender_ta_signature_hash' => $model->sender_ta_signature_hash,
                'sender_ta_signature' => $model->sender_ta_signature,
            ], [
                'sender_ta_signature_hash' => ['required', new CryptoSignatureHash($model->sender_ta_address, $model->sender_ta_public_key, $model->sender_ta_signature, 'VERISCOPE_TA') ],
                'sender_ta_signature' => ['required']
            ]);
        }


        if ($from === 'OR_TA_SIGNATURE' && $to === 'OR_USER_PUBLIC_KEY') {
            return ValidatorFacade::make([
                'sender_user_address' => $model->sender_user_address,
                'sender_user_public_key' => $model->sender_user_public_key,
            ], [
                'sender_user_address' => ['required', new CryptoAddress('ETH')],
                'sender_user_public_key' => ['required', new CryptoPublicKey($model->sender_user_address)]
            ]);
        }


        if ($from === 'OR_USER_PUBLIC_KEY' && $to === 'OR_USER_SIGNATURE') {
            return ValidatorFacade::make([
                'sender_user_signature_hash' => $model->sender_user_signature_hash,
                'sender_user_signature' => $model->sender_user_signature,
            ], [
                'sender_user_signature_hash' => ['required', new CryptoSignatureHash($model->sender_user_address, $model->sender_user_public_key, $model->sender_user_signature) ],
                'sender_user_signature' => ['required']
            ]);
        }


        if ($from === 'OR_USER_SIGNATURE' && $to === 'OR_TA_URLS') {
            return ValidatorFacade::make([
                'sender_ta_url' => $model->sender_ta_url,
                'beneficiary_ta_url' => $model->beneficiary_ta_url,
            ], [
                'sender_ta_url' => ['required', 'url' ],
                'beneficiary_ta_url' => ['required', 'url']
            ]);
        }


        if ($from === 'OR_TA_URLS' && $to === 'OR_TA_VERIFIED') {
            return ValidatorFacade::make([
                'beneficiary_ta_address' => $model->beneficiary_ta_address,
                'sender_ta_address' => $model->sender_ta_address,
            ], [
                'beneficiary_ta_address' => ['required', 'iexists:verified_trust_anchors,account_address' ],
                'sender_ta_address' => ['required', 'iexists:verified_trust_anchors,account_address' ],
            ]);
        }

        if( ($from === 'OR_TA_VERIFIED' || $from === 'OR_KYC_UPDATE') && $to === 'BE_KYC_UPDATE'){
          return ValidatorFacade::make([
              'beneficiary_kyc' => $model->beneficiary_kyc,
          ], [
              'beneficiary_kyc' => ['required']
          ]);
        }

        if( ($from === 'OR_TA_VERIFIED' || $from === 'BE_KYC_UPDATE') && $to === 'OR_KYC_UPDATE'){
          return ValidatorFacade::make([
              'sender_kyc' => $model->sender_kyc,
          ], [
              'sender_kyc' => ['required']
          ]);
        }



        return parent::validatorForTransition($from, $to, $model);
    }




    static function isBeneficary($value)
    {

      try {
        $ta = TrustAnchor::firstOrFail();
        $sca = SmartContractAttestation::where('attestation_hash', $value)->firstOrFail();
        Log::debug('ta->account_address');
        Log::debug($ta->account_address);

        Log::debug('sca->ta_account');
        Log::debug($sca->ta_account);

        if (strcasecmp($ta->account_address, $sca->ta_account) != 0)  {
           return true;
        } else {
           return false;
        }


      } catch (\Throwable $e) {

        return false;
      }

    }
    public function afterTransitionHooks(): array
    {
        return [

            'BE_TA_VERIFIED' => [
                function ($from, $model) {

                  $type = $model->getUserType();
                  Log::debug('getUserType');
                  Log::debug($type);
                  // If BE_TA_VERIFIED and this state is happening on the benificiary end
                  if($type === 'BENEFICIARY'){
                      // Invoke DataExternalJob('Beneficiary')
                      DataExternalJob::dispatch($model, 'BE_DATA');
                  } elseif($type === 'ORIGINATOR') {
                      // Invoke DataInternalJob('Orignator')
                      DataInternalJob::dispatch($model, 'OR_DATA_REQ');
                  }
                }
            ],
            'OR_TA_VERIFIED' => [
                function ($from, $model) {
                  $type = $model->getUserType();
                  Log::debug('getUserType');
                  Log::debug($type);
                  // If OR_TA_VERIFIED and this state is happening on the benificiary end
                  if($type === 'BENEFICIARY'){
                    // Invoke New DataInternalJob('Beneficiary')
                    DataInternalJob::dispatch($model, 'BE_KYC_REQ');
                  } elseif($type === 'ORIGINATOR') {
                    // Invoke DataExternalJob('Orignator')
                    DataExternalJob::dispatch($model, 'OR_DATA');
                  }
                }
            ],

            'BE_KYC_UPDATE' => [
              function ($from, $model) {
               $type = $model->getUserType();
               Log::debug('getUserType');
               Log::debug($type);
               // If BE_KYC_UPDATE and this state is happening on the benificiary end
               if($type === 'BENEFICIARY' && $model->webhook_status()->canBe('BE_KYC_SENT') ){
                // Invoke DataExternalJob('Beneficiary')
                DataExternalJob::dispatch($model, 'BE_KYC');
              } elseif ($type === 'ORIGINATOR' && $model->ivms_status()->canBe('BE_ENC_SENT') ) {
                DataInternalIVMSJob::dispatch($model, 'BE_ENC');
               }

              }
            ],
            'OR_KYC_UPDATE' => [
              function ($from, $model) {
              $type = $model->getUserType();
              Log::debug('getUserType');
              Log::debug($type);
              // If OR_KYC_UPDATE and this state is happening on the originator end
              if($type === 'ORIGINATOR' && $model->webhook_status()->canBe('OR_KYC_SENT') ){
                // Invoke DataExternalJob('Orignator')
                DataExternalJob::dispatch($model, 'OR_KYC');
              } elseif ($type === 'BENEFICIARY' && $model->ivms_status()->canBe('OR_ENC_SENT') ) {
                DataInternalIVMSJob::dispatch($model, 'OR_ENC');
              }

             }
           ],
            'OR_KYC_ACCEPTED' => [
              function ($from, $model) {
              $type = $model->getUserType();
              Log::debug('getUserType');
              Log::debug($type);
              if($type === 'BENEFICIARY' && $model->ivms_status()->was('OR_ENC_RECEIVED') ){
                DataExternalStatelessJob::dispatch($model, 'OR_KYC_ACCEPTED');
              } elseif ($type === 'ORIGINATOR') {
                DataInternalStatelessJob::dispatch($model, 'OR_KYC_ACCEPTED');
              }

             }
           ],
           'OR_KYC_REJECTED' => [
              function ($from, $model) {
              $type = $model->getUserType();
              Log::debug('getUserType');
              Log::debug($type);
              if($type === 'BENEFICIARY' && $model->ivms_status()->was('OR_ENC_RECEIVED') ){
                DataExternalStatelessJob::dispatch($model, 'OR_KYC_REJECTED');
              } elseif($type === 'ORIGINATOR') {
                DataInternalStatelessJob::dispatch($model, 'OR_KYC_REJECTED');
                if($model->status()->getCustomProperty('or_ivms_state_code') == "0307"){
                  DataInternalJob::dispatch($model, 'OR_KYC_REQ');
                }
              }

             }
           ],
           'BE_KYC_ACCEPTED' => [
             function ($from, $model) {
             $type = $model->getUserType();
             Log::debug('getUserType');
             Log::debug($type);
             if($type === 'ORIGINATOR' && $model->ivms_status()->was('BE_ENC_RECEIVED') ){
               DataExternalStatelessJob::dispatch($model, 'BE_KYC_ACCEPTED');
             } elseif($type === 'BENEFICIARY') {
               DataInternalStatelessJob::dispatch($model, 'BE_KYC_ACCEPTED');
             }

            }
           ],
           'BE_KYC_REJECTED' => [
              function ($from, $model) {
              $type = $model->getUserType();
              Log::debug('getUserType');
              Log::debug($type);
              if($type === 'ORIGINATOR' && $model->ivms_status()->was('BE_ENC_RECEIVED') ){
                DataExternalStatelessJob::dispatch($model, 'BE_KYC_REJECTED');
              } elseif($type === 'BENEFICIARY') {
                DataInternalStatelessJob::dispatch($model, 'BE_KYC_REJECTED');
                if($model->status()->getCustomProperty('be_ivms_state_code') == "0307"){
                  DataInternalJob::dispatch($model, 'BE_KYC_REQ');
                }
              }

             }
           ]
        ];
    }


    public function defaultState(): ?string
    {
        return 'START';
    }
}
