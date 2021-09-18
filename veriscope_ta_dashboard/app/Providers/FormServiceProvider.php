<?php

namespace App\Providers;

use Form;
use Illuminate\Support\ServiceProvider;

class FormServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

      Form::component('check', 'components.checkbox', ['key', 'name', 'label', 'value', 'checked', 'disabled']);
      Form::component('pcfCheckbox', 'components.checkbox', ['key', 'name', 'label', 'value', 'checked', 'disabled']);
      Form::component('simpleInput', 'components.simple-input', ['type', 'name', 'label', 'value', 'classes']);
      Form::component('pcfInput', 'components.pcf-input', ['name', 'value' => null, 'attributes' => []]);
      Form::component('toggle', 'components.toggle', ['name', 'value' => null, 'labels' => [], 'dataToggle' => [], 'attributes' => []]);
      Form::component('pcfSelect', 'components.pcf-select', ['name', 'value' => null, 'label', 'options' => [], 'attributes' => []]);
      
    }
}
