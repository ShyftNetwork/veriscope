<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;

class HttpApiUrlCheck implements Check
{
    public function getId()
    {
        return 'http-api-url';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];
        $apiUrl = env('HTTP_API_URL');

        if (empty($apiUrl)) {
            $result['message'] = 'HTTP_API_URL variable is not set in the .env file';
            return $result;
        } else {
            $result['success'] =  true;
            $result['message'] = 'HTTP_API_URL variable is set to ' . $apiUrl;
            return $result;
        }

    }
}
