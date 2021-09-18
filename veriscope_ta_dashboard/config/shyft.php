<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shyft Variables
    |--------------------------------------------------------------------------
    |
    | core services url used for customer registration
    | document services url used for photo id
    |
    */
    'shyftIncentiveValue'  => env('SHYFT_INCENTIVE_VALUE', 100),
    'onboarding' => env('SHYFT_ONBOARDING', true), // can a user sign up in this experience
    'url' => env('SHYFT_ONBOARDING_URL', 'http://localhost'),
    'cryptoSecurityHash' => env('APP_CRYPTO_SECURITY_HASH', ''),

];
