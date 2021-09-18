<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Requests\UserCreatePasswordRequest;

class OnboardController extends Controller
{

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
      //$this->middleware('guest')->except(['kyc', 'welcome']);
  }

    /**
     * Collect additional data and build job in the state machine for 4stop verification
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */


    public function passwordSet(Request $request, $token)
    {
      $user = User::where('remember_token', $token)->firstOrFail();

      return response()->view('.auth.passwords.set', compact('user', 'token'));
    }

    /**
     * Collect additional data and build job in the state machine for 4stop verification
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */


    public function passwordAssign(UserCreatePasswordRequest $request)
    {
      $input = $request->all();

      $password = bcrypt($input['password']);

      $user = User::where(
        [
          ['remember_token', $input['token']],
          ['email', $input['email']]
        ]
      )->firstOrFail();

      $user->update(['password' => $password, 'remember_token' => null, 'marketing_subscribe' => empty($input['marketing_subscribe']) ? 0 : 1, 'legal_agree' => 1]);

      // auto login use attempt instead of login
      if(Auth::attempt(['email' => $input['email'], 'password' => $input['password']])) {
        // Authentication passed...
        // return redirect()->route('welcome');
        return redirect()->intended('auth/attestations/manage-organization');
      }
      return redirect()->route('login');
    }

    /**
     * Update password from the settings page
     *
     * @return \Illuminate\Http\Response
     */


    public function passwordUpdate()
    {
      $input = request()->all();
      $this->validate(request(), [
        'password'        => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|confirmed',
      ]);

      $password = bcrypt($input['password']);

      if(Hash::check($input['old_password'], Auth::user()->password)) {
        Auth::user()->update(['password' => $password]);
        Session::flash('flash_message', 'Successfully updated password.');
        Session::flash('flash_type', 'success');
      } else {
        Session::flash('flash_message', 'Your old password does not match.');
        Session::flash('flash_type', 'error');
        return redirect()->back();
      }

      return redirect()->route('settings');
    }


    /**
     * Update email from the settings page
     *
     * @return \Illuminate\Http\Response
     */


    public function emailUpdate()
    {
      $input = request()->all();

      $email = $input['email'];
      $confirm_email = $input['confirm_email'];
      if(Auth::user()->update(['email' => $email])) {
        Session::flash('flash_message', 'Successfully updated email.');
        Session::flash('flash_type', 'success');
      } else {
        Session::flash('flash_message', 'There was an error updating your email.');
        Session::flash('flash_type', 'error');
      }


      return redirect()->route('settings');
    }

    /**
     * Collect additional data and build job in the state machine for 4stop verification
     *
     * @return \Illuminate\Http\Response
     */


    public function kyc()
    {
        if(Auth::user()->isMember()) {
            return redirect()->route('shyft-id');
        }
        return response()->view('.auth.kyc');
    }

}
