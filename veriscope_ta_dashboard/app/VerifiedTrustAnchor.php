<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class VerifiedTrustAnchor extends Model
{
    use Searchable;

    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['account_address'];

	protected $fillable = ['account_address'];
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */

}