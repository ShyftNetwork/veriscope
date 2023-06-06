<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\Http;

class HTTPApiCheck implements Check
{
    public function getId()
    {
        return 'http-api';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        try {
          $apiUrl = env('HTTP_API_URL');
          $response = Http::timeout(5)->get($apiUrl);

          if ($response->successful()) {
            $result['success'] =  true;
            $result['message'] = 'HTTP API is running';
            return $result;
          } else {
            $result['message'] = 'HTTP API is not running';
            return $result;
          }

        } catch (\Exception $e) {
          $result['message'] = 'HTTP API is not running';
          return $result;
        }



    }
}
