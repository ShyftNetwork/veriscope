<?php

namespace App\Http\Controllers\Backoffice;

use App\{SmartContractTransaction, SmartContractAttestation, TrustAnchor, TrustAnchorUser};
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;


class BlockExplorerController extends Controller
{

    /**
    * Show the application dashboard.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $trustanchors = TrustAnchor::orderBy('user_id')->paginate(config('backoffice.results_per_page'));

        return view('.backoffice.trustanchors.index', ['trustanchors' => $trustanchors]);
    }

    public function edit(Request $request, $id)
    {
        $trustanchor = TrustAnchor::findOrFail($id);

        $trustanchor_users = TrustAnchorUser::orderBy('prefname')->paginate(config('backoffice.results_per_page'));

        return view('.backoffice.trustanchors.edit', ['trustanchor' => $trustanchor, 'trustanchor_users' => $trustanchor_users]);
    }

    public function view(Request $request, $id)
    {
        $transaction = SmartContractTransaction::findOrFail($id);

        return view('.blockexplorer.view', ['transaction' => $transaction]);
    }

    public function ta_account(Request $request, $id)
    {
        Log::debug('BlockExplorerController ta_account');
        Log::debug($id);
        // $trustanchor = TrustAnchor::where('account_address', $id)->first();
        // Log::debug($trustanchor);
        return view('.blockexplorer.ta_account', ['trustanchor_address' => $id]);
    }
    
    public function user_account(Request $request, $id)
    {
        Log::debug('BlockExplorerController user_account');
        Log::debug($id);
        // $trustanchoruser = TrustAnchorUser::where('account_address', $id)->first();
        // Log::debug($trustanchoruser);
        return view('.blockexplorer.user_account', ['user_account_address' => $id]);
    }

    public function account_address(Request $request, $id)
    {
        Log::debug('BlockExplorerController account_address');
        Log::debug($id);
    
        return view('.blockexplorer.address', ['account_address' => $id]);
    }

    public function attestation_components(Request $request, $id)
    {
        Log::debug('BlockExplorerController attestation_components');
        Log::debug($id);

        if (is_numeric($id)) {
            $attestation = SmartContractAttestation::findOrFail($id);
            return view('.blockexplorer.attestation', ['attestation' => $attestation]);
        } else {
            $attestation = SmartContractAttestation::where('transaction_hash', $id)->firstOrFail();
            return view('.blockexplorer.attestation', ['attestation' => $attestation]);
        }
        
    }
    
    
}
