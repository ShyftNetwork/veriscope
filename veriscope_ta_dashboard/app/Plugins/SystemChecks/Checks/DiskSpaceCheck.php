<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;

class DiskSpaceCheck implements Check
{
    public function getId()
    {
        return 'disk-space';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];
        $freeSpace = disk_free_space('/');

        if ($freeSpace === false) {
            $result['message'] = 'Unable to check disk space';
            return $result;
        }

        if ($freeSpace < 1024 * 1024 * 1024) {
            $result['message'] = 'Low disk space: ' . $freeSpace . ' bytes free';
            return $result;
        }
        
        $result['success'] =  true;
        $result['message'] = 'Disk space OK';

        return $result;

    }
}
