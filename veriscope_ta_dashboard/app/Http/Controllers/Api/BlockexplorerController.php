<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\{SmartContractAttestation, SmartContractTransaction};

class BlockexplorerController extends Controller
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

        Log::debug('BlockexplorerController index');

        // get all params
        $input = $request->all();

        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection
        $event_type = 'EVT_setAttestation';
        $attestations = new SmartContractAttestation;
        $paginatedAttestations = new SmartContractAttestation;

        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $attestations = $attestations->search($input['searchTerm']);
            $paginatedAttestations = $paginatedAttestations->search($input['searchTerm']);
        }

        // sort logic
        if(!empty($input['sort'])) {
          $sort = json_decode($input['sort']);
          if($sort->field != '' && $sort->type != '') {
            $paginatedAttestations = $paginatedAttestations->orderBy($sort->field, $sort->type);
          }
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedAttestations = $paginatedAttestations->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedAttestations = $paginatedAttestations->get();
        }

        // add custom column for editing and verifying
        foreach($paginatedAttestations as $attestation) {
          // $data = json_decode($event->payload);
          // $message = $data->message;
          // Log::debug($message);
          // $event->payload = $data->data;
          // // $event->payload = $data['message'];
          $attestation['action'] = '<a href="/backoffice/blockexplorer/attestation/'.$attestation->id.'/view" class="btn btn--alt btn--sm">view</a> ';
          $attestation['ta-account'] = '<a href="/backoffice/blockexplorer/ta-account/'.$attestation->ta_account.'/view" class="btn btn--alt btn--sm">'.$attestation->ta_account.'</a> ';
          $attestation['user-account'] = '<a href="/backoffice/blockexplorer/user-account/'.$attestation->user_account.'/view" class="btn btn--alt btn--sm">'.$attestation->user_account.'</a> ';
        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $attestations->count(),
          'rows' => $paginatedAttestations,
        ];
    }

    public function transactions(Request $request)
    {

        Log::debug('BlockexplorerController transactions');

        // get all params
        $input = $request->all();

        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection
        $transactions = new SmartContractTransaction;
        $paginatedTransactions = new SmartContractTransaction;

        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $transactions = $transactions->search($input['searchTerm']);
            $paginatedTransactions = $paginatedTransactions->search($input['searchTerm']);
        }

        // sort logic
        if(!empty($input['sort'])) {
          $sort = json_decode($input['sort']);
          if($sort->field != '' && $sort->type != '') {
            $paginatedTransactions = $paginatedTransactions->orderBy($sort->field, $sort->type);
          }
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedTransactions = $paginatedTransactions->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedTransactions = $paginatedTransactions->get();
        }

        // add custom column for editing and verifying
        foreach($paginatedTransactions as $transaction) {
          // $data = json_decode($event->payload);
          // $message = $data->message;
          // Log::debug($message);
          // $event->payload = $data->data;
          // $event->payload = $data['message'];
          $transaction['action'] = '<a href="/backoffice/blockexplorer/transaction/'.$transaction->id.'/view" class="btn btn--alt btn--sm">view</a> ';
          $transaction['to_address_action'] = '<a href="/backoffice/blockexplorer/address/'.$transaction->to_address.'/view" class="btn btn--alt btn--sm">'.$transaction->to_address.'</a> ';
          $transaction['from_address_action'] = '<a href="/backoffice/blockexplorer/address/'.$transaction->from_address.'/view" class="btn btn--alt btn--sm">'.$transaction->from_address.'</a> ';

        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $transactions->count(),
          'rows' => $paginatedTransactions,
        ];
    }

    public function get_transaction(Request $request)
    {

        Log::debug('BlockexplorerController get_transaction');

        // get all params
        $input = $request->all();

        Log::debug($input);
        $filter = explode('|', $input['filter']);
        $transaction_id = $filter[1];
        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection

        $result = SmartContractTransaction::where('id', $transaction_id)->first();

        $list = [['field' => 'ID', 'data'  => $result['id']],
                    ['field' => 'Transaction Hash', 'data'  => $result['transaction_hash']],
                    ['field' => 'Created On', 'data' => $result['created_at']],

                    ['field' => 'Nonce', 'data'  => $result['nonce']],
                    ['field' => 'Block Hash', 'data'  => $result['block_hash']],
                    ['field' => 'Block Number', 'data'  => $result['block_number']],
                    ['field' => 'Transaction Index', 'data'  => $result['transaction_index']],
                    ['field' => 'From Address', 'data'  => $result['from_address']],
                    ['field' => 'To Address', 'data' => $result['to_address']],
                    ['field' => 'Value', 'data'  => $result['value']],
                    ['field' => 'Gas', 'data'  => $result['gas']],
                    ['field' => 'Gas Price', 'data'  => $result['gas_price']],
                    ['field' => 'Payload', 'data'  => $result['payload']]

                  ];

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

    public function get_address_transactions(Request $request)
    {

        Log::debug('BlockexplorerController get_address_transactions');

        // get all params
        $input = $request->all();

        Log::debug($input);
        $filter = explode('|', $input['filter']);
        $address = $filter[1];
        Log::debug($address);
        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection

        $transactions = SmartContractTransaction::where('to_address', $address)->orWhere('from_address', $address);
        $paginatedTransactions = SmartContractTransaction::where('to_address', $address)->orWhere('from_address', $address);


        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $transactions->search($input['searchTerm']);
            $paginatedTransactions->search($input['searchTerm']);
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedTransactions = $paginatedTransactions->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedTransactions = $paginatedTransactions->get();
        }

        // add custom column for editing and verifying
        foreach($paginatedTransactions as $transaction) {
          // override last_state with camel case state


        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $transactions->count(),
          'rows' => $paginatedTransactions,
        ];
    }

    public function get_attestation_components(Request $request)
    {

        Log::debug('BlockexplorerController get_attestation_components');

        // get all params
        $input = $request->all();

        Log::debug($input);
        $filter = explode('|', $input['filter']);
        $attestation_id = $filter[1];
        Log::debug($attestation_id);
        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection

        $result = SmartContractAttestation::where('id', $attestation_id)->first();

        $list = [['field' => 'ID', 'data'  => $result['id']],
                    ['field' => 'Created On', 'data'  => $result['created_at']],
                    ['field' => 'Trust Anchor Account', 'data'  => $result['ta_account']],
                    ['field' => 'User Account', 'data'  => $result['user_account']],

                    ['field' => 'Jurisdiction', 'data' => $result['jurisdiction']],

                    ['field' => 'Effective Time', 'data'  => $result['effective_time']],
                    ['field' => 'Expiry Time', 'data'  => $result['expiry_time']],
                    ['field' => 'Public Data', 'data'  => $result['public_data']],
                    ['field' => 'Public Data Decoded', 'data'  => $result['public_data_decoded']],
                    ['field' => 'Documents Matrix Encrypted', 'data'  => $result['documents_matrix_encrypted']],
                    ['field' => 'Documents Matrix Encrypted Decoded', 'data'  => $result['documents_matrix_encrypted_decoded']],
                    ['field' => 'Availability Address Encrypted', 'data'  => $result['availability_address_encrypted']],
                    ['field' => 'Availability Address Encrypted Decoded', 'data'  => $result['availability_address_encrypted_decoded']],

                    ['field' => 'Verison Code', 'data'  => $result['version_code']],
                    ['field' => 'Coin Blockchain', 'data'  => $result['coin_blockchain']],
                    ['field' => 'Coin Token', 'data'  => $result['coin_token']],
                    ['field' => 'Coin Address', 'data'  => $result['coin_address']],
                    ['field' => 'Coin Memo', 'data'  => $result['coin_memo']],

                    ['field' => 'Is Managed', 'data' => $result['is_managed']],
                    ['field' => 'Attestation Hash', 'data'  => $result['attestation_hash']],
                    ['field' => 'Transaction Hash', 'data'  => $result['transaction_hash']],

                    ['field' => 'Block number', 'data'  => $result['block_number']]

                  ];


        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => 13,
          'rows' => $list,
        ];
    }

    public function get_ta_account_attestations(Request $request)
    {

        Log::debug('BlockexplorerController get_ta_account_attestations');

        // get all params
        $input = $request->all();

        Log::debug($input);
        $filter = explode('|', $input['filter']);
        $trustAnchorAccount = $filter[1];
        Log::debug($trustAnchorAccount);
        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection

        $attesations = SmartContractAttestation::where('ta_account', $trustAnchorAccount);
        $paginatedAttestations = SmartContractAttestation::where('ta_account', $trustAnchorAccount);


        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $attesations->search($input['searchTerm']);
            $paginatedAttestations->search($input['searchTerm']);
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedAttestations = $paginatedAttestations->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedAttestations = $paginatedAttestations->get();
        }

        // add custom column for editing and verifying
        foreach($paginatedAttestations as $attesation) {
          // override last_state with camel case state


        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $attesations->count(),
          'rows' => $paginatedAttestations,
        ];
    }

    public function get_user_account_attestations(Request $request)
    {

        Log::debug('BlockexplorerController get_user_account_attestations');

        // get all params
        $input = $request->all();

        Log::debug($input);
        $filter = explode('|', $input['filter']);
        $userAccount = $filter[1];
        Log::debug($userAccount);
        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        // build a users, and a paginated users collection

        $attesations = SmartContractAttestation::where('user_account', $userAccount);
        $paginatedAttestations = SmartContractAttestation::where('user_account', $userAccount);


        // logic for searching
        // TODO: use a 3rd party search tool?
        if(!empty($input['searchTerm'])) {
            $attesations->search($input['searchTerm']);
            $paginatedAttestations->search($input['searchTerm']);
        }

        // apply pagination
        if($perPage !== -1) {
          $paginatedAttestations = $paginatedAttestations->offset(($page-1) * $perPage)->limit($perPage)->get();
        } else {
          $paginatedAttestations = $paginatedAttestations->get();
        }

        // add custom column for editing and verifying
        foreach($paginatedAttestations as $attesation) {
          // override last_state with camel case state


        }

        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => $attesations->count(),
          'rows' => $paginatedAttestations,
        ];
    }
}
