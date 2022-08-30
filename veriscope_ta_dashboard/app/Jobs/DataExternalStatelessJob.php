<?php

namespace App\Jobs;

use App\{ KycTemplate };
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\WebhookServer\WebhookCall;
use App\Transformers\KycTemplateTransformer;
use Illuminate\Support\Facades\Log;

class DataExternalStatelessJob implements ShouldQueue
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

      // Reversing sides
      $url  = (substr($this->invokedMethod, 0, 2) === 'OR') ? $this->model->sender_ta_url : $this->model->beneficiary_ta_url;
      $kycTemplateJSON = fractal()->item($this->model)->transformWith(new KycTemplateTransformer())->toArray();

      $who = strtolower(substr($this->invokedMethod, 0, 2));

      WebhookCall::create()
      ->url($url)
      ->meta(['invokedMethod' => $this->invokedMethod, 'hasState' => false])
      ->payload([
        "eventType" => $this->invokedMethod,
        "kycStateMachine" => ["code" => $this->model->status()->getCustomProperty($who.'_ivms_state_code') ],
        "kycTemplate" => $kycTemplateJSON['data']
      ])
      ->doNotSign()
      ->dispatch();

    }


}
