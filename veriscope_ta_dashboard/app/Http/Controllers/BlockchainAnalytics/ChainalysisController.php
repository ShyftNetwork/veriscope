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


class ChainalysisController extends Controller {
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct($data, $api_key, $user) {
        Log::debug('ChainalysisController __construct');
        
        $userId = '';

        if (!$user) {
            $data['coin_address'] = $data['wallet'];
            $data['user_account'] = 'unknown';
            $data['coin_blockchain'] = $data['network']['ticker'];
            $data['ta_account']['account_address'] = 'unknown';
       
        } else {
            $userId = "-" . $user['id'];
        }
        $network = BlockchainAnalyticsSupportedNetworks::where('ticker', strtolower($data['coin_blockchain']))->where('blockchain_analytics_provider_id', 4)->first();

        $payload = [];
        $payload['network'] = $network['name'];
        $payload['address'] = $data['coin_address'];
        $payload['asset'] = strtoupper($data['coin_blockchain'])  ;


        try {
            $client = new Client();
           
            $response = $client->request('POST', 'https://api.chainalysis.com/api/kyt/v1/users/veriscope' . $userId . '/withdrawaladdresses', [
                'headers' => [
                    'Token' => $api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'body' => json_encode([$payload])
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
        $chainalysis = BlockchainAnalyticsProvider::where('name', 'Chainalysis')->first();
        $custodian = '';


        if (isset($response[0]['cluster']['name'])) $custodian = $response[0]['cluster']['name'];
        

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $chainalysis->id,
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

    function save_error_to_db($response, $data,  $status_code) {
        $chainalysis = BlockchainAnalyticsProvider::where('name', 'Chainalysis')->first();

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $chainalysis->id,
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