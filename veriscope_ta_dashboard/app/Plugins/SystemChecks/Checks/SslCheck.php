<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\Http;

class SslCheck implements Check
{
    public function getId()
    {
        return 'ssl';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        try {

          $response = Http::withOptions(['verify' => true])->get(config('app.url'));

          if ($response->successful()) {
            $result['success'] =  true;
            $result['message'] =  "SSL certificate is valid";
            return $result;

          } else {
            $result['message'] = 'Unable to connect to app URL';
            return $result;

          }

        } catch (\Exception $e) {
          $result['message'] = 'Unable to connect to app URL  due to error: '. $e->getMessage();
          return $result;
        }




    }
}
