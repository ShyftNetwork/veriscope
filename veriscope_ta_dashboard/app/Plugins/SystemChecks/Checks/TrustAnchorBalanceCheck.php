<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use App\{ TrustAnchor };
use Illuminate\Support\Facades\File;
use Web3\Web3;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;


class TrustAnchorBalanceCheck implements Check
{
    public function getId()
    {
        return 'trust-anchor-balance';
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


        // Check if the TRUST_ANCHOR_ACCOUNT variable is set
        if ($trustAnchorAddrs  === null) {
            $result['message'] =  'TRUST_ANCHOR_ACCOUNT is not set';
            return $result;
        }

        // Check if the HTTP variable is set
        if ($httpRpc  === null) {
            $result['message'] =  'HTTP is not set';
            return $result;
        }


        $accountAddresses = array_map('trim',explode(',', $trustAnchorAddrs));

        $trustAnchorAccountsCount = TrustAnchor::count();

        // Check if the number of trust anchor accounts in the environment is equal to the number of trust anchor accounts in the database
        if ($trustAnchorAccountsCount !== count($accountAddresses)) {
            $result['message'] = 'Number of Trust Anchor accounts in the environment is not equal to the number of Trust Anchor accounts in the database';
            return $result;
        }

        // Check if all account addresses that have no balances
        $noBalanceAddresses = [];

        try {
          $web3 = new Web3(new HttpProvider(new HttpRequestManager($httpRpc)));

          foreach ($accountAddresses as $address) {


              $web3->eth->getBalance($address, function ($err, $balance) use($address, &$noBalanceAddresses)   {
                if ($err !== null) {
                  $result['message'] =  'Cannot fetch balance for '.$address.' set error: ' . $err->getMessage();
                  return $result;
  	            }
                $balanceInWei = (float) $balance->toString();
                if (!$balanceInWei) {
                    $noBalanceAddresses[] = $address;
                }

              });


          }

          if (count($noBalanceAddresses) > 0) {
              $result['message'] =  'The following Trust Anchor account addresses are have no balance: '.implode(', ', $noBalanceAddresses);
              return $result;
          } else {
              $result['success'] =  true;
              $result['message'] =  'All Trust Anchor account addresses have balances';
              return $result;
          }

        } catch (\Exception $e) {
              $result['message'] = 'Nethermind HTTP RPC at '.$httpRpc.' is not running or due to error: '. $e->getMessage();
              return $result;

        }


    }
}
