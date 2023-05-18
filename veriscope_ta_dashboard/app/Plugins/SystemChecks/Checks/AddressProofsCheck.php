<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;

class AddressProofsCheck implements Check
{
    public function getId()
    {
        return 'addressproofs';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];
        $path = '/opt/veriscope/veriscope_addressproof';

        if (is_dir($path) && is_readable($path)) {
            $files = scandir($path);
            if (count($files) > 2) { // Ignore . and ..
                $result['success'] =  true;
                $result['message'] = 'AddressProofs library exists';
                return $result;
            } else {
                $result['message'] = 'AddressProofs library directory is empty';
                return $result;
            }
        } else {
            $result['message'] = 'AddressProofs library directory does not exist or is not readable';
            return $result;
        }

    }
}
