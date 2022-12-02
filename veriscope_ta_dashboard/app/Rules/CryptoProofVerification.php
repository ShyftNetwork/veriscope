<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class CryptoProofVerification implements Rule
{

    public $expected_address;

    public $trust_anchor_pubkey;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($expected_address, $trust_anchor_pubkey)
    {
      $this->expected_address = $expected_address;
      $this->trust_anchor_pubkey = $trust_anchor_pubkey;
    }

    /**
     * Replace single quotes with double quotes
     *
     * @return string
     */
    public function json_string_encode( $str ) {
       // Replace the string passed
       return str_replace('\'','"',$str);
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

      // if crypto_proof is empty
      if (empty($value)) {
        return true;
      }elseif (!file_exists("/opt/veriscope/veriscope_addressproof/test.py")) {
        return true;
      } else {
        $escaped_data = base64_encode($this->json_string_encode($value));
        $cmd_str = "python3 /opt/veriscope/veriscope_addressproof/test.py {$this->expected_address} {$this->trust_anchor_pubkey} {$escaped_data}";
        $command = escapeshellcmd($cmd_str);
        try {
            $output = shell_exec($command);
        } catch (Exception $e) {
            return false;
        }
        $isValid  = filter_var($output, FILTER_VALIDATE_BOOLEAN);
        if($isValid) {
          return true;
        } else{
          return false;
        }
      }

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The crypto proof supplied does not appear to be valid.';
    }
}
