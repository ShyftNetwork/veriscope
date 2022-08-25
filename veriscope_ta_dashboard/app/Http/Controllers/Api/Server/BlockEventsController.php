<?php

namespace App\Http\Controllers\Api\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\{LatestBlockEvents};
use App\Http\Requests\{EditLatestBlockEventNumberRequest};


class BlockEventsController extends Controller
{

      /**
     * Create a new controller instance.
     *
     * @return void
     */
      public function __construct()
      {

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

      public function editBlockEvent(EditLatestBlockEventNumberRequest $request, $id)
      {
          $input = $request->all();

          $event = LatestBlockEvents::where('id', $id)->firstOrFail();
        
          $event->block_number = (int) $input['block_number'];
          $event->save();
          return response()->json($event);
      }
}