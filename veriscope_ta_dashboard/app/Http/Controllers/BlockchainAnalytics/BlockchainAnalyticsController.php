<?php

namespace App\Http\Controllers\BlockchainAnalytics;

use Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\BlockchainAnalyticsProvider;
use App\Http\Controllers\BlockchainAnalytics\{CoinfirmAnalyticsController, CrystalBlockchainAnalyticsController, MerkleScienceAnalyticsController, ChainalysisController, EllipticAnalyticsController};

class BlockchainAnalyticsController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($data, $user) {
        Log::debug('BlockchainAnalyticsController __construct');
        if ($user) {
            $data['user_id'] = $user['id'];
        }
            $providers = BlockchainAnalyticsProvider::all();
            foreach($providers as $provider) {
                if ($provider->id == 1 && $provider->key) {
                    if (!$user || $provider['enabled'] == '1')  {
                        if (!isset($data['ba_provider']) || $data['ba_provider']['name'] == 'Crystal') {
                            Log::debug('Crystal init');
                            (new CrystalBlockchainAnalyticsController($data, $provider->key, $user));
                        }
                    }
                }
                if ($provider->id == 2 && $provider->key) {
                    if (!$user || $provider['enabled'] == '1')  {
                        if (!isset($data['ba_provider']) || $data['ba_provider']['name'] == 'Merkle Science') {
                            Log::debug('Merkle init');
                            (new MerkleScienceAnalyticsController($data, $provider->key, $user));
                        }
                    }
                }
                if ($provider->id == 3 && $provider->key) {
                    if (!$user || $provider['enabled'] == '1')  {
                        if (!isset($data['ba_provider']) || $data['ba_provider']['name'] == 'Coinfirm') {
                            Log::debug('Coinfirm init');
                            (new CoinfirmAnalyticsController($data, $provider->key, $user));
                        }
                    }
                }
                if ($provider->id == 4 && $provider->key) {
                    if (!$user || $provider['enabled'] == '1')  {
                        if (!isset($data['ba_provider']) || $data['ba_provider']['name'] == 'Chainalysis') {
                            Log::debug('Chainalysis init');
                            (new ChainalysisController($data, $provider->key, $user));
                        }
                    }
                }

                if ($provider->id == 5 && $provider->key) {
                    if (!$user || $provider['enabled'] == '1')  {
                        if (!isset($data['ba_provider']) || $data['ba_provider']['name'] == 'Elliptic') {
                            Log::debug('Elliptic init');
                            (new EllipticAnalyticsController($data, $provider->key, $user, $provider->secret_key));
                        }
                    }
                }
            } 
    }
}


?>