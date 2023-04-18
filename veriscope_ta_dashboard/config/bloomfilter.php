<?php


return [
    /*
    |--------------------------------------------------------------------------
    | RedisBloom Client Variables
    |--------------------------------------------------------------------------
    |
    */
    'host' => env('REDIS_HOST', '127.0.0.1'),
    'password' => env('REDIS_PASSWORD', null),
    'port' => env('REDIS_PORT', 6379),
    'key'  => env('REDIS_BF_KEY','bloomdb')

];


?>
