<?php

namespace App\Jobs;


use Spatie\WebhookServer\WebhookCall;
use App\{ KycTemplate, Constant };
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Transformers\KycTemplateTransformer;


class CoinTransactionHashJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The kycTemplate instance.
     *
     * @var \App\KycTemplate
     */
    protected $model;

    /**
     * Create a new job instance.
     *
     * @return void
     */
     public function __construct(KycTemplate $model)
    {
      $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
     public function handle()
    {

      $kycTemplateJSON = fractal()->item($this->model)->transformWith(new KycTemplateTransformer())->toArray();

      $webhook_url = Constant::where('name', 'webhook_url')->first();
      $webhook_secret = Constant::where('name', 'webhook_secret')->first();

      if ( !empty($webhook_url->value) && !empty($webhook_secret->value) )
      {


        WebhookCall::create()
        ->url($webhook_url->value)
        ->meta(['hasState' => false])
        ->payload([
          "eventType" => 'COIN_TRANSACTION_HASH_UPDATED',
          "kycTemplate" => $kycTemplateJSON['data']
        ])
        ->useSecret($webhook_secret->value)
        ->dispatch();



      } else {
         new \Exception("Missing webhook_url or webhook_secret");
      }
    }

}
