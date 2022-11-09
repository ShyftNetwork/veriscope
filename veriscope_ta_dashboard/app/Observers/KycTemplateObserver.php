<?php

namespace App\Observers;

use App\{ KycTemplate, TrustAnchor, SmartContractAttestation  };
use Illuminate\Support\Facades\Log;

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

     //beneficiary user address crypto proof status
     if ($kycTemplate->status()->was('BE_CRYPTO_PROOF_VERIFIED') && !empty($kycTemplate->beneficiary_user_address_crypto_proof) && !$kycTemplate->beneficiary_user_address_crypto_proof_status) {
       $kycTemplate->beneficiary_user_address_crypto_proof_status = true;
       $kycTemplate->save();
     }

  }
}
