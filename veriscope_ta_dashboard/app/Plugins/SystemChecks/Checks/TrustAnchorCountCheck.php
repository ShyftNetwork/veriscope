<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use App\TrustAnchor;
use Illuminate\Support\Facades\File;

class TrustAnchorCountCheck implements Check
{
    public function getId()
    {
        return 'trust-anchor-count';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        // Get trust anchors from the database
        $databaseTrustAnchors = TrustAnchor::pluck('account_address')->toArray();

        // Get trust anchors from the .env file
        $envPath = base_path('../veriscope_ta_node/.env');
        if (!File::exists($envPath)) {
            $result['message'] = '.env file not found';
            return $result;
        }

        $envContents = File::get($envPath);
        preg_match('/TRUST_ANCHOR_ACCOUNT=(.+)/', $envContents, $matches);
        $envTrustAnchors = $matches[1] ?? null;
        if ($envTrustAnchors === null) {
            $result['message'] = 'TRUST_ANCHOR_ACCOUNT variable not found in .env file';
            return $result;
        }

        $envTrustAnchors = array_map('trim', explode(',', $envTrustAnchors));

        // Compare the trust anchors from the .env file and the database
        $missingInDatabase = array_diff($envTrustAnchors, $databaseTrustAnchors);

        if (count($missingInDatabase) === 0) {
            $result['success'] = true;
            $result['message'] = 'All trust anchors in the .env file are in the database.';
            return $result;
        }

        if (count($missingInDatabase) > 0) {
            $result['message'] = 'Trust anchors in mismatch in .env file: '.implode(', ', $envTrustAnchors).' and database: '.implode(', ', $databaseTrustAnchors);
            return $result;
        }

    }
}
