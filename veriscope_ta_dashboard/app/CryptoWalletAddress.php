<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CryptoWalletAddress extends Model
{

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

