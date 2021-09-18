<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class TrustAnchorExtraDataUnique extends Model
{

	protected $fillable = ['key_value_pair_name'];

    use Searchable;
    //
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['key_value_pair_name', 'key_value_pair_value', 'trust_anchor_address'];
}
