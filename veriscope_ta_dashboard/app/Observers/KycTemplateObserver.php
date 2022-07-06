<?php

namespace App\Observers;

use App\{ KycTemplate, TrustAnchor, SmartContractAttestation  };

class KycTemplateObserver
{

  /**
   * Handle the KycTemplate "created" event.
   *
   * @param  \App\KycTemplate  $kycTemplate
   * @return void
   */
  public function created(KycTemplate $kycTemplate)
  {

     $kycTemplate->status()->transitionTo($to = 'START');

  }

  /**
   * Handle the KycTemplate "updated" event.
   *
   * @param  \App\KycTemplate  $kycTemplate
   * @return void
   */
  public function updated(KycTemplate $kycTemplate)
  {


  }



}
