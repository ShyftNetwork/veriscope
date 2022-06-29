<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Support\EthereumToolsUtils;

class CryptoSignatureHash implements Rule
{

    public $address;

    public $pubKey;

    public $signature;

    public $plainTextMessage;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($address, $pubKey, $signature, $plainTextMessage = "VERISCOPE_USER")
    {
      $this->address = $address;
      $this->pubKey  = $pubKey;
      $this->signature = $signature;
      $this->plainTextMessage = $plainTextMessage;

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value (nessage_hash)
     * @return bool
     */
    public function passes($attribute, $value)
    {

        try {

          $publicKey = EthereumToolsUtils::stripUncompressed(EthereumToolsUtils::stripZero($this->pubKey));

          $chainId = 1;

          $signature = json_decode($this->signature);

          $response = EthereumToolsUtils::ecRecoverVRS($value, $signature->v, $signature->r, $signature->s, $chainId, $this->plainTextMessage);

          $this->response = $response;

          if($response['address'] === strtolower($this->address) && $response['publicKey'] === $publicKey && $response['isVeriscopeUser']) {
            return true;
          } else{
            return false;
          }


        } catch (\Exception $e) {


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
        return 'The signature hash or signature supplied does not appear to be a valid.';
    }
}
