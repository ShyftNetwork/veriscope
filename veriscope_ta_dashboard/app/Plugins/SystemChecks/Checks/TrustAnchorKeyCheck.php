<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\File;
use App\Support\EthereumToolsUtils;

class TrustAnchorKeyCheck implements Check
{
    public function getId()
    {
        return 'trust-anchor-key';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        $path = base_path('../veriscope_ta_node/.env');

        // Check if the .env file exists
        if (!File::exists($path)) {
            $result['message'] = 'Environment file not found';
            return $result;
        }

        // Read the TRUST_ANCHOR_PK and TRUST_ANCHOR_ACCOUNT variables from the .env file
        $envContents = File::get($path);
        preg_match('/TRUST_ANCHOR_PK=(.+)/', $envContents, $matches);
        $privateKeys = $matches[1] ?? null;
        preg_match('/TRUST_ANCHOR_ACCOUNT=(.+)/', $envContents, $matches);
        $trustAnchorAddresses = $matches[1] ?? null;

        // Check if the TRUST_ANCHOR_PK and TRUST_ANCHOR_ACCOUNT variables are set
        if ($privateKeys === null || $trustAnchorAddresses === null) {
            $result['message'] = 'TRUST_ANCHOR_PK and/or TRUST_ANCHOR_ACCOUNT variable(s) not found';
            return $result;
        }

        // Split the private keys and trust anchor addresses into arrays
        $privateKeys = array_map('trim', explode(',', $privateKeys));
        $trustAnchorAddresses = array_map('trim', explode(',', $trustAnchorAddresses));

        // Check if the number of private keys matches the number of trust anchor addresses
        if (count($privateKeys) !== count($trustAnchorAddresses)) {
            $result['message'] = 'Number of private keys does not match number of trust anchor addresses';
            return $result;
        }

        try {
          // Verify if each private key matches with the corresponding trust anchor address
          for ($i = 0; $i < count($privateKeys); $i++) {
              $address = EthereumToolsUtils::privateKeyToAddress($privateKeys[$i]);
              if (strtolower($address) !== strtolower($trustAnchorAddresses[$i])) {
                  $truncatedKey = substr($privateKeys[$i], 0, 6) . '...';
                  $result['message'] = "Private key and trust anchor address do not match. Private key: {$truncatedKey}, Trust Anchor Address: {$trustAnchorAddresses[$i]} should be {$address}";
                  return $result;
              }
          }

            $result['success'] = true;
            $result['message'] = 'Private keys match with corresponding trust anchor addresses';
            return $result;

        } catch (\Exception $e) {
            $result['message'] = 'Error occurred while checking private keys: ' . $e->getMessage();
            return $result;
        }
    }
}
