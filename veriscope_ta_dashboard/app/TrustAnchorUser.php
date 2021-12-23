<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrustAnchorUser extends Model
{

    protected $fillable = ['prefname', 'password', 'trust_anchor_id', 'primary_identifier', 'secondary_identifier', 'name_identifier_type', 'address_type', 'street_name', 'building_number', 'postcode', 'town_name', 'country_sub_division', 'country', 'national_identifier', 'national_identifier_type', 'country_of_issue', 'registration_authority', 'date_of_birth', 'place_of_birth', 'country_of_residence'];
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */

    protected $hidden = ['private_key', 'private_key_encrypt'];
    
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
