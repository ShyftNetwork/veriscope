<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if(Auth::check()) {
            if(Auth::user()->status !== 'active') {
              return redirect('/errors/'.Auth::user()->status);
            }
            if (Auth::user()->inGroup('admin') && config('backoffice.enabled')) {
              return redirect('/backoffice');
            } elseif (Auth::user()->hasRole('member') && config('shyft.onboarding')) {
              return redirect('/dashboard');
            }
        } else {
          if (Auth::guard($guard)->check()) {
              return redirect('/dashboard');
          }
        }
        return $next($request);
    }
}
