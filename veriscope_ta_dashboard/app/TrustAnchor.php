<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class TrustAnchor extends Model
{
    use Searchable;

    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['ta_prefname'];

	protected $fillable = ['ta_prefname', 'ta_password', 'user_id'];
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function trustAnchorUser() {
        return $this->hasMany('App\TrustAnchorUser')->orderBy('created_at', 'DESC');
    }

    public function trustAnchorUserAttestation() {
        return $this->hasMany('App\trustAnchorUserAttestation')->orderBy('created_at', 'DESC');
    }

}
