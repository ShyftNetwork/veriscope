<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;

class NethermindCheck implements Check
{
    public function getId()
    {
        return 'nethermind';
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


        $client = new Client();
        $response = $client->get($httpRpc);
        $status = $response->getStatusCode();

        if ($status === 200) {
            $result['success'] =  true;
            $result['message'] = 'Nethermind is running';
            return $result;
        } else {
            $result['message'] = 'Nethermind is not running';
            return $result;
        }

      } catch (\Exception $e) {
        $result['message'] = 'Nethermind is not running due to error: '. $e->getMessage();
        return $result;
      }

    }
}
