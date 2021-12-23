<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CryptoWalletAddress extends Model
{

	protected $hidden = ['private_key', 'private_key_encrypt'];
	
    public function cryptoWalletType() {
        return $this->belongsTo('App\CryptoWalletType');
    }

    public function trustAnchorUser() {
        return $this->belongsTo('App\TrustAnchorUser');
    }
    public function trustAnchor() {
        return $this->belongsTo('App\TrustAnchor');
    }

}

