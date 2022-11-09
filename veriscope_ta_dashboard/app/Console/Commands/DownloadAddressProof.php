<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Validator;
use GuzzleHttp\Client;
use PhpZip\ZipFile;


class DownloadAddressProof extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:addressproof {github_token?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download veriscope addressproof repository';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    protected function move($source, $target){
        rename( $source,  $target);
    }

    public function handle() {

        $github_token = $this->validate_cmd_input(function() {
          return $this->argument('github_token') ?: $this->ask('Enter github token');
        }, ['github_token','required|string']);


        $outputDirExtract = storage_path("app");

        $outputFilename = $outputDirExtract."/master.zip";

        $request = new \GuzzleHttp\Client(['headers' => ['Authorization' => 'token '.$github_token ]]);
        $uri = "https://api.github.com/repos/ShyftNetwork/addressproofs/zipball/master";
         // create new archive
         $zipFile = new \PhpZip\ZipFile();
         try {
            $response = $request->request('GET', $uri  , ['sink' => $outputFilename]);
            $zipFile->openFile($outputFilename)->extractTo($outputDirExtract);
            $dirs = array_filter(glob($outputDirExtract.'/*'), 'is_dir');
            $this->move($dirs[0],'/opt/veriscope/veriscope_addressproof');
            $this->info('Shyft AddressProofs Library was successfully downloaded');
         }
         catch(\PhpZip\Exception\ZipException $e){
           $this->info('Shyft AddressProofs Error: '.$e->getMessage());
         }
         catch (\Exception $e) {
            $this->info('Shyft AddressProofs Error: '.$e->getMessage());
         }
         finally{
             $zipFile->close();
             unlink($outputFilename);
         }
    }

    /**
     * Validate an input.
     *
     * @param  mixed   $method
     * @param  array   $rules
     * @return string
     */
    public function validate_cmd_input($method, $rules)
    {
        $value = $method();
        $validate = $this->validateInput($rules, $value);

        if ($validate !== true) {
            $this->warn($validate);
            $value = $this->validate_cmd_input($method, $rules);
        }
        return $value;
    }

    public function validateInput($rules, $value)
    {

        $validator = Validator::make([$rules[0] => $value], [ $rules[0] => $rules[1] ]);

        if ($validator->fails()) {
            $error = $validator->errors();
            return $error->first($rules[0]);
        }else{
            return true;
        }

    }

}
