<?php

/**
 * This file is part of the PHPEthereumTools package
 *
 * Based on https://github.com/web3p/ethereum-util/blob/master/src/Util.php
 * Based on https://medium.com/coinmonks/generate-ethereum-wallet-key-pairs-using-php-ethereum-tutorial-c1cc75f0d64f
 *
 * PHP Version 7.1
 *
 * @category PHPEthereumTools
 * @package  PHPEthereumTools
 * @author   Micky Socaci <micky@nowlive.ro>
 * @license  https://github.com/mickys/php-eth-tools/blob/master/LICENSE.md MIT
 * @link     https://github.com/mickys/php-eth-tools/
 */

namespace App\Support;

use Sop\CryptoTypes\Asymmetric\EC\ECPublicKey;
use Sop\CryptoTypes\Asymmetric\EC\ECPrivateKey;
use Sop\CryptoEncoding\PEM;
use kornrunner\Keccak;

use InvalidArgumentException;
use RuntimeException;
use Elliptic\EC;
use Elliptic\EC\KeyPair;
use Elliptic\EC\Signature;

use Ethereum\EcRecover;

/**
 * This file is part of the PHPEthereumTools package
 *
 * PHP Version 7.1
 *
 * @category PHPEthereumTools
 * @package  PHPEthereumTools
 * @author   Micky Socaci <micky@nowlive.ro>
 * @license  https://github.com/mickys/php-eth-tools/blob/master/LICENSE.md MIT
 * @link     https://github.com/mickys/php-eth-tools/
 */
class EthereumToolsUtils
{
    /**
     * Generate a new Private / Public key pair
     *
     * @return string
     */
    public static function generateNewPrivateKey()
    {

        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'secp256k1'
        ];

        $res = openssl_pkey_new($config);
        if (!$res) {
            throw new RuntimeException(
                'ERROR: Failed to generate private key. -> ' . openssl_error_string()
            );
        }

        // Generate Private Key
        openssl_pkey_export($res, $priv_key);

        // Get The Public Key
        $key_detail = openssl_pkey_get_details($res);
        $pub_key = $key_detail["key"];
        $priv_pem = PEM::fromString($priv_key);

        // Convert to Elliptic Curve Private Key Format
        $ec_priv_key = ECPrivateKey::fromPEM($priv_pem);

        // Then convert it to ASN1 Structure
        $ec_priv_seq = $ec_priv_key->toASN1();

        // Private Key & Public Key in HEX
        $priv_key_hex = bin2hex($ec_priv_seq->at(1)->asOctetString()->string());

