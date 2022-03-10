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

   if( !empty($smartContractAttestation->public_data_decoded) && !empty($smartContractAttestation->documents_matrix_encrypted_decoded)  && !empty($smartContractAttestation->availability_address_encrypted_decoded) )
   {

     SmartContractAttestationJob::dispatch($smartContractAttestation);

   }

  }


}
