<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Config;

class EthSyncCheck implements Check
{
    public function getId()
    {
        return 'eth-sync';
    }

    public function run()
    {

        Config::set('database.redis.options.prefix', '');

        $result = ['success' => false, 'message' => ''];
        $path = base_path('../veriscope_ta_node/.env');

        try {
            // Check if the .env file exists
            if (!File::exists($path)) {
                $result['message'] = 'Environment file not found';
                return $result;
            }

            // Read the TRUST_ANCHOR_ACCOUNT variable from the .env file
            $envContents = File::get($path);
            preg_match('/HTTP=(.+)/', $envContents, $matches);

            // Get Web3 HTTP RPC endpointName
            $httpRpc = str_replace('"', '', $matches[1]) ?? null;

            // Check if the HTTP variable is set
            if ($httpRpc === null) {
                $result['message'] = 'HTTP is not set';
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

                // Check Redis key for startBlock value
                $redisValue = Redis::get('keyv:startBlock');
                $decodedValue = json_decode($redisValue, true);

                if ($decodedValue && isset($decodedValue['value'])) {
                    $startBlock = $decodedValue['value'];

                    if ($startBlock >= ($highestBlockNumber - 2)) {
                      $result['success'] = true;
                      $result['message'] = 'Eth-sync is fully synced';
                      return $result;
                    }

                    $result['message'] = 'Eth-sync is not fully synced. The current block is ' . $startBlock . ' and the highest block is ' . $highestBlockNumber;
                    return $result;

                }

                $result['message'] = $decodedValue;

                return $result;
            }
        } catch (\Exception $e) {
            $result['message'] = 'Nethermind is not running due to an error: ' . $e->getMessage();
            return $result;
        }
    }
}
