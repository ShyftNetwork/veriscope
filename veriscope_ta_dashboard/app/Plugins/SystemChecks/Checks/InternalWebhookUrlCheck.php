<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class InternalWebhookUrlCheck implements Check
{
    public function getId()
    {
        return 'internal-webhook-url';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        // Read the webhook URL from the .env file in veriscope_ta_node folder
        $path = base_path('../veriscope_ta_node/.env');
        if (!File::exists($path)) {
            $result['message'] = 'Environment file not found in veriscope_ta_node';
            return $result;
        }
        $envContents = File::get($path);
        preg_match('/WEBHOOK=(.+)/', $envContents, $matches);
        $taWebhookUrl = str_replace('"', '',$matches[1]) ?? null;


        if ($taWebhookUrl === null) {
            $result['message'] = 'Unable to read internal webhook url from .env file in veriscope_ta_node';
            return $result;
        }

        // Get the internal webhook client secret from .env file in veriscope_ta_dashboard folder
        $taDashboardSecret = config('shyft.webhook_client_secret');

        if ($taDashboardSecret === null) {
            $result['message'] = 'Unable to read internal webhook client secret from .env file in veriscope_ta_dashboard';
            return $result;
        }


        try {

          // Check if the webhook URL is reachable
          $response = Http::withHeaders([
            'X-WEBHOOK-TOKEN' => $taDashboardSecret
          ])->post($taWebhookUrl, [
            'obj' => [
                'message' => 'ta-ping'
            ]
          ]);

          if (!$response->ok()) {
              $result['message'] = 'Internal webhook url is unreachable';
              return $result;
          }

          $result['success'] = true;
          $result['message'] = 'Internal webhook url configuration OK';

          return $result;

        } catch (\Exception $e) {

          $result['message'] = 'Unable to connect to internal webhook url due to error: '. $e->getMessage();
          return $result;
        }
    }
}
