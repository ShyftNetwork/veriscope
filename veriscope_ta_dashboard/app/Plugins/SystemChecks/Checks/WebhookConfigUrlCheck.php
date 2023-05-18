<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Constant;
use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\Http;

class WebhookConfigUrlCheck implements Check
{
    public function getId()
    {
        return 'webhook-config-url';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        // Check if 'webhook_url' column exist in the 'constants' table
        $columnsExist = Constant::query()
            ->whereIn('name', ['webhook_url'])
            ->count() === 1;

        if (!$columnsExist) {
            $result['message'] = 'Webhook url configuration column missing';
        }

        // Check if webhook URL is valid
        $webhookUrl = Constant::where('name', 'webhook_url')->value('value');
        if(!$webhookUrl){
           $result['message'] = 'Webhook configuration url is not set';
           return $result;
        } elseif (filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
           $result['message'] = 'Webhook configuration url is invalid';
           return $result;
        } else {

           try {

             $response = Http::get($webhookUrl);
             if ($response->successful()) {
                 $result['success'] = true;
                 $result['message'] = 'Webhook configuration url is working';
             } else {
                 $result['message'] = 'Webhook configuration url is not working';
             }
             return $result;

           } catch (\Exception $e) {

             $result['message'] = 'Unable to connect to webhook url due to error: '. $e->getMessage();
             return $result;
           }


        }
    }
}
