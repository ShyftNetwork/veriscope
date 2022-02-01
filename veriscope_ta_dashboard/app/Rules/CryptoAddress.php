<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Support\CryptoAddressValidator;

class CryptoAddress implements Rule
{

    public $type;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type)
    {
      $this->type = $type;
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
      $isValid = CryptoAddressValidator::isValid($value, $this->type);

      if($isValid) {
        return true;
      } else{
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
        return 'The address supplied does not appear to be a valid crypto address.';
    }
}
