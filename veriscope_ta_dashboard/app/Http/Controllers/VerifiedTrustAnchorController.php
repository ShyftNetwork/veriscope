<?php

namespace App\Http\Controllers;

use App\{VerifiedTrustAnchor};
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class VerifiedTrustAnchorController extends Controller
{

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $trust_anchors = VerifiedTrustAnchor::orderBy('account_address')->paginate(config('backoffice.results_per_page'));

        return view('.verifiedtrustanchor.index', ['trust_anchors' => $trust_anchors]);
    }
    
}
