<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Traits\Searchable;

class SmartContractAttestation extends Model
{
	use Searchable;

	protected $fillable = ['transaction_hash'];
    //
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['ta_account', 'attestation_hash', 'transaction_hash', 'user_account'];
}
