<?php

namespace App\Http\Controllers\Api;

use Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\{BlockchainAnalyticsAddress,SmartContractAttestation};
use App\Http\Controllers\BlockchainAnalytics\BlockchainAnalyticsController;

class BlockchainAnalyticsAddressesController extends Controller
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

        Log::debug('BlockchainAnalyticsAddressesController index');
        
        // // get all params
        $input = $request->all();

        // // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

       
        $extraDatas = new BlockchainAnalyticsAddress;
        $paginatedextraDatas = new BlockchainAnalyticsAddress;

        // // logic for searching
        // // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $extraDatas = $extraDatas->search($input['searchTerm']);
            $paginatedextraDatas = $paginatedextraDatas->search($input['searchTerm']);
        }

        // // sort logic
        if(!empty($input['sort'])) {
          $sort = json_decode($input['sort']);
          if($sort->field != '' && $sort->type != '') {
            $paginatedextraDatas = $paginatedextraDatas->orderBy($sort->field, $sort->type);
          } else {
            $paginatedextraDatas = $paginatedextraDatas->orderBy('id', 'desc');
          }
        }

        // // apply pagination
        if($perPage !== -1) {
          $paginatedextraDatas = $paginatedextraDatas->offset(($page-1) * $perPage)->limit($perPage)->select('*', 'crypto_address as wallet_address')->with('provider')->get();
        } else {
          $paginatedextraDatas = $paginatedextraDatas->with('provider')->select('*', 'crypto_address as wallet_address')->get();
        }

        foreach($paginatedextraDatas as $data) {
          $data['action'] = '<a href="/backoffice/blockchain-analytics-addresses/'.$data->id.'/view" class="btn btn--alt btn--sm">view</a> ';
        }
        Log::debug($input['sort']);

        // // return the current params and rows back
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

    public function get_report(Request $request) {
        $input = $request->all();

        
        $filter = explode('|', $input['filter']);
        $report_id = $filter[1];
       

        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

       

        $result = BlockchainAnalyticsAddress::where('id', $report_id)->with('provider')->first();

        $attestation = SmartContractAttestation::where([
          ['user_account', $result['user_account']],
          ['ta_account', $result['trust_anchor']],
          ['documents_matrix_encrypted_decoded', 'ilike', '%' . $result['crypto_address'] . '%'],
          ['availability_address_encrypted_decoded', 'ilike', '%' . $result['blockchain'] . '%']
    
        ])->first();
        $list = $this->organizeResponse($result, $attestation);
        Log::debug($attestation);
      
        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => 1,
          'rows' => $list,
        ];
    }



    private function organizeResponse($result, $attestation) {
      $input = [];

      array_push($input,['field' => 'ID', 'data'  => $result['id']]);
      array_push($input,['field' => 'Analytics Provider', 'data'  => $result['provider']['name']]);
      array_push($input,['field' => 'Trust Anchor', 'data'  => $result['trust_anchor']]);
      array_push($input,['field' => 'User Account', 'data'  => $result['user_account']]);
      array_push($input,['field' => 'Blockchain', 'data'  => $result['blockchain']]);
      array_push($input,['field' => 'Crypto Address', 'data'  => $result['crypto_address']]);
      array_push($input,['field' => 'Custodian', 'data'  => $result['custodian']]);
      array_push($input,['field' => 'Response (json)', 'data'  => $result['response']]);

      if ($result['blockchain_analytics_provider_id'] == 1 && $result['response_status_code'] == 200) {
        
      } 
      else if($result['response_status_code'] != 200) {
        $response = json_decode($result['response'], true);
        if ($result['blockchain_analytics_provider_id'] == 1) {
          array_push($input,['field' => 'Error message', 'data'  =>  $response['meta']['error_message'] ]);
        } else if ($result['blockchain_analytics_provider_id'] == 2 ) {
          array_push($input,['field' => 'Error message', 'data'  =>  $response['identifier'][0] ]);
        }
      }
      

      if($attestation) {
        array_push($input,['field' => 'Attestation', 'data'  => '<a style="min-width:auto" href="/backoffice/blockexplorer/attestation/'.$attestation->id.'/view" class="btn btn--alt btn--sm">view</a> ']);
        
      } else {

      }
      

      return $input;
    }


  public function createReport(Request $request, $id) {
      $input = $request->all();
      Log::debug($input);
      $input['user_id'] = $id;
      new BlockchainAnalyticsController($input, null );
  }

}
