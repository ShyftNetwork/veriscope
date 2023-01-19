<?php

namespace App\Http\Controllers\Backoffice;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Session;
use Laravel\Passport\TokenRepository;

class TokenController extends Controller
{


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->tokenRepository = app(TokenRepository::class);

        $this->defaultScopes = [
          'set_attestation',
          'create_shyft_user',
          'get_jurisdictions',
          'get_verified_trust_anchors',
          'get_trust_anchor_details',
          'verify_trust_anchor'
        ];
    }


    public function index(Request $request){

        return view('.backoffice.tokens.index');

    }

    public function create(){

        $token = auth()->user()->createToken('Token Name', $this->defaultScopes);

        Session::flash('flash_message', 'Successfully created token');
        Session::flash('flash_type', 'success');

        return redirect('dashboard/tokens');

    }


    public function revoke($id){

        $token = $this->tokenRepository->findForUser($id, auth()->user()->id);
        if($token){
          $this->tokenRepository->revokeAccessToken($id);
          Session::flash('flash_message', 'Successfully revoked token');
          Session::flash('flash_type', 'success');
        } else {
          Session::flash('flash_message', 'Token not found');
          Session::flash('flash_type', 'error');
        }

        return redirect('dashboard/tokens');
    }

}
