<?php
namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\{TrustAnchor, SmartContractAttestation, KycTemplate};

class KycTemplateEventSubscriber implements ShouldQueue
{

    /**
     * Handle final failed events.
     */
    public function handleFinalFailed($event) {

      $hasState = $event->meta['hasState'] ? $event->meta['hasState'] : false;

      if ($hasState) {

        $attestation_hash = $event->payload['kycTemplate']['AttestationHash'];
        $invokedMethod = $event->meta['invokedMethod'] ? $event->meta['invokedMethod'] : false;

        if($attestation_hash){

          try {
            $kt = KycTemplate::where('attestation_hash', $attestation_hash)->firstOrFail();

            Log::debug('invokedMethod');
            Log::debug($invokedMethod);

            if (strpos($invokedMethod, 'ENC') !== false) {
              Log::debug('ENC');
              $kt->ivms_status()->transitionTo($to = $invokedMethod.'_FAILED');
            } else {
              $kt->webhook_status()->transitionTo($to = $invokedMethod.'_FAILED');
            }

          } catch (\Exception $e) {
            Log::debug('Exception error');
            Log::debug($e->getMessage());
          }

        }


      }



    }

    /**
     * Handle succeeded events.
     */
    public function handleSucceeded($event) {

      $hasState = $event->meta['hasState'] ? $event->meta['hasState'] : false;

      if ($hasState) {

      $attestation_hash = $event->payload['kycTemplate']['AttestationHash'];
      $invokedMethod = $event->meta['invokedMethod'] ? $event->meta['invokedMethod'] : false;

      if($attestation_hash){

        try {
          $kt = KycTemplate::where('attestation_hash', $attestation_hash)->firstOrFail();


          Log::debug('invokedMethod');
          Log::debug($invokedMethod);



          if (strpos($invokedMethod, 'ENC') !== false) {
            Log::debug('ENC');
            $kt->ivms_status()->transitionTo($to = $invokedMethod.'_RECEIVED');
          } else {
            $kt->webhook_status()->transitionTo($to = $invokedMethod.'_RECEIVED');
          }

        } catch (\Exception $e) {
          Log::debug('Exception error');
          Log::debug($e->getMessage());
        }

       }


      }





    }


    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {


      $events->listen(
          'Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent',
          [KycTemplateEventSubscriber::class, 'handleFinalFailed']
      );


      $events->listen(
          'Spatie\WebhookServer\Events\WebhookCallSucceededEvent',
          [KycTemplateEventSubscriber::class, 'handleSucceeded']
      );

    }
}
