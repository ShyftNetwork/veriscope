<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SandboxTrustAnchorUser extends Model
{

    protected $fillable = ['prefname','account_address','public_key','private_key','signature_hash','signature','ivms_data','sandbox_trust_anchor_id'];

    protected $hidden = ['private_key'];

    public function SandboxTrustAnchor() {
      return $this->belongsTo('App\SandboxTrustAnchor');
    }

    public function SandboxTrustAnchorUserCryptoAddress() {
      return $this->hasMany('App\SandboxTrustAnchorUserCryptoAddress')->orderBy('created_at', 'DESC');
    }

}
