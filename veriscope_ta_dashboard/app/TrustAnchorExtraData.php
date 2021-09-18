<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class TrustAnchorExtraData extends Model
{
    use Searchable;
    //
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['endpoint_name'];

    protected $table = 'trust_anchor_extra_datas';
}
