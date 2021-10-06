<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSignature
{

    public function handle(Request $request, Closure $next)
    {

      $webhook_token = $request->header('X-WEBHOOK-TOKEN');

      $signingSecret = config('shyft.webhook_client_secret');

      if($webhook_token !==  $signingSecret){
        return abort(503);
      }

      return $next($request);

    }
}
