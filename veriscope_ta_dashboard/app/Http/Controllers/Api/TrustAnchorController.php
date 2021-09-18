<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\{User, TrustAnchor, TrustAnchorUser, TrustAnchorUserAttestation, KycData, CryptoWalletType, CryptoWalletAddress};

use Illuminate\Support\Facades\Log;


class TrustAnchorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        Log::debug('TrustAnchorController index');
        
        // get all params
        $input = $request->all();

        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection

        $trustAnchors = new TrustAnchor;
        $paginatedTrustAnchors = new TrustAnchor;

        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $trustAnchors = $trustAnchors->search($input['searchTerm']);
            $paginatedTrustAnchors = $paginatedTrustAnchors->search($input['searchTerm']);
        }

        // sort logic
        if(!empty($input['sort'])) {
          $sort = json_decode($input['sort']);
          if($sort->field != '' && $sort->type != '') {
            $paginatedTrustAnchors = $paginatedTrustAnchors->orderBy($sort->field, $sort->type);
          }
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedTrustAnchors = $paginatedTrustAnchors->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedTrustAnchors = $paginatedTrustAnchors->get();
        }

        // add custom column for editing and verifying
        foreach($paginatedTrustAnchors as $trustAnchor) {
          // override last_state with camel case state
          // $user['last_state'] = $user->niceStateIs();
          $user = User::findOrFail($trustAnchor->user_id);
          $trustAnchor['user'] = $user->first_name .' '. $user->last_name;
          $trustAnchor['user_email'] = $user->email;
          $trustAnchor['trustanchor_users'] = TrustAnchorUser::where('trust_anchor_id', $trustAnchor->id)->count();
          $trustAnchor['action'] = '<a href="/backoffice/trustanchors/'.$trustAnchor->id.'/edit" class="btn btn--alt btn--sm">edit</a> ';
          $trustAnchor['reload_account'] = '<a href="/backoffice/trustanchors/'.$trustAnchor->id.'/reload-trust-anchor-account" class="btn btn--alt btn--sm">Reload Account</a> ';
          $trustAnchor['request_tokens'] = '<a href="/backoffice/trustanchors/'.$trustAnchor->id.'/request-tokens" class="btn btn--alt btn--sm">Request Tokens</a> ';
          $trustAnchor['unlock_account'] = '<a href="/backoffice/trustanchors/'.$trustAnchor->id.'/unlock-account" class="btn btn--alt btn--sm">Unlock Account</a> ';
        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $trustAnchors->count(),
          'rows' => $paginatedTrustAnchors,
        ];
    }

    public function trustanchor_users(Request $request)
    {

        Log::debug('TrustAnchorController trustanchor_users');
        
        // get all params
        $input = $request->all();

        Log::debug($input);
        $filter = explode('|', $input['filter']);
        $trust_anchor_id = $filter[1];
        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection

        $trustAnchorUsers = TrustAnchorUser::where('trust_anchor_id', $trust_anchor_id);
        $paginatedTrustAnchorUsers = TrustAnchorUser::where('trust_anchor_id', $trust_anchor_id);


        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $trustAnchorUsers->search($input['searchTerm']);
            $paginatedTrustAnchorUsers->search($input['searchTerm']);
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedTrustAnchorUsers = $paginatedTrustAnchorUsers->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedTrustAnchorUsers = $paginatedTrustAnchorUsers->get();
        }

        // add custom column for editing and verifying
        foreach($paginatedTrustAnchorUsers as $trustAnchorUser) {
          // override last_state with camel case state

          $trustAnchorUser['attestations'] = TrustAnchorUserAttestation::where('trust_anchor_user_id', $trustAnchorUser->id)->count();

          $trustAnchorUser['action'] = '<a href="/backoffice/trustanchor-user/'.$trustAnchorUser->id.'/attestations" class="btn btn--alt btn--sm">Attestations</a> ';

        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $trustAnchorUsers->count(),
          'rows' => $paginatedTrustAnchorUsers,
        ];
    }

    public function trust_anchor_user_attestations(Request $request)
    {

        Log::debug('TrustAnchorController trust_anchor_user_attestations');
        
        // get all params
        $input = $request->all();

        Log::debug($input);
        $filter = explode('|', $input['filter']);
        $trust_anchor_user_id = $filter[1];
        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection

        $trustAnchorUserAttestations = TrustAnchorUserAttestation::where('trust_anchor_user_id', $trust_anchor_user_id);
        $paginatedTrustAnchorUserAttestations = TrustAnchorUserAttestation::where('trust_anchor_user_id', $trust_anchor_user_id);


        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $trustAnchorUserAttestations->search($input['searchTerm']);
            $paginatedTrustAnchorUserAttestations->search($input['searchTerm']);
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedTrustAnchorUserAttestations = $paginatedTrustAnchorUserAttestations->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedTrustAnchorUserAttestations = $paginatedTrustAnchorUserAttestations->get();
        }

        // add custom column for editing and verifying
        foreach($paginatedTrustAnchorUserAttestations as $attestation) {
          // override last_state with camel case state
          $trustAnchorUser = TrustAnchorUser::findOrFail($attestation->trust_anchor_user_id);
          $trustAnchor = TrustAnchor::findOrFail($attestation->trust_anchor_id);
          $attestation['user_account_address'] = $trustAnchorUser->account_address;

          $attestation['ta_account_address'] = $trustAnchor->account_address;

        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $trustAnchorUserAttestations->count(),
          'rows' => $paginatedTrustAnchorUserAttestations,
        ];
    }

    public function wallet_types()
    {
        //
        $wallet_types = CryptoWalletType::get(['id', 'wallet_type']);
        return response()->json($wallet_types);
    }

    public function wallet_addresses(Request $request, $id)
    {
        $input = $request->all();
        Log::debug('TrustAnchorController wallet_addresses');
        Log::debug($id);

        $wallet_type = $input['wallet_type'];
        $trust_anchor_user_id = $input['trust_anchor_user_id'];

        $transactions = CryptoWalletAddress::where('crypto_wallet_type_id', $wallet_type)->where('trust_anchor_user_id', '!=', $trust_anchor_user_id)->get();
  
        return response()->json($transactions);
    }

}
