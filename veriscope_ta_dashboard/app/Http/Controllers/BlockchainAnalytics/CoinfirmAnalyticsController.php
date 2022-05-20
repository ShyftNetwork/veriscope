<?php

namespace App\Http\Controllers\BlockchainAnalytics;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
use Illuminate\Support\Facades\DB;
use App\{BlockchainAnalyticsProvider, BlockchainAnalyticsAddress};
use App\Events\{ContractsInstantiate};


class CoinfirmAnalyticsController extends Controller {
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct($data, $api_key, $user) {
        Log::debug('CoinfirmAnalyticsController __construct');
        
        if (!$user) {
            $data['coin_address'] = $data['wallet'];
            $data['user_account'] = 'unknown';
            $data['coin_blockchain'] = $data['network']['ticker'];
            $data['ta_account']['account_address'] = 'unknown';
        }

        try {
            $client = new Client();
            $response = $client->request('GET', 'https://api.coinfirm.com/v3/reports/aml/full/' . $data['coin_address'] . '?v=2', [
                'headers' => [
                    'authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);
            
            $jsonResponse = json_decode($response->getBody(), true);
            return $this->save_response_to_db($jsonResponse, $data);

        } catch (ClientException $e) {  
            $response = $e->getResponse();
            $status_code = $response->getStatusCode();
            $jsonResponse = json_decode((string) $response->getBody(), true);
            return $this->save_error_to_db($jsonResponse, $data, $status_code);
        };

       
    
    }

    function save_response_to_db($response, $data) {
        $coinfirm = BlockchainAnalyticsProvider::where('name', 'Coinfirm')->first();
        $custodian = '';


        if (isset($response['profile_section']['owner']['name'])) $custodian = $response['profile_section']['owner']['name'];
        

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $coinfirm->id,
                   'trust_anchor' => $data['ta_account']['account_address'],
                   'user_account' => $data['user_account'],
                   'blockchain' => strtolower($data['coin_blockchain']),
                   'crypto_address' =>  $data['coin_address'],
                   'custodian' => $custodian,
                   'response' => json_encode($response),
                   'response_status_code' => 200
            )
       );
       
       $data['report_id'] = $report;
       $data['message'] = 'report-created';

       broadcast(new ContractsInstantiate($data));
    }

    function save_error_to_db($response, $data, $status_code) {
        $coinfirm = BlockchainAnalyticsProvider::where('name', 'Coinfirm')->first();

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $coinfirm->id,
                   'trust_anchor' => $data['ta_account']['account_address'],
                   'user_account' => $data['user_account'],
                   'blockchain' => strtolower($data['coin_blockchain']),
                   'crypto_address' => $data['coin_address'],
        
                   'response' => json_encode($response),
                   'response_status_code' => $status_code
            )
       );

       $data['report_id'] = $report;
       $data['message'] = 'report-created';

       broadcast(new ContractsInstantiate($data));

    }
}

?>