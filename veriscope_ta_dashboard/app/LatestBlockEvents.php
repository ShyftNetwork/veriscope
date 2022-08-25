<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LatestBlockEvents extends Model
{

    protected $fillable = ['type', 'blockNumber'];

}
