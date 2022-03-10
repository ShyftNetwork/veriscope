<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class BlockchainAnalyticsAddress extends Model
{
    use Searchable;
    
    /**
     * The attributes that are mass searchable.
     *
     * @var array
     */
    protected $searchable = ['custodian', 'crypto_address'];

    public function provider() {
        return $this->belongsTo('App\BlockchainAnalyticsProvider','blockchain_analytics_provider_id');
    }

}
