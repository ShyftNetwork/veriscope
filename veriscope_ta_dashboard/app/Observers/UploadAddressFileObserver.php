<?php

namespace App\Observers;

use App\{ UploadAddressFile };
use App\Jobs\BloomFilterJob;


class UploadAddressFileObserver
{

  /**
   * Handle the UploadAddressFile "created" event.
   *
   * @param  \App\UploadAddressFile  $uploadAddressFile
   * @return void
   */
  public function created(UploadAddressFile $uploadAddressFile)
  {
     BloomFilterJob::dispatch($uploadAddressFile);
  }


}
