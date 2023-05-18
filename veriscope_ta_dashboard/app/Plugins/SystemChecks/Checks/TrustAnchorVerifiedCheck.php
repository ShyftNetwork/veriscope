<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use App\{ TrustAnchor, VerifiedTrustAnchor};
use Illuminate\Support\Facades\File;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Contract;


class TrustAnchorVerifiedCheck implements Check
{
    public function getId()
    {
        return 'trust-anchor-verified';
    }

    public function run()
    {

        $result = ['success' => false, 'message' => ''];

        $path = base_path('../veriscope_ta_node/.env');

        // Check if the .env file exists
        if (!File::exists($path)) {
            $result['message'] = 'Environment file not found';
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

        preg_match('/TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS=(.+)/', $envContents, $matches);
        // Get Web3 TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS
        $trustAnchorManagerContractAddress = str_replace('"', '',$matches[1]) ?? null;


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
        } elseif($trustAnchorManagerContractAddress == null){
            $result['message'] =  'TRUST_ANCHOR_MANAGER_CONTRACT_ADDRESS is not set';
            return $result;
        } else {

          try {
            $web3 = new Web3(new HttpProvider(new HttpRequestManager($httpRpc)));
            $contractABI = json_decode(File::get($contractsPath.'TrustAnchorManager.json'), true);
            $contractAddress = $trustAnchorManagerContractAddress; // set the contract address


            $accountAddresses = array_map('trim', explode(',', $trustAnchorAddrs));

            $trustAnchorAccountsCount = TrustAnchor::count();

            // Check if the number of trust anchor accounts in the environment is equal to the number of trust anchor accounts in the database
            if ($trustAnchorAccountsCount !== count($accountAddresses)) {
                $result['message'] = 'Number of Trust Anchor accounts in the environment file is not equal to the number of Trust Anchor accounts in the database';
                return $result;
            }

            $contract = new Contract($web3->provider, $contractABI['abi']); // create a new Contract instance with the Ethereum provider and the contract ABI
            $contract->at($contractAddress); // set the contract address

            // Check if all account addresses are verified in the blockchain
            $unverifiedAddressesBlockchain = [];
            foreach ($accountAddresses as $address) {
              // Call get function with parameter
              $result = $contract->call('isTrustAnchorVerified', $address , function ($err, $data)  use ($address, &$unverifiedAddressesBlockchain) {

                if ($err !== null) {
                  throw new \Exception("isTrustAnchorVerified error ".$err->getMessage());
                }

                if(!$data['result']){
                  $unverifiedAddressesBlockchain[] = $address;
                }

              });


            }


            // Check if all account addresses are verified in the database
            $unverifiedAddresses = [];
            foreach ($accountAddresses as $address) {
              $exists = VerifiedTrustAnchor::where('account_address', $address)->exists();
              if (!$exists) {
                  $unverifiedAddresses[] = $address;
              }
            }


            if (count($unverifiedAddressesBlockchain) > 0) {
              $result['message'] =  'The following Trust Anchor account addresses are not verified on Smart Contract: '.implode(', ', $unverifiedAddressesBlockchain);
              return $result;
            } elseif (count($unverifiedAddresses) > 0) {
              $result['message'] =  'The following Trust Anchor account addresses are not verified on this Veriscope Instance: '.implode(', ', $unverifiedAddresses);
              return $result;
            } elseif(count($unverifiedAddressesBlockchain) !== count($unverifiedAddresses)){
              $result['message'] = 'The number of Trust Anchor account addresses not verified on the smart contract and this veriscope instance are not equal. Smart Contract: '.implode(', ', $unverifiedAddressesBlockchain).' Veriscope Instance: '.implode(', ', $unverifiedAddresses);
              return $result;
            } else {
              $result['success'] =  true;
              $result['message'] =  'All Trust Anchor account addresses are verified';
              return $result;
            }


          } catch (\Exception $e) {
            $result['message'] = 'Nethermind HTTP RPC at '.$httpRpc.' is not running or due to error: '. $e->getMessage();
            return $result;

          }


        }

    }
}
