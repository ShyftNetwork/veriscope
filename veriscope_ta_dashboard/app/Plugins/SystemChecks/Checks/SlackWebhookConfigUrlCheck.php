<?php

namespace App\Plugins\SystemChecks\Checks;

use App\Constant;
use App\Plugins\SystemChecks\Check;
use Illuminate\Support\Facades\Http;

class SlackWebhookConfigUrlCheck implements Check
{
    public function getId()
    {
        return 'slack-webhook-config-url';
    }

    public function run()
    {
        $result = ['success' => false, 'message' => ''];

        // Check if 'slack_webhook_url' column exist in the 'constants' table
        $columnsExist = Constant::query()
            ->whereIn('name', ['slack_webhook_url'])
            ->count() === 1;

        if (!$columnsExist) {
            $result['message'] = 'Slack Webhook url configuration column missing';
        }

        // Check if webhook URL is valid
        $webhookUrl = Constant::where('name', 'slack_webhook_url')->value('value');
        if(!$webhookUrl){
           $result['message'] = 'Slack Webhook configuration url is not set';
           return $result;
        } elseif (filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
           $result['message'] = 'Slack Webhook configuration url is invalid';
           return $result;
        } else {
           $result['success'] = true;
           $result['message'] = 'Slack Webhook configuration url is working';
           return $result;
        }
    }
}
