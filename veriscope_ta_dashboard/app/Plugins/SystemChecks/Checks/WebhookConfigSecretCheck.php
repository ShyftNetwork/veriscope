<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Constant;
use App\Plugins\SystemChecks\Check;

class WebhookConfigSecretCheck implements Check
{
    public function getId()
    {
        return 'webhook-config-secret';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        // Check if 'webhook_secret' column exist in the 'constants' table
        $columnsExist = Constant::query()
            ->whereIn('name', ['webhook_secret'])
            ->count() === 1;

        if (!$columnsExist) {
            $result['message'] = 'Webhook secret configuration column missing';
            return $result;
        }

        // Check if webhook secret is non-empty
        $webhookSecret = Constant::where('name', 'webhook_secret')->value('value');
        if (empty($webhookSecret)) {
           $result['message'] = 'Webhook secret not set';
           return $result;
        }else {
           $result['success'] =  true;
           $result['message'] = 'Webhook configuration secret is set';
           return $result;
        }


    }
}
