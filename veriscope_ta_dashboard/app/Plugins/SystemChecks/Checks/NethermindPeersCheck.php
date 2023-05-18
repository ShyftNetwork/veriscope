<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
class NethermindPeersCheck implements Check
{
    public function getId()
    {
        return 'nethermind-peer';
    }


    public function run()
    {
        $result = ['success' => false, 'message' => ''];
        $path = base_path('../veriscope_ta_node/.env');

        try {
            // Check if the .env file exists
            if (!File::exists($path)) {
                $result['message'] = 'Environment file not found';
                return $result;
            }

            // Read the HTTP variable from the .env file
            $envContents = File::get($path);
            preg_match('/HTTP=(.+)/', $envContents, $matches);
            // Get Web3 HTTP RPC endpointName
            $httpRpc = str_replace('"', '', $matches[1]) ?? null;

            // Check if the HTTP variable is set
            if ($httpRpc === null) {
                $result['message'] = 'HTTP is not set';
                return $result;
            }

            // JSON-RPC payload for net_peerCount
            $payload = [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'net_peerCount',
                'params' => [],
            ];

            $response = Http::post($httpRpc, $payload);

            if ($response->failed()) {
                $result['message'] = 'Nethermind is not responding to RPC requests';
                return $result;
            }

            $result = $response->json();
            if (isset($result['error'])) {
                $result['message'] = 'RPC request error: ' . $result['error']['message'];
                return $result;
            }

            $peerCount = isset($result['result']) ? hexdec($result['result']) : 0;

            if ($peerCount < 1) {
                $result['message'] = 'Nethermind is not connected to any peers';
                return $result;
            }

            // Peer count should be always more than 3
            if ($peerCount > 3) {
                $result['success'] = true;
                $result['message'] = 'Nethermind is connected to ' . $peerCount . ' peers';
                return $result;
            }

        } catch (\Exception $e) {
            $result['message'] = 'Nethermind is not running due to error: ' . $e->getMessage();
            return $result;
        }
    }



}
