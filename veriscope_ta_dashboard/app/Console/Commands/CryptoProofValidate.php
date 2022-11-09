<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Rules\CryptoProofVerification;

class CryptoProofValidate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cryptoproof:validate {address?} {trust_anchor_pubkey?} {cryptoproof_data?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cryptographic proof validate';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle() {

        $address = $this->validate_cmd_input(function() {
          return $this->argument('address') ?: $this->ask('Enter crypto address');
        }, ['address','required|string']);

        $trust_anchor_pubkey = $this->validate_cmd_input(function() {
          return $this->argument('trust_anchor_pubkey') ?: $this->ask('Enter trust anchor public key');
        }, ['trust_anchor_pubkey','required|string']);

        $cryptoproof_data = $this->validate_cmd_input(function() {
            return $this->argument('cryptoproof_data') ?: $this->ask('Enter crypto proof data');
        }, ['cryptoproof_data',new CryptoProofVerification($address ,$trust_anchor_pubkey) ]);


        $this->info('The proof validated successfully with address:'.$address.' and trust_anchor_pubkey:'.$trust_anchor_pubkey);

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
