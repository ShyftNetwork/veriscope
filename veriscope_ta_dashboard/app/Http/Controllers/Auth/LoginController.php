<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Config;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your dashboard screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     *
     * @return mixed
     */
    protected function authenticated($request, $user)
    {

        if($user) {
          if($user->inGroup('admin') && config('backoffice.enabled')) {
            // return redirect()->intended('dashboard');
            return redirect()->intended('auth/attestations/manage-organization');
          } elseif($user->hasRole('member') && config('shyft.onboarding')) {

            // if user can transition to personal_information_updated then allow them to
            // edit their kyc data (basically if they are rejected)
            if( $user->last_state == 'new' || $user->last_state == 'pending' || $user->last_state == 'rejected' ){
              // return redirect()->intended('auth/welcome');
              return redirect()->intended('auth/attestations/manage-organization');
            }
            // reviewing or approved
            return redirect()->intended('dashboard');
          } elseif(config('shyft.onboarding')) {
            // return redirect()->intended('auth/welcome');
            return redirect()->intended('auth/attestations/manage-organization');
          } else {
            return redirect()->intended('/');
          }
        } else {
          return redirect()->intended('login');
        }
    }
}
