<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrustAnchorUserCryptoAddress extends Model
{

	protected $fillable = ['crypto_type', 'crypto_address', 'trust_anchor_user_id'];
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */

    public function trustAnchorUser() {
        return $this->belongsTo('App\TrustAnchorUser');
    }

}
