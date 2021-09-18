<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrustAnchorUserAttestation extends Model
{

	protected $fillable = ['attestation_hash', 'trust_anchor_id', 'trust_anchor_user_id'];
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */

    public function trustAnchor() {
        return $this->belongsTo('App\TrustAnchor');
    }

    public function trustAnchorUser() {
        return $this->belongsTo('App\TrustAnchorUser');
    }

}
