<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Support\EthereumToolsUtils;

class CryptoPublicKey implements Rule
{

    public $address;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($address)
    {
      $this->address = $address;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

      try {

        //Fix for 0x and 04
        $input = EthereumToolsUtils::makeUncompressed($value);

        $isValid = EthereumToolsUtils::publicKeyToAddress($input);

        if($isValid == strtolower($this->address)) {
          return true;
        } else{
          return false;
        }

      } catch (\Throwable $e) {

        return false;

      }

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The public key supplied does not appear to be a valid.';
    }
}
