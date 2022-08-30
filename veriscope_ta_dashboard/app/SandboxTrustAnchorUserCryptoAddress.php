<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SandboxTrustAnchorUserCryptoAddress extends Model
{

	protected $fillable = ['crypto_address', 'crypto_type', 'crypto_proof', 'sandbox_trust_anchor_user_id'];

  public function SandboxTrustAnchorUser() {
    return $this->belongsTo('App\SandboxTrustAnchorUser');
  }

}
