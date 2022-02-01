<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Validator;
use msztorc\LaravelEnv\Env;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class PassportEnvLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passportenv:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create passport client link';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new ClientRepository;
    }


    public function handle() {

      $env = new Env();
      $firstClient = Client::firstOrFail();

      if($firstClient->id) {
        $client = $this->client->find($firstClient->id);
        $env->setValue('PASSPORT_PERSONAL_ACCESS_CLIENT_ID',$firstClient->id);
        $env->setValue('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET',$client->secret);

        $this->info('Your passport oauth client enviroment variables are set');
      } else{
        $this->info('Sorry could not find any passport oauth client');

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
