<?php

namespace App\Jobs;

use App\{ KycTemplate, Constant };
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\WebhookServer\WebhookCall;
use App\Transformers\KycTemplateTransformer;
use Illuminate\Support\Facades\Log;

class DataInternalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The kycTemplate instance.
     *
     * @var \App\KycTemplate
     */
    protected $model;

    /**
     * invokedMethod.
     *
     * @var String
     */
    protected $invokedMethod;


    /**
     * Create a new job instance.
     *
     * @return void
     */
     public function __construct(KycTemplate $model, String $invokedMethod)
    {
      $this->model = $model;
      $this->invokedMethod = $invokedMethod;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
     public function handle()
    {
      $url  = (substr($this->invokedMethod, 0, 2) === 'BE') ? $this->model->sender_ta_url : $this->model->beneficiary_ta_url;
      $kycTemplateJSON = fractal()->item($this->model)->transformWith(new KycTemplateTransformer())->toArray();

      $webhook_url = Constant::where('name', 'webhook_url')->first();
      $webhook_secret = Constant::where('name', 'webhook_secret')->first();

      if ( !empty($webhook_url->value) && !empty($webhook_secret->value) )
      {
        WebhookCall::create()
        ->url($webhook_url->value)
        ->meta(['invokedMethod' => $this->invokedMethod, 'hasState' => true])
        ->payload([
          "eventType" => $this->invokedMethod,
          "kycTemplate" => $kycTemplateJSON['data']
        ])
        ->useSecret($webhook_secret->value)
        ->dispatch();

        $this->model->webhook_status()->transitionTo($to = "{$this->invokedMethod}_SENT");

      } else {
         new \Exception("Missing webhook_url or webhook_secret");
      }



    }


}
