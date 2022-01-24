<?php

namespace App\Http\Controllers\Backoffice;

use App\{SmartContractAttestation, KycTemplate, TrustAnchorExtraDataUnique, VerifiedTrustAnchor};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $verified_trust_anchors = VerifiedTrustAnchor::all();
        $trust_anchors = TrustAnchorExtraDataUnique::all();
        $attestations = SmartContractAttestation::all();
        $kyc_templates = KycTemplate::all();


        return view('backoffice.dashboard', compact('verified_trust_anchors', 'trust_anchors', 'attestations', 'kyc_templates'));
    }

    public function arena_auth(){

      $signingSecret = config('shyft.webhook_client_secret');

      $payload = array(
       "iss" => config('app.url'),
       "aud" => config('app.url')
      );

      $jwt = JWT::encode($payload, $signingSecret, 'HS256');

      return redirect('/arena/?token='.$jwt);

    }
}
