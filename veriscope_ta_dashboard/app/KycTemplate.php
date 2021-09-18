<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class KycTemplate extends Model
{

	use Searchable;

    protected $fillable = ['attestation_hash'];

    protected $searchable = ['attestation_hash', 'beneficiary_ta_address', 'sender_ta_address', 'beneficiary_user_address', 'sender_user_address'];

}
