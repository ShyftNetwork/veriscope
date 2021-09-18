<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backoffice Variables
    |--------------------------------------------------------------------------
    |
    | A list of configurable backoffice vars
    |
    */

    'enabled' => env('BACKOFFICE_ENABLED', false),
    'url' => env('BACKOFFICE_URL', 'http://localhost'),
    'results_per_page' => 20,
];