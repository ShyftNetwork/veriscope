<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class SmartContractTransaction extends Model
{
	use Searchable;

	protected $fillable = ['transaction_hash'];
    //
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['transaction_hash', 'from_address', 'to_address'];
}
