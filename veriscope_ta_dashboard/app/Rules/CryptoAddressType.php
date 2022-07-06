<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\{SmartContractAttestation};

class CryptoAddressType implements Rule
{

    public $type;
    public $attestation_hash;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type, $attestation_hash)
    {
      $this->type = $type;
      $this->attestation_hash = $attestation_hash;
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
        $sca = SmartContractAttestation::where('attestation_hash', $this->attestation_hash)->firstOrFail();

        if ($this->type == 'beneficiary') {
          return (strcasecmp($sca->ta_account, $value) != 0);
        } else {
          return (strcasecmp($sca->ta_account, $value) == 0);
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
        return "The address supplied does not appear to be a valid address type.";
    }
}
