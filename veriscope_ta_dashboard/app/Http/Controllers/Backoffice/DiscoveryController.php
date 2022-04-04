<?php

namespace App\Http\Controllers\Backoffice;

use App\{SmartContractTransaction, SmartContractAttestation, TrustAnchor, TrustAnchorUser};
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class DiscoveryController extends Controller
{

    public function validations(Request $request, $id)
    {
        Log::debug("App\Http\Controllers\DiscoveryController validations");
        Log::debug($id);

        return view('.discovery.validations', ['id' => $id]);
    }
    
    
}
