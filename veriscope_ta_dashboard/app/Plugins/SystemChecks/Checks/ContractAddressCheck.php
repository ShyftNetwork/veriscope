<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\File;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Utils;

class ContractAddressCheck implements Check
{
    public function getId()
    {
        return 'contract-addresses';
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
        // Read the contract addresses from the .env file
        $envContents = File::get($path);
        preg_match('/TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS=(.+)/', $envContents, $matches);
        $trustAnchorManagerContractAddress = str_replace('"', '', $matches[1] ?? null);
        preg_match('/TRUST_ANCHOR_STORAGE_CONTRACT_ADDRESS=(.+)/', $envContents, $matches);
        $trustAnchorStorageContractAddress = str_replace('"', '', $matches[1] ?? null);
        preg_match('/TRUST_ANCHOR_EXTRA_DATA_GENERIC_CONTRACT_ADDRESS=(.+)/', $envContents, $matches);
        $trustAnchorExtraDataGenericContractAddress = str_replace('"', '', $matches[1] ?? null);
        preg_match('/TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS=(.+)/', $envContents, $matches);
        $trustAnchorExtraDataUniqueContractAddress = str_replace('"', '', $matches[1] ?? null);
        preg_match('/HTTP=(.+)/', $envContents, $matches);
        // Get Web3 HTTP RPC endpointName
        $httpRpc = str_replace('"', '',$matches[1]) ?? null;

        if ($httpRpc  === null) {
            $result['message'] =  'HTTP is not set';
            return $result;
        } // Check if the contract addresses are set
        elseif (!$trustAnchorManagerContractAddress || !$trustAnchorStorageContractAddress || !$trustAnchorExtraDataGenericContractAddress || !$trustAnchorExtraDataUniqueContractAddress || !$httpRpc) {
            $result['message'] = 'All contract addresses must be set in the environment file';
            return $result;
        }



        try {
            $web3 = new Web3(new HttpProvider(new HttpRequestManager($httpRpc)));

            $invalidContracts = [];

            $contracts = [
                'TrustAnchorManager' => $trustAnchorManagerContractAddress,
                'TrustAnchorStorage' => $trustAnchorStorageContractAddress,
                'TrustAnchorExtraDataGeneric' => $trustAnchorExtraDataGenericContractAddress,
                'TrustAnchorExtraDataUnique' => $trustAnchorExtraDataUniqueContractAddress,
            ];

            foreach ($contracts as $contractName => $contractAddress) {
                $web3->eth->getStorageAt($contractAddress, 0 , function ($err, $storage) use($contractName, $contractAddress, &$invalidContracts)  {
                    if ($err !== null) {
                        throw new \Exception("getStorageAt error " . $err->getMessage());
                    }

                    if ($storage == '0x') {
                        $invalidContracts[$contractName] = $contractAddress;
                    }
                });
            }

            if (count($invalidContracts) > 0) {
                $result['message'] = 'Invalid contract address(es) detected: ' . implode(', ', array_map(function ($k, $v) { return "$k: $v"; }, array_keys($invalidContracts), $invalidContracts));
                return $result;
            }

            $result['success'] = true;
            $result['message'] = 'All contract addresses are valid';
            return $result;

        } catch (\Exception $e) {
            $result['message'] = 'Nethermind HTTP RPC at ' . $httpRpc . ' is not running or due to error: ' . $e->getMessage();
            return $result;
        }
    }
}
