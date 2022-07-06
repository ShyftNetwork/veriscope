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


    protected $fillable = ['attestation_hash'];

    protected $searchable = ['attestation_hash', 'beneficiary_ta_address', 'sender_ta_address', 'beneficiary_user_address', 'sender_user_address'];

		public $stateMachines = [
        'status' => StatusStateMachine::class,
        'webhook_status' => WebhookStatusStateMachine::class,
        'ivms_status' => IvmsStatusStateMachine::class
    ];


    function getUserType()
    {

      try {
        $ta = TrustAnchor::firstOrFail();
        $sca = SmartContractAttestation::where('attestation_hash', $this->attestation_hash)->firstOrFail();
        
        if (strcasecmp($ta->account_address, $sca->ta_account) != 0)  {
           return 'BENEFICIARY';
        } else {
           return 'ORIGINATOR';
        }
      } catch (\Throwable $e) {
        return false;
      }

    }

}
