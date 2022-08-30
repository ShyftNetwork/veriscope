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


class EllipticAnalyticsController extends Controller {
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct($data, $api_key, $user, $secret) {
        Log::debug('EllipticAnalyticsController __construct');
        
        $userId = '';

        if (!$user) {
            $data['coin_address'] = $data['wallet'];
            $data['user_account'] = 'unknown';
            $data['coin_blockchain'] = $data['network']['ticker'];
            $data['ta_account']['account_address'] = 'unknown';
       
        } else {
            $userId = "-" . $user['id'];
        }
        $network = BlockchainAnalyticsSupportedNetworks::where('ticker', strtolower($data['coin_blockchain']))->where('blockchain_analytics_provider_id', 5)->first();

        $payload = [];
        $payload['subject']['asset'] = strtoupper($network['ticker']);
        $payload['subject']['blockchain'] = $network['request_name'];
        $payload['subject']['type'] = "address";
        $payload['subject']['hash'] = $data['coin_address'];
        $payload['type'] = "wallet_exposure";
        $payload['customer_reference'] = "string";


        $time_of_request = (int)(microtime(true)*1000);
        $json = json_encode($payload);
        $signature = $this->get_signature($secret, $time_of_request, 'POST', '/v2/wallet/synchronous', $json);
       
        try {
            $client = new Client();
           
            $response = $client->request('POST', 'https://aml-api.elliptic.co/v2/wallet/synchronous', [
                'headers' => [
                    'x-access-key' => $api_key,
                    'x-access-sign' => $signature,
                    'x-access-timestamp' => $time_of_request,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'body' => $json
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
        $elliptic = BlockchainAnalyticsProvider::where('name', 'Elliptic')->first();
        $custodian = '';


        if (isset($response['cluster_entities'][0])) $custodian = $response['cluster_entities'][0]['name'];
        

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $elliptic->id,
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
        $elliptic = BlockchainAnalyticsProvider::where('name', 'Elliptic')->first();

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $elliptic->id,
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


    function get_signature(
        $secret,
        $time_of_request,
        $http_method,
        $http_path,
        $payload
      ) {
        // create a SHA256 HMAC using the supplied secret, decoded from base64
        $ctx = hash_init('sha256', HASH_HMAC, base64_decode($secret));
        // concatenate the request text to be signed
        $request_text = $time_of_request . $http_method . $http_path . $payload;
        // update the HMAC with the text to be signed
        hash_update($ctx, $request_text);
        // output the signature as a base64 encoded string
        return base64_encode(hex2bin(hash_final($ctx)));
      }
}

?>