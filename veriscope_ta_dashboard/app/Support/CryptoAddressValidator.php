<?php
namespace App\Support;

use \PsychoB\Ethereum\AddressValidator as EthereumAddressValidator;

class CryptoAddressValidator
{

  // Supported Assets
  static $assets =["ETH"];


  /**
   * Validate
   * @param  String  address (Crypto Address)
   * @param  String  type (Crypto Type)
   * @return Boolean
   */
  static function isValid($address, $type)
  {
     if (in_array($type, self::$assets , TRUE) ) {

       switch ($type) {
         default:
           return self::validateETH($address);
           break;
       }

     } else{
       return false;
     }
  }

  /**
   * Validate ETH address
   * @param  String  address (Crypto Address)
   * @return Boolean
   */
  static protected function validateETH($address)
  {
      return EthereumAddressValidator::isValid($address) === EthereumAddressValidator::ADDRESS_VALID;
  }

}
