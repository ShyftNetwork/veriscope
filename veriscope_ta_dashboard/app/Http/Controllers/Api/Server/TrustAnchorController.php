<?php

namespace App\Http\Controllers\Api\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\{VerifiedTrustAnchor, TrustAnchorExtraDataUnique};


class TrustAnchorController extends Controller
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
      * Show All Verified Trust Anchors
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function get_verified_trust_anchors(Request $request)
      {

          $trust_anchors = VerifiedTrustAnchor::orderBy('account_address')->get();

          return response()->json($trust_anchors);
      }

      /**
      * Get Trust Anchor Details
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function get_trust_anchor_details(Request $request, $address)
      {

          $trust_anchor_details = TrustAnchorExtraDataUnique::where('trust_anchor_address', $address)->get();

          return response()->json($trust_anchor_details);
      }

      /**
      * Verify Trust Anchor
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function verify_trust_anchor(Request $request, $address)
      {
          $isVerified = VerifiedTrustAnchor::where('account_address', $address)->exists();

          return response()->json(['address' => $address, 'verified' => $isVerified ]);

      }
      
}
