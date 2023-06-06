<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Plugins\SystemChecks\Check;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class InternetConnectivityCheck implements Check
{
    public function getId()
    {
        return 'internet-connectivity';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        try {
            $client = new Client([
                'timeout' => 5,
                'connect_timeout' => 5,
            ]);
            $response = $client->get('https://www.google.com');
            if ($response->getStatusCode() === 200) {
                $result['success'] =  true;
                $result['message'] = 'Internet connectivity OK';
                return $result;
            } else {
                $result['message'] = 'Internet connectivity check failed';
                return $result;
            }
        } catch (\Exception $e) {
            $result['message'] = 'Unable to connect to internet due to error: '. $e->getMessage();
            return $result;
        }


    }
}
