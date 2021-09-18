<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\{TrustAnchorExtraData, TrustAnchorExtraDataUnique};

class DiscoveryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }


    public function index(Request $request)
    {

        Log::debug('DiscoveryController index');
        
        // get all params
        $input = $request->all();

        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection
        $extraDatas = new TrustAnchorExtraData;
        $paginatedextraDatas = new TrustAnchorExtraData;

        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $extraDatas = $extraDatas->search($input['searchTerm']);
            $paginatedextraDatas = $paginatedextraDatas->search($input['searchTerm']);
        }

        // sort logic
        if(!empty($input['sort'])) {
          $sort = json_decode($input['sort']);
          if($sort->field != '' && $sort->type != '') {
            $paginatedextraDatas = $paginatedextraDatas->orderBy($sort->field, $sort->type);
          }
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedextraDatas = $paginatedextraDatas->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedextraDatas = $paginatedextraDatas->get();
        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $extraDatas->count(),
          'rows' => $paginatedextraDatas,
        ];
    }

    public function unique(Request $request)
    {

        Log::debug('DiscoveryController unique');
        
        // get all params
        $input = $request->all();

        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection
        $extraDatas = new TrustAnchorExtraDataUnique;
        $paginatedextraDatas = new TrustAnchorExtraDataUnique;

        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $extraDatas = $extraDatas->search($input['searchTerm']);
            $paginatedextraDatas = $paginatedextraDatas->search($input['searchTerm']);
        }

        // sort logic
        if(!empty($input['sort'])) {
          $sort = json_decode($input['sort']);
          if($sort->field != '' && $sort->type != '') {
            $paginatedextraDatas = $paginatedextraDatas->orderBy($sort->field, $sort->type);
          }
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedextraDatas = $paginatedextraDatas->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedextraDatas = $paginatedextraDatas->get();
        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $extraDatas->count(),
          'rows' => $paginatedextraDatas,
        ];
    }
}
