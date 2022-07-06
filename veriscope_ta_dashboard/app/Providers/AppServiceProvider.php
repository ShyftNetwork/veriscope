<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

      /*
       * Creates a new custom rule in the Validator facade.
       */
      Validator::extend('iexists', function ($attribute, $value, $parameters, $validator) {
        $query = DB::table($parameters[0]);
        $column = $query->getGrammar()->wrap($parameters[1]);

        return $query->whereRaw("lower({$column}) = lower(?)", [$value])->count();
      });

    }
}
