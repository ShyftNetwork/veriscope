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


class CrystalBlockchainAnalyticsController extends Controller {
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct($data, $api_key, $user) {
        Log::debug('CrystalBlockchainAnalyticsController __construct');
        
        if (!$user) {
            $data['documents_matrix_encrypted'] = $data['wallet'];
            $data['user_address'] = 'unknown';
            $data['availability_address_encrypted'] = $data['network']['ticker'];
            $data['ta_account']['account_address'] = 'unknown';
        }

        
        $payload = [];
        $payload['direction'] = "withdrawal";
        $payload['address'] = $data['documents_matrix_encrypted'];
        $payload['name'] = $data['user_address'] ;
        $payload['currency'] = strtolower($data['availability_address_encrypted'])  ;


        try {
            $client = new Client();
            $response = $client->request('POST', 'https://apiexpert.crystalblockchain.com/monitor/tx/add', [
                'headers' => [
                    'X-Auth-Apikey' => $api_key,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($payload)
            ]);
            
            $jsonResponse = json_decode($response->getBody(), true);
            return $this->save_response_to_db($jsonResponse, $data);

        } catch (ClientException $e) {
            $response = $e->getResponse();
            $jsonResponse = json_decode((string) $response->getBody(), true);
            return $this->save_error_to_db($jsonResponse, $data);
        };
    
    }

    function save_response_to_db($response, $data) {
        $crystal = BlockchainAnalyticsProvider::where('name', 'Crystal')->first();
        $custodian = '';


        if (isset($response['data']['counterparty']['name'])) $custodian = $response['data']['counterparty']['name'];
        

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $crystal->id,
                   'trust_anchor' => $data['ta_account']['account_address'],
                   'user_account' => $data['user_address'],
                   'blockchain' => strtolower($data['availability_address_encrypted']),
                   'crypto_address' =>  $data['documents_matrix_encrypted'],
                   'custodian' => $custodian,
                   'response' => json_encode($response),
                   'response_status_code' => 200
            )
       );
       
       $data['report_id'] = $report;
       $data['message'] = 'report-created';

       broadcast(new ContractsInstantiate($data));
    }

    function save_error_to_db($response, $data) {
        $crystal = BlockchainAnalyticsProvider::where('name', 'Crystal')->first();

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $crystal->id,
                   'trust_anchor' => $data['ta_account']['account_address'],
                   'user_account' => $data['user_address'],
                   'blockchain' => strtolower($data['availability_address_encrypted']),
                   'crypto_address' => $data['documents_matrix_encrypted'],
        
                   'response' => json_encode($response),
                   'response_status_code' => $response['meta']['error_code']
            )
       );

       $data['report_id'] = $report;
       $data['message'] = 'report-created';

       broadcast(new ContractsInstantiate($data));

    }
}

?>