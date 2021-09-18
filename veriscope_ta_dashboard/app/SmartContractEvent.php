<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmartContractEvent extends Model
{
    //
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['transaction_hash', 'attestation_hash', 'user_address', 'ta_address'];
}
