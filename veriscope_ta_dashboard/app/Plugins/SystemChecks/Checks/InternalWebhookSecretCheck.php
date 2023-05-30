<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\File;

class InternalWebhookSecretCheck implements Check
{
    public function getId()
    {
        return 'internal-webhook-secret';
    }

    public function run()
    {

        $result = ['success' => false, 'message' => ''];
        $path = base_path('../veriscope_ta_node/.env');

        // Check if the .env file exists
        if (!File::exists($path)) {
            $result['message'] = 'Environment file not found in veriscope_ta_node';
        }

        // Read the TRUST_ANCHOR_ACCOUNT variable from the .env file
        $envContents = File::get($path);
        preg_match('/WEBHOOK_CLIENT_SECRET=(.+)/', $envContents, $matches);

        // Get the internal webhook client secret from .env file in veriscope_ta_node folder
        $taNodeSecret = str_replace('"', '',$matches[1]) ?? null;

        // Get the internal webhook client secret from .env file in veriscope_ta_dashboard folder
        $taDashboardSecret = env('WEBHOOK_CLIENT_SECRET');

        if ($taNodeSecret === null) {
            $result['message'] = 'Unable to read internal webhook client secret from .env file in veriscope_ta_node';
            return $result;
        }

        if ($taDashboardSecret === null) {
            $result['message'] = 'Unable to read internal webhook client secret from .env file in veriscope_ta_dashboard';
            return $result;
        }

        if ($taNodeSecret !== $taDashboardSecret) {
            $result['message'] = 'Internal webhook client secret mismatch between veriscope_ta_node and veriscope_ta_dashboard';
            return $result;
        }

        $result['success'] =  true;
        $result['message'] = 'Internal webhook client secret between node and dashboard configuration OK';

        return $result;
    }
}
