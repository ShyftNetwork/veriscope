<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\{TrustAnchorExtraData, TrustAnchorExtraDataUnique, TrustAnchorExtraDataUniqueValidation};

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

        // add custom column for editing and verifying
        foreach($paginatedextraDatas as $transaction) {

          $trust_anchor_address = $transaction['trust_anchor_address'];
          $key_value_pair_name = $transaction['key_value_pair_name'];

          $count = TrustAnchorExtraDataUniqueValidation::where('trust_anchor_address', $trust_anchor_address)->where('key_value_pair_name', $key_value_pair_name)->count();

          if ($count > 0) {
            $transaction['action'] = '<a href="/backoffice/discovery/'.$trust_anchor_address.'|'.$key_value_pair_name.'/validations" class="btn btn--alt btn--sm">Validations</a> ';
          }
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

    public function validations(Request $request)
    {

        Log::debug('App\Http\Controllers\Api\DiscoveryController validations');

        // get all params
        $input = $request->all();

        Log::debug($input);
        $filter = explode('|', $input['filter']);
        Log::debug('filter');
        Log::debug($filter);

        $trust_anchor_address = $filter[0];
        $key_value_pair_name = $filter[1];
        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection

        $list = TrustAnchorExtraDataUniqueValidation::where('trust_anchor_address', $trust_anchor_address)->where('key_value_pair_name', $key_value_pair_name)->get();

        foreach($list as $transaction) {
          $item = TrustAnchorExtraDataUnique::where('trust_anchor_address', $trust_anchor_address)->where('key_value_pair_name', $key_value_pair_name)->first();
          $transaction['key_value_pair_value'] = $item['key_value_pair_value'];

        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => 13,
          'rows' => $list,
        ];
    }
}
