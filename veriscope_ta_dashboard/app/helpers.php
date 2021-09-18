<?php

function getISO3166CountryCode(string $country) {
    switch (strtolower($country)) {
        case 'canada':
        case 'ca':
            return 'CA';
        break;
    }
}

function formatPhone(string $phone) {
    return preg_replace('/\D/', '', $phone);
}

function str_snake_title(string $str) {
    return title_case(str_replace('_', ' ', $str));
}

function comma_number($number, $decimalPlaces = 0) {
    $numberDecimalsPlaces = strlen(substr(strrchr((string)$number, "."), 1));
    if($numberDecimalsPlaces > $decimalPlaces) $decimalPlaces = $numberDecimalsPlaces;
    if(is_null($number)) $number = 0;
    return  money_format('%!.'.$decimalPlaces.'n', $number);
}

function cryptoLookup($key) {
    $cryptos = collect(config('shyft.cryptos'));
    $crypto = $cryptos->where('symbol', $key)->first();
    if(!empty($crypto)) {
      return $crypto['name'];
    }
    return '';
}
