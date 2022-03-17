<?php

namespace App\Jobs;


use Spatie\WebhookServer\WebhookCall;
use App\{ SmartContractAttestation };
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Constant;


class SmartContractAttestationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The smartContractAttestation instance.
     *
     * @var \App\SmartContractAttestation
     */
    protected $smartContractAttestation;


    /**
     * Create a new job instance.
     *
     * @return void
     */
     public function __construct(SmartContractAttestation $smartContractAttestation)
    {
      $this->smartContractAttestation = $smartContractAttestation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
     public function handle()
    {


      $webhook_url = Constant::where('name', 'webhook_url')->first();

      $webhook_secret = Constant::where('name', 'webhook_secret')->first();

      if ( !empty($webhook_url->value) && !empty($webhook_secret->value) )
      {
        WebhookCall::create()->url($webhook_url->value)
        ->payload([
          'ta_account' => $this->smartContractAttestation->ta_account,
          'jurisdiction' => $this->smartContractAttestation->jurisdiction,
          'effective_time' => $this->smartContractAttestation->effective_time,
          'expiry_time' => $this->smartContractAttestation->expiry_time,
          'is_managed' => $this->smartContractAttestation->is_managed,
          'attestation_hash' => $this->smartContractAttestation->attestation_hash,
          'transaction_hash' => $this->smartContractAttestation->transaction_hash,
          'user_account' => $this->smartContractAttestation->user_account,
          'public_data' => $this->smartContractAttestation->public_data,
          'public_data_decoded' => $this->smartContractAttestation->public_data_decoded,
          'documents_matrix_encrypted' => $this->smartContractAttestation->documents_matrix_encrypted,
          'documents_matrix_encrypted_decoded' => $this->smartContractAttestation->documents_matrix_encrypted_decoded,
          'availability_address_encrypted' => $this->smartContractAttestation->availability_address_encrypted,
          'availability_address_encrypted_decoded' => $this->smartContractAttestation->availability_address_encrypted_decoded,
          'version_code' => $this->smartContractAttestation->version_code,
          'coin_blockchain' => $this->smartContractAttestation->coin_blockchain,
          'coin_token' => $this->smartContractAttestation->coin_token,
          'coin_address' => $this->smartContractAttestation->coin_address,
          'coin_memo' => $this->smartContractAttestation->coin_memo
        ])
        ->useSecret($webhook_secret->value)
        ->dispatch();
      }

    }


}
