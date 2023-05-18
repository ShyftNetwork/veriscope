<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\DB;

class DatabaseCheck implements Check
{
    public function getId()
    {
        return 'database';
    }

    public function run()
    {
       $result = ['success' => false, 'message' => ''];

        try {
            DB::connection()->getPdo();
            $result['success'] =  true;
            $result['message'] = 'Database connection OK';
            return $result;
        } catch (\Exception $e) {
            $result['message'] = 'Database connection failed: ' . $e->getMessage();
            return $result;
        }


    }
}
