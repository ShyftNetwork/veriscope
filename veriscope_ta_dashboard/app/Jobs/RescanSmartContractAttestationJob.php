<?php

namespace App\Jobs;

use App\SmartContractAttestation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\SmartContractAttestationJob;

class RescanSmartContractAttestationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Define the chunk size
    protected $chunkSize;

    /**
     * Create a new job instance.
     *
     * @param int $chunkSize
     */
    public function __construct(int $chunkSize = 200)
    {
        $this->chunkSize = $chunkSize;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        SmartContractAttestation::query()
            ->orderBy('id') // Use the primary key or a unique column for better performance
            ->chunk($this->chunkSize, function ($models) {
                // Process the chunked models
                foreach ($models as $model) {
                    // Perform your desired operation on each model
                    // For example, call a specific method or update a field
                    $this->processModel($model);
                }
            });
    }

    /**
     * Process a single model.
     *
     * @param \App\SmartContractAttestation  $smartContractAttestation
     * @return void
     */
    protected function processModel(SmartContractAttestation $smartContractAttestation)
    {
      if( !empty($smartContractAttestation->coin_address))
      {
        SmartContractAttestationJob::dispatch($smartContractAttestation);
      }
    }
}
