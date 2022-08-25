<?php

namespace App\Http\Controllers\Api\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Http\Requests\SetAttestationRequest;
use App\{Country,BlockchainAnalyticsProvider, BlockchainAnalyticsSupportedNetworks, BlockchainAnalyticsAddress, Constant};
use kornrunner\Ethereum\Address;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
use App\Http\Requests\{GetBAReportRequest};

class BlockchainAnalyticsApiController extends Controller
{

      /**
     * Create a new controller instance.
     *
     * @return void
     */
      public function __construct()
      {

      }


      /**
      * Get all blockchain analytics providers
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */

      public function get_ba_providers(Request $request)
      {

          $data = BlockchainAnalyticsProvider::all();

          return response()->json($data);
      }

       /**
      * Get all blockchain analytics provider networks
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */

      public function get_ba_providers_available_networks(Request $request, $id)
      {

          $data = BlockchainAnalyticsSupportedNetworks::where('blockchain_analytics_provider_id', $id)->get();
          return response()->json($data);
      }

        /**
      * Get all blockchain analytics provider networks
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */

    public function get_ba_report(GetBAReportRequest $request)
    {
        $input = $request->all();

        $provider = BlockchainAnalyticsProvider::where('id', $input['providerId'])->first();
        if (!$provider) return response()->json("No such provider", 400);

        $network = BlockchainAnalyticsSupportedNetworks::where('blockchain_analytics_provider_id', $provider->id)->where('ticker', $input['network'])->firstOrFail();

        $response = [];
        $inertedObject = [];
        $custodian = '';
        $api_key = $provider['key'];
        if (!$api_key) return response()->json("Provider API key is not set", 400);

        if ($provider->id == 1) {
            $response = $this->crystalRequest($input['address'], $network['ticker'], $api_key);
            if (isset($response['data']['counterparty']['name'])) $custodian = $response['data']['counterparty']['name'];
            $inertedObject = $this->save_response_to_db($provider->id, $network->ticker, $input['address'], $custodian, $response, $response['statusCode'] );
        } else if ($provider->id == 2) {
            $response = $this->merkleScienceRequest($input['address'], $network['provider_network_id'], $api_key);
            if (isset($response['tags']['owner']['tag_name_verbose'])) $custodian = $response['tags']['owner']['tag_name_verbose'];
            $inertedObject = $this->save_response_to_db($provider->id, $network->ticker, $input['address'], $custodian, $response, $response['statusCode'] );
        } else if ($provider->id == 3) {
            $response = $this->coinfirmRequest($input['address'], $api_key);
            if (isset($response['profile_section']['owner']['name'])) $custodian = $response['profile_section']['owner']['name'];
            $inertedObject = $this->save_response_to_db($provider->id, $network->ticker, $input['address'], $custodian, $response, $response['statusCode'] );
        } else if ($provider->id == 4) {
            $response = $this->chainalysisRequest($input['address'],  $network, $api_key);
            if (isset($response[0]['cluster']['name'])) $custodian = $response[0]['cluster']['name'];
            $inertedObject = $this->save_response_to_db($provider->id, $network->ticker, $input['address'], $custodian, $response, $response['statusCode'] );
        }

        return response()->json($inertedObject);
    }


    function save_response_to_db($providerId, $blockchain, $address, $custodian, $response, $statusCode) {

        $insertData = [];
        $insertData['blockchain_analytics_provider_id'] = $providerId;
        $insertData['trust_anchor'] = 'unknown';
        $insertData['user_account'] =  'unknown';
        $insertData['blockchain'] = $blockchain;
        $insertData['crypto_address'] = $address;
        $insertData['custodian'] = $custodian;
        $insertData['response'] = json_encode($response);
        $insertData['response_status_code'] = $statusCode;

        $reportId = BlockchainAnalyticsAddress::insertGetId($insertData);

        $insertData['id'] = $reportId;
       
        return $insertData;

    }

    function save_error_to_db($response, $data) {
        $crystal = BlockchainAnalyticsProvider::where('name', 'Crystal')->first();

        $report = BlockchainAnalyticsAddress::insertGetId(
            array(
                   'blockchain_analytics_provider_id'   =>   $crystal->id,
                   'trust_anchor' => $data['ta_account']['account_address'],
                   'user_account' => $data['user_address'],
                   'blockchain' => strtolower($data['coin_blockchain']),
                   'crypto_address' => $data['coin_address'],
        
                   'response' => json_encode($response),
                   'response_status_code' => $response['meta']['error_code']
            )
       );

       $data['report_id'] = $report;
       $data['message'] = 'report-created';

       broadcast(new ContractsInstantiate($data));

    }

    function crystalRequest($address, $network, $api_key) {

        $payload = [];
        $payload['direction'] = "withdrawal";
        $payload['address'] = $address;
        $payload['name'] = 'unknown';
        $payload['currency'] = strtolower($network)  ;

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
            $jsonResponse['statusCode'] = 200;
            return $jsonResponse;

        } catch (ClientException $e) {
            $response = $e->getResponse();
            $jsonResponse = json_decode((string) $response->getBody(), true);
            $jsonResponse['statusCode'] = $jsonResponse['meta']['error_code'];
            return $jsonResponse;
        };
    }

    function merkleScienceRequest($address, $network, $api_key) {
        $payload = [];
        $payload['identifier'] = $address;
        $payload['currency'] = $network;

        try {
            $client = new Client();
            $response = $client->request('POST', 'https://demo.api.merklescience.com/api/v3/addresses/', [
                'headers' => [
                    'X-API-KEY' => $api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($payload)
            ]);
            
            $jsonResponse = json_decode($response->getBody(), true);
            $jsonResponse['statusCode'] = 200;
            return $jsonResponse;
        } catch (ClientException $e) {  
            $response = $e->getResponse();           
            $jsonResponse = json_decode((string) $response->getBody(), true);
            $jsonResponse['statusCode'] = $response->getStatusCode();
            return $jsonResponse;
        };
    }

    function coinfirmRequest($address, $api_key) {

        try {
            $client = new Client();
            $response = $client->request('GET', 'https://api.coinfirm.com/v3/reports/aml/full/' . $address . '?v=2', [
                'headers' => [
                    'authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);
            
            $jsonResponse = json_decode($response->getBody(), true);
            $jsonResponse['statusCode'] = 200;
            return $jsonResponse;
        } catch (ClientException $e) {  
            $response = $e->getResponse();           
            $jsonResponse = json_decode((string) $response->getBody(), true);
            $jsonResponse['statusCode'] = $response->getStatusCode();
            return $jsonResponse;
        };
    }

    function chainalysisRequest($address, $network, $api_key) {

        $payload = [];
        $payload['network'] = $network['name'];
        $payload['address'] = $address;
        $payload['asset'] = strtoupper($network['ticker'])  ;

        try {
            $client = new Client();
            $response = $client->request('POST', 'https://api.chainalysis.com/api/kyt/v1/users/veriscope/withdrawaladdresses', [
                'headers' => [
                    'Token' => $api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'body' => json_encode([$payload])
            ]);
            
            $jsonResponse = json_decode($response->getBody(), true);
            $jsonResponse['statusCode'] = 200;
            return $jsonResponse;
        } catch (ClientException $e) {  
            $response = $e->getResponse();           
            $jsonResponse = json_decode((string) $response->getBody(), true);
            $jsonResponse['statusCode'] = $response->getStatusCode();
            return $jsonResponse;
        };
    }


}
