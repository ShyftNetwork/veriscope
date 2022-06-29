<?php

namespace App\StateMachines;

use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Spatie\WebhookServer\WebhookCall;
use App\Transformers\KycTemplateTransformer;
use App\{Constant, TrustAnchor, SmartContractAttestation};
use App\Jobs\{DataInternalJob, DataInternalStatelessJob};
use Illuminate\Support\Facades\Log;

class WebhookStatusStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [

          'START' => ['BE_DATA_SENT','OR_DATA_REQ_SENT'],
          // BE_TA_VERIFIED -> BENEFICIARY DATA (This is when benificiary send over data to the originator)
          'BE_DATA_SENT' => ['BE_DATA_FAILED','BE_DATA_RECEIVED'],
          'BE_DATA_FAILED' => ['BE_DATA_SENT'],
          // BE_TA_VERIFIED -> ORIGINATOR DATA (This is when the originator has received the benificiary data we send a webhook call to originator backend system)
          'OR_DATA_REQ_SENT' => ['OR_DATA_REQ_FAILED','OR_DATA_REQ_RECEIVED'],
          'OR_DATA_REQ_FAILED' => ['OR_DATA_REQ_SENT'],
          'OR_DATA_REQ_RECEIVED' => ['OR_DATA_SENT'],
          // OR_TA_VERIFIED -> ORIGINATOR DATA (This is when originator send over data to the benificiary)
          'OR_DATA_SENT' => ['OR_DATA_RECEIVED','OR_DATA_FAILED'],
          'OR_DATA_FAILED' => ['OR_DATA_SENT'],


          'BE_DATA_RECEIVED' => ['BE_KYC_REQ_SENT'],
          'BE_KYC_REQ_SENT' => ['BE_KYC_REQ_FAILED','BE_KYC_REQ_RECEIVED'],
          'BE_KYC_REQ_FAILED' => ['BE_KYC_REQ_SENT'],
          //BENEFICIARY KYC DATA (This is when benificiary send over kyc to the originator)
          'BE_KYC_REQ_RECEIVED' => ['BE_KYC_SENT'],
          'BE_KYC_SENT' => ['BE_KYC_RECEIVED','BE_KYC_FAILED'],
          'BE_KYC_FAILED' => ['BE_KYC_SENT'],
          // Requested KYC if Rejected
          'BE_KYC_RECEIVED' => ['BE_KYC_REQ_SENT'],


          //ORIGINATOR KYC DATA (This is when originator send over kyc to the benificiary)
          'OR_DATA_RECEIVED' => ['OR_KYC_REQ_SENT','OR_KYC_SENT'],
          'OR_KYC_REQ_SENT' => ['OR_KYC_REQ_FAILED','OR_KYC_REQ_RECEIVED'],
          'OR_KYC_REQ_FAILED' => ['OR_KYC_REQ_SENT'],

          'OR_KYC_REQ_RECEIVED' => ['OR_KYC_SENT'],
          'OR_KYC_SENT' => ['OR_KYC_RECEIVED','OR_KYC_FAILED'],
          'OR_KYC_FAILED' => ['OR_KYC_SENT'],
          // Requested KYC if Rejected
          'OR_KYC_RECEIVED' => ['OR_KYC_REQ_SENT']
        ];
    }



    public function afterTransitionHooks(): array
    {
        return [
            'OR_DATA_RECEIVED' => [
                function ($from, $model) {
                  $type = $model->getUserType();
                  Log::debug('OR_DATA_RECEIVED');
                  Log::debug('getUserType');
                  Log::debug($type);
                  // If OR_DATA_RECEIVED and this state is happening on the originator end
                  if($type === 'ORIGINATOR' && !$model->sender_kyc ){
                    // Invoke New DataInternalJob('Orignator')
                    DataInternalJob::dispatch($model, 'OR_KYC_REQ');
                  } else {
                    $model->status()->transitionTo($to = 'OR_KYC_UPDATE');
                  }
                }
            ],
            'OR_DATA_FAILED' => [
              function ($from, $model) {
                DataInternalStatelessJob::dispatch($model, 'OR_DATA_FAILED');
              }
            ],
            'OR_KYC_FAILED' => [
              function ($from, $model) {
                DataInternalStatelessJob::dispatch($model, 'OR_KYC_FAILED');
              }
            ],
            'BE_DATA_FAILED' => [
              function ($from, $model) {
                DataInternalStatelessJob::dispatch($model, 'BE_DATA_FAILED');
              }
            ],
            'BE_KYC_FAILED' => [
              function ($from, $model) {
                DataInternalStatelessJob::dispatch($model, 'BE_KYC_FAILED');
              }
            ],

        ];
    }

    public function defaultState(): ?string
    {
        return 'START';
    }
}
