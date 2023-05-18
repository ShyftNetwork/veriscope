<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\Redis;

class RedisCheck implements Check
{
    public function getId()
    {
        return 'redis';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        try {
            $redis = Redis::connection();
            $redis->ping();
            $result['success'] =  true;
            $result['message'] = 'Redis running';
            return $result;
        } catch (\Exception $e) {
            $result['message'] = 'Redis not running due to error: '. $e->getMessage();
            return $result;
        }



    }
}
