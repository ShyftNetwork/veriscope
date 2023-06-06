<?php

namespace App\Http\Controllers\Api\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\{LatestBlockEvents};
use App\Http\Requests\{EditLatestBlockEventNumberRequest, RefreshEventSyncRequest};
use GuzzleHttp\Client;


class BlockEventsController extends Controller
{

      /**
     * Create a new controller instance.
     *
     * @return void
     */
      public function __construct()
      {
        $this->http_api_url = env('HTTP_API_URL');
      }


      /**
      * Get all blockchain analytics providers
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */

      public function getBlockEvents(Request $request)
      {
          $data = LatestBlockEvents::all();
          return response()->json($data);
      }

      public function getRefreshEventSync(RefreshEventSyncRequest $request)
      {

        $startBlock = $request->input('startBlock', 1);
        $url = $this->http_api_url.'/refresh_event_sync?startBlock='.$startBlock;
        $client = new Client();
        $res = $client->request('GET', $url);
        if($res->getStatusCode() == 200) {
          response()->json(['status' => 'success']);

        } else {
          response()->json(['status' => 'fail']);
        }

      }

      public function editBlockEvent(EditLatestBlockEventNumberRequest $request, $id)
      {
          $input = $request->all();

          $event = LatestBlockEvents::where('id', $id)->firstOrFail();

          $event->block_number = (int) $input['block_number'];
          $event->save();
          return response()->json($event);
      }
}
