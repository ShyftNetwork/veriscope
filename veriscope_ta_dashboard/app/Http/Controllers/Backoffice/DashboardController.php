<?php

namespace App\Http\Controllers\Backoffice;

use App\{SmartContractAttestation, KycTemplate, TrustAnchorExtraDataUnique, VerifiedTrustAnchor};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;

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
        $trust_anchors = TrustAnchorExtraDataUnique::all()->groupBy('trust_anchor_address');
        $attestations = SmartContractAttestation::all();
        $kyc_templates = KycTemplate::all();


        return view('backoffice.dashboard', compact('verified_trust_anchors', 'trust_anchors', 'attestations', 'kyc_templates'));
    }
}
