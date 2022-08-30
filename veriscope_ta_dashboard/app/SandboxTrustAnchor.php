<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SandboxTrustAnchor extends Model
{

    protected $fillable = ['ta_prefname','ta_account_type','ta_account_address','ta_public_key','ta_private_key', 'ta_signature', 'ta_signature_hash'];

    protected $hidden = ['ta_private_key'];

    public function SandboxTrustAnchorUser() {
      return $this->hasMany('App\SandboxTrustAnchorUser')->orderBy('created_at', 'DESC');
    }
}