        return $priv_key_hex;
    }

    /**
     * Generate the Address of the provided Public key
     *
     * @param string $publicKey
     *
     * @return string
     */
    public static function publicKeyToAddress(string $publicKey)
    {
        if (self::isHex($publicKey) === false) {
            throw new InvalidArgumentException('Invalid public key format.');
        }

        // Strip Zero
        $publicKey = self::stripZero($publicKey);


        if (strlen($publicKey) !== 130) {
            throw new InvalidArgumentException('Invalid public key length.');
        }
        return '0x' . mb_substr(self::sha3(mb_substr(hex2bin($publicKey), 1)), 24);
    }

    /**
     * Generate the Address of the provided Private key
     *
     * @param string $privateKey
     *
     * @return string
     */
    public static function privateKeyToAddress(string $privateKey)
    {
        return self::publicKeyToAddress(
            self::privateKeyToPublicKey($privateKey)
        );
    }

    /**
     * Generate the Public key for provided Private key
     *
     * @param string $privateKey Private Key
     *
     * @return string
     */
    public static function privateKeyToPublicKey( string $privateKey )
    {
        if (self::isHex($privateKey) === false) {
            throw new InvalidArgumentException('Invalid private key format.');
        }
        $privateKey = self::stripZero($privateKey);

        if (strlen($privateKey) !== 64) {
            throw new InvalidArgumentException('Invalid private key length.');
        }

        $secp256k1 = new EC('secp256k1');
        $privateKey = $secp256k1->keyFromPrivate($privateKey, 'hex');
        $publicKey = $privateKey->getPublic(false, 'hex');

        return '0x' . $publicKey;
    }

    /**
     * Personal Sign
     *
     * @param string $privateKey
     * @param string $message
     * @param int $chainId
     *
     * @return string
     */
    public static function personalSign(string $privateKey, string $message, int $chainId)
    {

        $hash    = self::hashPersonalMessage($message);

        $message = self::hashPersonalMessage($message);

        if (self::isHex($privateKey) === false) {
            throw new InvalidArgumentException('Invalid private key format.');
        }

        $privateKeyLength = strlen(self::stripZero($privateKey));

        if ($privateKeyLength % 2 !== 0 && $privateKeyLength !== 64) {
            throw new InvalidArgumentException('Private key length was wrong.');
        }

        $secp256k1 = new EC('secp256k1');
        $privateKey = $secp256k1->keyFromPrivate($privateKey, 'hex');
        $signature = $privateKey->sign(
            $message, [ 'canonical' => true ]
        );

        $signature->recoveryParam += (int) ($chainId ? ($chainId * 2 + 35) : 27);

        $sig = $signature->r->toString(16).$signature->s->toString(16);

        $message = "0x".$sig.dechex($signature->recoveryParam);


        return [
          "hash" => "0x".$hash,
          "sig" => "0x".$sig.dechex($signature->recoveryParam),
          'r' => '0x'.mb_substr(mb_substr($message, 2),0, 64),
          's' => '0x'.substr(mb_substr(mb_substr($message, 2),64, 128),0,-2),
          'v' => '0x'.mb_substr(mb_substr($message, 2),128, 130),
        ];
    }

    /**
     * Personal ecRecover
     *
     * @param string $msg    No header, plain text message
     * @param string $signed Hex encoded signiture string
     *
     * @return string
     */
    public static function personalEcRecover($msg, $signed)
    {
        $message = self::hashPersonalMessage($msg);
        return self::ecRecover($message, $signed);
    }

    /**
     * Recover Public Key
     *
     * @param string $hex
     * @param string $signed
     *
     * @return string
     */
    public static function ecRecover($hex, $signed)
    {
        return EcRecover::phpEcRecover($hex, $signed);
    }

    /**
     * Recover Public Key
     *
     * @param string $message_hash
     * @param string $v
     * @param string $r
     * @param string $s
     * @param int $chainId
     * @param string $plainTextMessage
     * @return array
     */
    public static function ecRecoverVRS(string $message_hash, string $v, string $r, string $s, int $chainId, string $plainTextMessage = "VERISCOPE_USER")
    {

        $message = mb_substr($message_hash,2);
        $isVeriscopeUser  = (self::hashPersonalMessage($plainTextMessage) === $message) ? true : false;
        try {
          $ec = new EC('secp256k1');
          $sign   = ["r" => mb_substr($r, 2), "s" => mb_substr($s, 2)];
          $recid  = ord(hex2bin(mb_substr($v, 2))) - ($chainId ? ($chainId * 2 + 35) : 27);
          $pubKey = $ec->recoverPubKey($message, $sign, $recid)->encode("hex");
          return [ "address" => self::publicKeyToAddress($pubKey), "publicKey" => mb_substr($pubKey,2), "isVeriscopeUser" => $isVeriscopeUser ];
        } catch (\Throwable $e) {
          return [ "address" => false, "publicKey" => false, "isVeriscopeUser" => $isVeriscopeUser ];
        }
    }



    /**
     * Hash Personal Message
     *
     * @param string $message
     *
     * @return string
     */
    public static function hashPersonalMessage(string $message)
    {
        $prefix = sprintf("\x19Ethereum Signed Message:\n%d", mb_strlen($message));
        return self::sha3($prefix . $message);
    }

    /**
     * Get sha3
     * keccak256
     *
     * @param string $value
     *
     * @return string
     */
    public static function sha3(string $value)
    {
        $hash = Keccak::hash($value, 256);
        // null sha
        $null = 'c5d2460186f7233c927e7db2dcc703c0e500b653ca82273b7bfad8045d85a470';
        if ($hash === $null) {
            return null;
        }
        return $hash;
    }

    /**
     * Is value Zero Prefixed
     *
     * @param string $value
     *
     * @return bool
     */
    public static function isZeroPrefixed(string $value)
    {
        return (strpos($value, '0x') === 0);
    }

    /**
     * Is value uncompresed
     *
     * @param string $value
     *
     * @return bool
     */
    public static function makeUncompressed(string $value)
    {

        $prefix  = (strpos($value, '0x04') === 0) ? "" : "0x04";

        return $prefix.$value;
    }

    /**
     * Strip Zero X
     *
     * @param string $value
     *
     * @return string
     */
    public static function stripZero(string $value)
    {
        if (self::isZeroPrefixed($value)) {
            $count = 1;
            return str_replace('0x', '', $value, $count);
        }
        return $value;
    }


    /**
     * Strip Uncompressed
     *
     * @param string $value
     *
     * @return string
     */
    public static function stripUncompressed(string $value)
    {
        $newValue = mb_substr($value, 0, 2);
        if ($newValue  === "04") {
          return mb_substr($value, 2);
        } else {
          return $value;
        }
    }
    /**
     * Check if value isHex
     *
     * @param string $value
     *
     * @return bool
     */
    public static function isHex(string $value)
    {
        return (
            is_string($value)
            && preg_match(
                '/^(0x)?[a-fA-F0-9]+$/', $value
            ) === 1
        );
    }


    /**
     * Concat KDF function
     *
     * @param string $x value of x in hex
     * @param int $encryption_key_size
     *
     * @return string
     */
    public static function concatKDF($x, $encryption_key_size)
    {
        $encryption_segments = [
            self::toInt32Bits(1),
            self::hexToStr($x)
        ];

        $input = implode('', $encryption_segments);
        $hash = hash('sha256', $input);

        return $hash;
    }

    /**
     * Convert an integer into a 32 bits string.
     *
     * @param int $value Integer to convert
     *
     * @return string
     */
    public static function toInt32Bits($value)
    {
        return hex2bin(str_pad(dechex($value), 8, '0', STR_PAD_LEFT));
    }

    /**
     * Convert an hexadecimal into string.
     *
     * @param string $value hex to convert
     *
     * @return string
     */
    public static function hexToStr($value)
    {
        $string='';
        for ($i=0; $i < strlen($value)-1; $i+=2)
        {
            if ( $value[$i] == ' ') continue;
            $string .= chr(hexdec($value[$i].$value[$i+1]));
        }
        return $string;
    }

    /**
     * Encrypt Data
     *
     * @param string $publicKey   eth public key (append 04 at the start if not included)
     * @param string $data       string to encrypt
     * @param string $sharedMacData
     *
     * @return string
     */
    public static function encryptData($publicKey, $data, $sharedMacData = false){
      $ec = new EC('secp256k1');
      $cipher = "aes-128-ctr";
      $publicKey = self::stripZero($publicKey);


      $bufferData = base64_encode($data);
      $privateKey = $ec->genKeyPair()->getPrivate();
      $key1 = $ec->keyFromPublic($publicKey, 'hex');
      $key2 = $ec->keyFromPrivate($privateKey);

      $x = $key2->derive($key1->getPublic())->toString(16);
      $key = self::concatKDF($x,32);
      $ekey = mb_substr($key, 0, 32);
      $mkey = hash("sha256",self::hexToStr(mb_substr($key, 32, 64)));



      // encrypt
      $ivlen = openssl_cipher_iv_length($cipher);
      $iv = bin2hex(openssl_random_pseudo_bytes($ivlen));
      $encryptedData = openssl_encrypt($data, $cipher, self::hexToStr($ekey), $options=0, self::hexToStr($iv));
      $dataIV = $iv.bin2hex(base64_decode($encryptedData));
      if($sharedMacData){
        $sharedMacData = implode("", $sharedMacData);
      }
      $tag = hash_hmac('sha256',self::hexToStr($dataIV.$sharedMacData), self::hexToStr($mkey));
      $result = $key2->getPublic(false,"hex").$dataIV.$tag;

      return base64_encode(pack('H*',$result));
    }

    /**
     * Decrypt Data
     *
     * @param string $privateKey  eth private key
     * @param string $data       string to decrypt
     * @param string $sharedMacData
     *
     * @return string
     */
    public static function decryptData($privateKey, $data, $sharedMacData = false){
      $ec = new EC('secp256k1');
      $cipher = "aes-128-ctr";

      $unpackedData = implode("",unpack("H*",base64_decode($data)));
      $publicKey = mb_substr($unpackedData,0, 130);
      $dataIV = mb_substr($unpackedData, 130, -64);
      $tag = mb_substr($unpackedData,-64);

      $key1 = $ec->keyFromPublic($publicKey, 'hex');
      $key2 = $ec->keyFromPrivate($privateKey);
      $x = $key2->derive($key1->getPublic())->toString(16);

      $key = self::concatKDF($x,32);
      $ekey = mb_substr($key, 0, 32);
      $mkey = hash("sha256",self::hexToStr(mb_substr($key, 32, 64)));
      if($sharedMacData){
        $sharedMacData = implode("", $sharedMacData);
      }
      $tag = hash_hmac('sha256',self::hexToStr($dataIV.$sharedMacData), self::hexToStr($mkey));

      // decrypt data
      $iv = mb_substr($dataIV,0, 32);
      $encryptedData =  mb_substr($dataIV,32);
      $result = openssl_decrypt(self::hexToStr($encryptedData), $cipher, self::hexToStr($ekey), $options=OPENSSL_RAW_DATA, self::hexToStr($iv));


      if(openssl_error_string() !== false && openssl_error_string() !== "error:0909006C:PEM routines:get_name:no start line"){
        throw new \Exception("Could not Decrypt data", 1);
      }

      return $result;
    }
}
