<?php

namespace App\Observers;

use App\{ KycTemplate, TrustAnchor, SmartContractAttestation  };
use Illuminate\Support\Facades\Log;
use App\Jobs\CoinTransactionHashJob;
use Illuminate\Support\Str;

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

  public function updating(KycTemplate $kycTemplate)
  {

     if (Str::length($kycTemplate->coin_transaction_hash) > 0) {
        $kycTemplate->coin_transaction_hash = $kycTemplate->coin_transaction_hash;
     }

  }

  /**
   * Handle the KycTemplate "updated" event.
   *
   * @param  \App\KycTemplate  $kycTemplate
   * @return void
   */
  public function updated(KycTemplate $kycTemplate)
  {

    if ($kycTemplate->coin_transaction_hash && $kycTemplate->wasChanged('coin_transaction_hash')) {
       CoinTransactionHashJob::dispatch($kycTemplate);
    }

     //beneficiary user address crypto proof status
     if ($kycTemplate->status()->was('BE_CRYPTO_PROOF_VERIFIED') && !empty($kycTemplate->beneficiary_user_address_crypto_proof) && !$kycTemplate->beneficiary_user_address_crypto_proof_status) {
       $kycTemplate->beneficiary_user_address_crypto_proof_status = true;
       $kycTemplate->save();
     }

  }
}
