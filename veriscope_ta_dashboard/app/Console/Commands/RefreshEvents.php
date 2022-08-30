<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class RefreshEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Events for Verified TAs, Discovery Layer and Attestations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->http_api_url = env('HTTP_API_URL');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::debug('RefreshEvents handle');

		$url = $this->http_api_url.'/refresh-all-verified-tas?user_id=1';
		$client = new Client();
		$res = $client->request('GET', $url);
		if($res->getStatusCode() == 200) {

			$response = json_decode($res->getBody());
			Log::debug('TrustAnchorController refresh_all_verified_trust_anchors');
			Log::debug($response);

		  
		} else {
		  	Log::error('TrustAnchorController refresh_all_verified_trust_anchors: ' . $res->getStatusCode());
		}

		$url = $this->http_api_url.'/refresh-all-discovery-layer-key-value-pairs?user_id=1';
		$client = new Client();
		$res = $client->request('GET', $url);
		if($res->getStatusCode() == 200) {

			$response = json_decode($res->getBody());
			Log::debug('TrustAnchorController refresh_all_discovery_layer_key_value_pairs');
			Log::debug($response);

		  
		} else {
		  	Log::error('TrustAnchorController refresh_all_discovery_layer_key_value_pairs: ' . $res->getStatusCode());
		}


		$url = $this->http_api_url.'/refresh-all-attestations?user_id=1';
		$client = new Client();
		$res = $client->request('GET', $url);
		if($res->getStatusCode() == 200) {

			$response = json_decode($res->getBody());
			Log::debug('TrustAnchorController refresh_all_attestations');
			Log::debug($response);

		  
		} else {
		  	Log::error('TrustAnchorController refresh_all_attestations: ' . $res->getStatusCode());
		}

        
    }
}
