<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Traits\ILikeSearchable;

class SmartContractAttestation extends Model
{
	use ILikeSearchable;

	protected $fillable = ['transaction_hash'];
  
 /**
  * The attributes that are mass searchable.
  *
  * @var array
  */
  protected $searchable = ['ta_account', 'attestation_hash', 'transaction_hash', 'user_account', 'coin_address', 'documents_matrix_encrypted_decoded'];
}
