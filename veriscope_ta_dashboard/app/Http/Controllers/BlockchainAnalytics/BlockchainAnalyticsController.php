<?php

namespace App\Http\Controllers\BlockchainAnalytics;

use Session;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Constant;
use App\Http\Controllers\BlockchainAnalytics\{CrystalBlockchainAnalyticsController, MerkleScienceAnalyticsController};

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
            $constants = Constant::all();
            foreach($constants as $constant) {
                if ($constant->name == 'crystal_api_key' && $constant->value) {
                    $enabled = Constant::where('name', 'crystal_enabled')->first();
                    if (!$user || $enabled['value'] == '1')  {
                        if (!isset($data['ba_provider']) || $data['ba_provider']['name'] == 'Crystal') {
                            Log::debug('Crystal init');
                            (new CrystalBlockchainAnalyticsController($data, $constant->value, $user));
                        }
                    }
                }
                if ($constant->name == 'merkle_api_key' && $constant->value) {
                    $enabled = Constant::where('name', 'merkle_enabled')->first();
                    if (!$user || $enabled['value'] == '1')  {
                        if (!isset($data['ba_provider']) || $data['ba_provider']['name'] == 'Merkle Science') {
                            Log::debug('Merkle init');
                            (new MerkleScienceAnalyticsController($data, $constant->value, $user));
                        }
                    }
                }
            } 
    }
}


?>