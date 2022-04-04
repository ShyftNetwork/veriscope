<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class TrustAnchorExtraDataUniqueValidation extends Model
{

	protected $fillable = ['transaction_hash'];
		
    use Searchable;
    //
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['validator_address', 'trust_anchor_address', 'key_value_pair_name'];
}
