<?php

namespace App\Http\Controllers\Api\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Http\Requests\{SetV3AttestationRequest};
use App\{Country};
use kornrunner\Ethereum\Address;
use App\Support\EthereumToolsUtils;

class ContractsController extends Controller
{

      /**
     * Create a new controller instance.
     *
     * @return void
     */
      public function __construct()
      {
          $this->helper_url = env('HTTP_API_URL');
      }

      /**
      * Create action for new entity
      *
      * @param  App\Http\Requests\SetV3AttestationRequest $request
      * @return \Illuminate\Http\Response
      */

      public function ta_set_v3_attestation(SetV3AttestationRequest $request)
      {

          $input = $request->all();

          $input['user_id'] = auth()->user()->id;

          $queryString = http_build_query($input);

          $url = $this->helper_url.'/ta-set-v3-attestation?'.$queryString;

          Log::debug($url);

          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {
            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_set_v3_attestation');
            Log::debug($response);
          } else {
            Log::error('ContractsController ta_set_v3_attestation: ' . $res->getStatusCode());
          }

          return response()->json([]);
      }


      /**
      * Create Shyft User
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function create_shyft_user(Request $request)
      {

          $data = new Address();
          $data = [
            'address' => strtolower($data->get()),
            'privateKey' => $data->getPrivateKey(),
            'publicKey'  => EthereumToolsUtils::privateKeyToPublicKey($data->getPrivateKey()),
          ];

          $message = "VERISCOPE_USER";
          $signed_message = EthereumToolsUtils::personalSign( $data['privateKey'], $message, 1);

          return response()->json([
            'account_address' => "0x".$data['address'],
            'private_key' => $data['privateKey'],
            'public_key' => $data['publicKey'],
            'message' => $message,
            'signature_hash' => $signed_message["hash"],
            'signature' => [
              'r' => $signed_message["r"],
              's' => $signed_message["s"],
              'v' => $signed_message["v"]
            ]
          ]);
      }

      /**
      * Get all jurisdictions
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function get_jurisdictions(Request $request)
      {

          $data = Country::select('id','sortname', 'name','created_at','updated_at')->get();

          return response()->json($data);
      }

}
