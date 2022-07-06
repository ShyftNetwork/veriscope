<?php

namespace App\Support;
use App\Support\EthereumToolsUtils;


class ValidateData
{

    /**
     * Validate Public Key For Address
     *
     * @param string $pubkey
     * @param string $address
     *
     * @return string
     */
    public static function pubKeyMatchAddress($pubkey, $address)
    {

       try {

         if (EthereumToolsUtils::publicKeyToAddress($pubkey) === $address) {
           return true;
         }
         else {
           return false;
         }


       } catch (\Exception $e) {

         return false;
       }

    }



    /**
     * Validate Signature Hash with Signature and Address
     *
     * @param string $signature_hash
     * @param string $signature
     * @param string $address
     *
     * @return string
     */
    public static function signatureHashMatchSignatureAndAddress($signature_hash, $signature, $address)
    {

       try {

         $sig =  json_decode($signature);

         $result = EthereumToolsUtils::ecRecoverVRS($signature_hash, $sig['v'], $sig['r'], $sig['s']);


         if($result === $address){
           return true;
         } else {
           return false;
         }


       } catch (\Exception $e) {

         return false;
       }

    }


}
