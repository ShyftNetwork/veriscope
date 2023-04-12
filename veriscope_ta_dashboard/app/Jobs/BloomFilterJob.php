<?php

namespace App\Jobs;

use App\{ UploadAddressFile };
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use BayAreaWebPro\SimpleCsv\SimpleCsv;
use PHPRedis\Filters\BloomFilter;


class BloomFilterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The uploadAddressFile instance.
     *
     * @var \App\UploadAddressFile
     */
    protected $model;





    /**
     * Create a new job instance.
     *
     * @return void
     */
     public function __construct(UploadAddressFile $model)
    {
      $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
     public function handle()
    {

      $bloom_filter = new BloomFilter();
      $bloom_filter->setConfig(
        config('bloomfilter.host'),
        config('bloomfilter.port'),
        config('bloomfilter.password')
      );
      $bloom_key   = config('bloomfilter.key');

      $lazyCollection = SimpleCsv::import(storage_path('app/'.$this->model->path));

      $lazyCollection->chunk(50)->map(function ($items) { return $items; })->each(function ($items) use ($bloom_filter, $bloom_key) {

        $uniqueKeyList = [];
        foreach ($items as $item) {
          $coin_address = strtolower($item['coin_address']);
          $uniqueKey = "{$item['coin_blockchain']}_{$item['coin_token']}_{$coin_address}";
          array_push($uniqueKeyList, $uniqueKey);
        }

        $bloom_filter->madd($bloom_key, $uniqueKeyList);
      });



    }







}
