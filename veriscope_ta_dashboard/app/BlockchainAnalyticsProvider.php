<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlockchainAnalyticsProvider extends Model
{

    protected $fillable = ['name', 'description', 'key', 'enabled'];

}
