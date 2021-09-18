<?php

namespace App\Http\Controllers\Backoffice;

use App\{User, KycTemplate};
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use App\Http\Controllers\Controller;
use Session;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class KycTemplatesController extends Controller
{

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $kyctemplates = KycTemplate::orderBy('id')->paginate(config('backoffice.results_per_page'));

        return view('.backoffice.kyctemplates.index', ['kyctemplates' => $kyctemplates]);
    }
    
    public function kyc_template_details(Request $request, $id)
    {
        Log::debug('BlockExplorerController attestation_components');
        Log::debug($id);

        $kyctemplate = KycTemplate::findOrFail($id);
        return view('.backoffice.kyctemplates.details', ['kyctemplate' => $kyctemplate]);
    }
}
