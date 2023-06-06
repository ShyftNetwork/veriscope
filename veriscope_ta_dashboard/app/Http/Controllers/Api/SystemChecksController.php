<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Plugins\SystemChecks\SystemChecksManager;

class SystemChecksController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        // get all params
        $input = $request->all();

        // set defaults for pagination
        $page = !empty($input['page']) ? (int)$input['page'] : 1;
        $perPage = !empty($input['perPage']) ? (int)$input['perPage'] : 50;

        $systemChecksRows = SystemChecksManager::runAllChecksForUI();


        // return the current params and rows back
        return [
          'serverParams' => [
            'sort' => !empty($input['sort']) ? $input['sort'] : '',
            'page' => $page,
            'perPage' => $perPage,
          ],
          'totalRecords' => count($systemChecksRows),
          'rows' => $systemChecksRows,
        ];
    }


}
