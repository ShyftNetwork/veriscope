<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use App\StateMachines\{ StatusStateMachine, WebhookStatusStateMachine, IvmsStatusStateMachine};
use App\Traits\Searchable;
use App\{SmartContractAttestation, TrustAnchor};


class KycTemplate extends Model
{

    use Searchable;

    use HasStateMachines;


    protected $fillable = ['attestation_hash','system_ta_account','owner'];

    protected $hidden = ['sender_user_address_crypto_proof','sender_user_address_crypto_proof_status'];

    protected $searchable = ['attestation_hash', 'beneficiary_ta_address', 'sender_ta_address', 'beneficiary_user_address', 'sender_user_address'];

    public $stateMachines = [
        'status' => StatusStateMachine::class,
        'webhook_status' => WebhookStatusStateMachine::class,
        'ivms_status' => IvmsStatusStateMachine::class
    ];


    function getUserType()
    {
        return $this->owner;
    }

}
