<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrustAnchorUser extends Model
{

	protected $fillable = ['prefname', 'password', 'trust_anchor_id', 'dob', 'gender', 'jurisdiction'];
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */

    public function trustAnchor() {
        return $this->belongsTo('App\TrustAnchor');
    }

    public function trustAnchorUserAttestation() {
        return $this->hasMany('App\trustAnchorUserAttestation')->orderBy('created_at', 'DESC');
    }

    public function trustAnchorUserCryptoAddress() {
        return $this->hasMany('App\trustAnchorUserCryptoAddress')->orderBy('created_at', 'DESC');
    }

}
