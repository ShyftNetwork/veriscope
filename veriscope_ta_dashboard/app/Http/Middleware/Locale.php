<?php namespace App\Http\Middleware;

use Closure;
use App;
use App\Constant;

class Locale {
   /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
    public function handle($request, Closure $next)
    {
        $locale = Constant::where('name', 'lang')->first();

        // check constant, if empty or undefined, default to app default (en)
        if(!empty($locale)) {
          App::setLocale($locale->value);
        }
        return $next($request);
    }

}
