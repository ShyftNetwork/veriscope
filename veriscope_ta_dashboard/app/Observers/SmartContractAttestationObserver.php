<?php

namespace App\Observers;

use App\{ SmartContractAttestation };
use App\Jobs\SmartContractAttestationJob;


class SmartContractAttestationObserver
{

  /**
   * Handle the SmartContractAttestation "updated" event.
   *
   * @param  \App\SmartContractAttestation  $smartContractAttestation
   * @return void
   */
  public function updated(SmartContractAttestation $smartContractAttestation)
  {

   if( !empty($smartContractAttestation->coin_address))
   {

     SmartContractAttestationJob::dispatch($smartContractAttestation);

   }

  }


}
