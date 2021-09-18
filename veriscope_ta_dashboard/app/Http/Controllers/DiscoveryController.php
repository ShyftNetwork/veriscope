<?php

namespace App\Http\Controllers;

use App\{TrustAnchorExtraData, TrustAnchorExtraDataUnique};
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class DiscoveryController extends Controller
{

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $extra_data = TrustAnchorExtraData::orderBy('endpoint_name')->paginate(config('backoffice.results_per_page'));

        return view('.discovery.index', ['extra_data' => $extra_data]);
    }
  
    public function unique(Request $request)
    {
        $extra_data = TrustAnchorExtraDataUnique::orderBy('trust_anchor_address')->paginate(config('backoffice.results_per_page'));

        return view('.discovery.index', ['extra_data_unique' => $extra_data]);
    }
    
}
