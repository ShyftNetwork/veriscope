<?php

namespace App\Http\Controllers\Api\Server;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Plugins\SystemChecks\SystemChecksManager;

class SystemChecksController extends Controller
{
    public function index()
    {
        $systemChecks = SystemChecksManager::runAllChecksForAPI();

        return response()->json($systemChecks);
    }
}
