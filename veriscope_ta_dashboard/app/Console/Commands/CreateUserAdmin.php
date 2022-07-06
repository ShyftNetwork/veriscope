<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Validator;
use App\User;
use Illuminate\Support\Facades\Hash;

class CreateUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'createuser:admin {first_name?} {last_name?} {email?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin user';

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

        $first_name = $this->validate_cmd_input(function() {
          return $this->argument('first_name') ?: $this->ask('Enter first name');
        }, ['first_name','required|string']);

        $last_name = $this->validate_cmd_input(function() {
          return $this->argument('last_name') ?: $this->ask('Enter last name');
        }, ['last_name','required|string']);

        $email = $this->validate_cmd_input(function() {
            return $this->argument('email') ?: $this->ask('Enter email');
        }, ['email','required|string|email|max:255|unique:users']);

        $password = $this->validate_cmd_input(function() {
            return $this->argument('password') ?: $this->ask('Enter password');
        }, ['password','required|string|min:6']);

        $data = [
          'first_name' => $first_name,
          'last_name' => $last_name,
          'email' => $email,
          'password' => Hash::make($password),
          'last_state' => 'approved'
        ];

        $godRole = \HttpOz\Roles\Models\Role::findBySlug('god');
        $adminRole = \HttpOz\Roles\Models\Role::findBySlug('super');
        $memberRole = \HttpOz\Roles\Models\Role::findBySlug('member');


        $user = User::create($data);

        $user->attachRole($godRole);

        $user->attachRole($memberRole);

        $this->info('The admin user was created successfully with email:'.$email.' and password:'.$password);

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
