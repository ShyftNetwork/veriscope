<?php

namespace App\Http\Controllers\BlockchainAnalytics;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
use Illuminate\Support\Facades\DB;
use App\{BlockchainAnalyticsProvider, BlockchainAnalyticsAddress, BlockchainAnalyticsSupportedNetworks};
use App\Events\{ContractsInstantiate};


class MerkleScienceAnalyticsController extends Controller {
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct($data, $api_key, $user) {
        Log::debug('MerkleScienceAnalyticsController __construct');
        $network = isset($data['coin_blockchain']) ? $data['coin_blockchain'] : $data['network']['ticker'];       
        $networkId = BlockchainAnalyticsSupportedNetworks::where('ticker', strtolower($network))->where('blockchain_analytics_provider_id', 2)->first();

        if (!$network) return;
        if (!$networkId) $networkId['provider_network_id'] = -1;

        if (!$user) {
            $data['coin_address'] = $data['wallet'];
            $data['user_account'] = 'unknown';
            $data['coin_blockchain'] = $data['network']['ticker'];
            $data['ta_account']['account_address'] = 'unknown';
        }

        
        $payload = [];
        $payload['identifier'] = $data['coin_address'];
        $payload['currency'] = $networkId['provider_network_id'];
        

        try {
            $client = new Client();
            $response = $client->request('POST', 'https://api.merklescience.com/api/v3/addresses/', [
                'headers' => [
                    'X-API-KEY' => $api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($payload)
            ]);
            
            $jsonResponse = json_decode($response->getBody(), true);

            return $this->save_response_to_db($jsonResponse, $data);

        } catch (ClientException $e) {
            
            // Log::debug($e->getResponse()->getMessage());
            $response = $e->getResponse();
           

            $status_code = $response->getStatusCode();
            $jsonResponse = json_decode((string) $response->getBody(), true);

            return $this->save_error_to_db($jsonResponse, $data, $status_code);
        };
    
    }

    function save_response_to_db($response, $data) {
        $merkel = BlockchainAnalyticsProvider::where('name', 'Merkle Science')->first();
        $custodian = '';


        if (isset($response['tags']['owner']['tag_name_verbose'])) $custodian = $response['tags']['owner']['tag_name_verbose'];


        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $merkel->id,
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
        $merkel = BlockchainAnalyticsProvider::where('name', 'Merkle Science')->first();

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $merkel->id,
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