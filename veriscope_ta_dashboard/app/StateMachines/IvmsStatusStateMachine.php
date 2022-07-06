<?php

namespace App\StateMachines;

use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Spatie\WebhookServer\WebhookCall;
use App\Transformers\KycTemplateTransformer;
use App\{Constant, TrustAnchor, SmartContractAttestation};
use App\Jobs\{DataInternalJob};
use Illuminate\Support\Facades\Log;

class IvmsStatusStateMachine extends StateMachine
{
    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [

          'START' => ['BE_ENC_SENT','OR_ENC_SENT'],
          'BE_ENC_SENT' => ['BE_ENC_FAILED','BE_ENC_RECEIVED'],
          'BE_ENC_FAILED' => ['BE_ENC_SENT'],
          'BE_ENC_RECEIVED' => ['BE_ENC_SENT'],

          'OR_ENC_SENT' => ['OR_ENC_FAILED','OR_ENC_RECEIVED'],
          'OR_ENC_FAILED' => ['OR_ENC_SENT'],
          'OR_ENC_RECEIVED' => ['OR_ENC_SENT']

        ];
    }



    public function defaultState(): ?string
    {
        return 'START';
    }
}
