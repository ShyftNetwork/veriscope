<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Plugins\SystemChecks\SystemChecksManager;
use App\Notifications\SlackSystemCheckNotification;
use App\{ Constant };

class SystemChecksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $systemChecks = SystemChecksManager::runAllChecksForAPI();

        foreach ($systemChecks as $key => $value) {
            if (!$value['success']) {
                $this->sendSlackNotification($key, $value['message']);
            }
        }
    }

    protected function sendSlackNotification($key, $message)
    {

        $slack_webhook_url = Constant::where('name', 'slack_webhook_url')->first();

        // If slack_webhook_url constant value is not empty
        if (!empty($slack_webhook_url->value)) {

          $notifiable = new class($slack_webhook_url->value) {
              use \Illuminate\Notifications\Notifiable;

              protected $webhookUrl;

              public function __construct($webhookUrl)
              {
                  $this->webhookUrl = $webhookUrl;
              }

              public function routeNotificationForSlack()
              {
                  return $this->webhookUrl;
              }
          };
          $data = [
              'message' => "System Check Failed: {$key}\nError: {$message}",
          ];

          $notification = new SlackSystemCheckNotification($data);
          $notifiable->notify($notification);

        }
    }
}
