<?php

namespace App\Http\Controllers\Api\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Http\Requests\SetAttestationRequest;
use App\{Country};
use kornrunner\Ethereum\Address;


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
      * @param  App\Http\Requests\SetAttestationRequest $request
      * @return \Illuminate\Http\Response
      */
      public function ta_set_attestation(SetAttestationRequest $request)
      {

          $input = $request->all();

          $input['user_id'] = auth()->user()->id;

          $queryString = http_build_query($input);

          $url = $this->helper_url.'/ta-set-attestation?'.$queryString;

          Log::debug($url);

          $client = new Client();
          $res = $client->request('GET', $url);
          if($res->getStatusCode() == 200) {
            $response = json_decode($res->getBody());
            Log::debug('ContractsController ta_set_attestation');
            Log::debug($response);
          } else {
            Log::error('ContractsController ta_set_attestation: ' . $res->getStatusCode());
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

          return response()->json([
            'address' => "0x".$data->get(),
            'privateKey' => $data->getPrivateKey(),
            'publicKey' => $data->getPublicKey()
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
