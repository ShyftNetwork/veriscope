<?php

namespace App\Jobs;

use App\{ KycTemplate, SandboxTrustAnchorUserCryptoAddress };
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\WebhookServer\WebhookCall;
use App\Transformers\KycTemplateTransformer;
use Illuminate\Support\Facades\Log;

class DataExternalJob implements ShouldQueue
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
      $test = SandboxTrustAnchorUserCryptoAddress::where('crypto_type',$this->model->coin_token)->where('crypto_address','ILIKE', $this->model->coin_address);
      // If transaction is a test transaction then automate auto-reply and set webhook to received
      if ($test->exists()) {
        app('App\Http\Controllers\KycTemplateV1Controller')->kyc_template_v1_reply($this->invokedMethod,$this->model->attestation_hash, $test->first(), $this->model->system_ta_account);

        $this->model->webhook_status()->transitionTo($to = "{$this->invokedMethod}_SENT");
        $this->model->webhook_status()->transitionTo($to = "{$this->invokedMethod}_RECEIVED");

      } else {

        WebhookCall::create()
        ->url($url)
        ->meta(['invokedMethod' => $this->invokedMethod, 'hasState' => true])
        ->payload([
          "eventType" => $this->invokedMethod,
          "kycTemplate" => $kycTemplateJSON['data']
        ])
        ->doNotSign()
        ->dispatch();

        $this->model->webhook_status()->transitionTo($to = "{$this->invokedMethod}_SENT");

      }


    }


}
