<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;

class NethermindSyncCheck implements Check
{
    public function getId()
    {
        return 'nethermind-sync';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];
        $path = base_path('../veriscope_ta_node/.env');

        try {
        // Check if the .env file exists
        if (!File::exists($path)) {
            $result['message'] = 'Environment file not found';
        }
        // Read the TRUST_ANCHOR_ACCOUNT variable from the .env file
        $envContents = File::get($path);
        preg_match('/HTTP=(.+)/', $envContents, $matches);
        // Get Web3 HTTP RPC endpointName
        $httpRpc = str_replace('"', '',$matches[1]) ?? null;
        // Check if the HTTP variable is set
        if ($httpRpc  === null) {
            $result['message'] =  'HTTP is not set';
            return $result;
        }



        $client = new Client(['base_uri' => $httpRpc]);

        // Get the current block number from Nethermind
        $response = $client->post('', ['json' => [
            'jsonrpc' => '2.0',
            'method' => 'eth_blockNumber',
            'params' => [],
            'id' => 1,
        ]]);
        $body = json_decode($response->getBody(), true);
        $currentBlockNumber = hexdec($body['result']);

        // Get the highest block number known to the Ethereum network
        $response = $client->post('', ['json' => [
            'jsonrpc' => '2.0',
            'method' => 'eth_blockNumber',
            'params' => [],
            'id' => 2,
        ]]);
        $body = json_decode($response->getBody(), true);
        $highestBlockNumber = hexdec($body['result']);

        // Check if Nethermind is fully synced
        if ($currentBlockNumber < ($highestBlockNumber - 2)) {
            $result['message'] = 'Nethermind is not fully synced. The current block is ' . $currentBlockNumber . ' and the highest block is ' . $highestBlockNumber;
            return $result;
        }

        if ($currentBlockNumber >= ($highestBlockNumber - 2)) {
            $result['success'] = true;
            $result['message'] = 'Nethermind is fully synced';
            return $result;
        }



      } catch (\Exception $e) {
        $result['message'] = 'Nethermind is not running due to error: '. $e->getMessage();
        return $result;
       }


    }
}
