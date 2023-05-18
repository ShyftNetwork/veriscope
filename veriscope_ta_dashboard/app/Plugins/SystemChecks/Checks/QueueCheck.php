<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;

class QueueCheck implements Check
{
    public function getId()
    {
        return 'queue';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        $output = [];
        $return_var = -1;
        exec('systemctl is-active ta-queue', $output, $return_var);

        if ($return_var === 0) {
            $result['success'] =  true;
            $result['message'] = 'Queue system is running';
            return $result;
        } else {
            $result['message'] = 'Queue system is not running';
            return $result;
        }

    }
}
