<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use App\{ TrustAnchor, TrustAnchorExtraDataUnique };
use Illuminate\Support\Facades\File;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Contract;

class TrustAnchorExtraDataUniqueCheck implements Check
{
    public function getId()
    {
        return 'trust-anchor-extra-data-unique';
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
        // Read the TRUST_ANCHOR_ACCOUNT variable from the .env file
        $envContents = File::get($path);
        preg_match('/TRUST_ANCHOR_ACCOUNT=(.+)/', $envContents, $matches);
        $trustAnchorAddrs = $matches[1] ?? null;
        preg_match('/HTTP=(.+)/', $envContents, $matches);
        // Get Web3 HTTP RPC endpointName
        $httpRpc = str_replace('"', '',$matches[1]) ?? null;

        preg_match('/CONTRACTS=(.+)/', $envContents, $matches);
        // Get Web3 CONTRACTS
        $contractsPath = str_replace('"', '',$matches[1]) ?? null;

        preg_match('/TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS=(.+)/', $envContents, $matches);
        $trustAnchorExtraDataUniqueContractAddress = str_replace('"', '', $matches[1]) ?? null;


        // Check if the TRUST_ANCHOR_ACCOUNT variable is set
        if ($trustAnchorAddrs  === null) {
            $result['message'] =  'TRUST_ANCHOR_ACCOUNT variable not found';
            return $result;
        } elseif ($httpRpc  === null) {
            $result['message'] =  'HTTP is not set';
            return $result;
        } elseif ($contractsPath  === null) {
            $result['message'] =  'CONTRACTS is not set';
            return $result;
        }
          // Check if the necessary variables are set
        elseif ($trustAnchorExtraDataUniqueContractAddress === null) {
            $result['message'] = 'TRUST_ANCHOR_EXTRA_DATA_UNIQUE_CONTRACT_ADDRESS is not set';
            return $result;
        } else {


          try {
              // Instantiate Web3, Contract, and set the contract address
              $web3 = new Web3(new HttpProvider(new HttpRequestManager($httpRpc)));

              $extraDataContractABI = json_decode(File::get($contractsPath . 'TrustAnchorExtraData_Unique.json'), true);
              $extraDataContractAddress = $trustAnchorExtraDataUniqueContractAddress;

              $accountAddresses = array_map('trim', explode(',', $trustAnchorAddrs));


              $extraDataContract = new Contract($web3->provider, $extraDataContractABI['abi']);
              $extraDataContract->at($extraDataContractAddress);

              $keyValuePairName = 'API_URL';


              $accountsWithoutApiUrlDatabase = [];
              $accountsWithoutApiUrlSmartContract = [];
              $accountsWithMismatchedApiUrl = [];


              foreach ($accountAddresses as $address) {
                  $extraData = TrustAnchorExtraDataUnique::where('trust_anchor_address', 'ILIKE', $address)
                      ->where('key_value_pair_name', $keyValuePairName)
                      ->first();

                  $extraDataContract->call('getTrustAnchorKeyValuePairValue', $address, $keyValuePairName, function ($err, $data) use ($extraData, $address, &$accountsWithoutApiUrlSmartContract, &$accountsWithMismatchedApiUrl, &$accountsWithoutApiUrlDatabase) {

                      if ($err !== null || !$data['doesExist']) {
                          $accountsWithoutApiUrlSmartContract[] = $address;
                      }

                      if ($extraData && $extraData->key_value_pair_value !== $data['keyValuePairValue']) {
                          $accountsWithMismatchedApiUrl[] = $address;
                      }

                      if ($extraData === null) {
                          $accountsWithoutApiUrlDatabase[] = $address;
                      }

                  });
              }

              if (count($accountsWithoutApiUrlSmartContract) > 0) {
                  $result['message'] = 'The following Trust Anchor account addresses do not have the API_URL key value pair in the Smart Contract: ' . implode(', ', $accountsWithoutApiUrlSmartContract);
                  return $result;
              }
              elseif (count($accountsWithoutApiUrlDatabase) > 0) {
                  $result['message'] = 'The following Trust Anchor account addresses do not have the API_URL key value pair in this Veriscope Instance: ' . implode(', ', $accountsWithoutApiUrlDatabase).' and Smart Contract: ' . implode(', ', $accountsWithoutApiUrlSmartContract);
                  return $result;
              }
              elseif(count($accountsWithoutApiUrlSmartContract) !== count($accountsWithoutApiUrlDatabase)){
                  $result['message']   = 'The number of Trust Anchor account addresses do not have the API_URL key value pair in this Veriscope Instance and on the Smart Contract are not equal. Smart Contract: '.implode(', ', $accountsWithoutApiUrlSmartContract).' Veriscope Instance: '.implode(', ', $accountsWithoutApiUrlDatabase);
                  return $result;
              }
              elseif (count($accountsWithMismatchedApiUrl) > 0) {
                  $result['message'] = 'The following Trust Anchor account addresses have mismatched API_URL values between the database and the Smart Contract: ' . implode(', ', $accountsWithMismatchedApiUrl);
                  return $result;
              }

              $result['success'] = true;
              $result['message'] = 'All Trust Anchor account addresses have the API_URL key value pair and match with the Smart Contract and this Veriscope Instance';
              return $result;




          } catch (\Exception $e) {
              $result['message'] = 'Nethermind HTTP RPC at ' . $httpRpc. ' is not running or due to error: ' . $e->getMessage();
              return $result;
          }


        }
    }
}
