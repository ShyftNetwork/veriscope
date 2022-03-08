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
        $publicKey = self::stripZero($publicKey);
        if (strlen($publicKey) !== 130) {
            throw new InvalidArgumentException('Invalid public key length.');
        }
        return '0x' . substr(self::sha3(substr(hex2bin($publicKey), 1)), 24);
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
          'r' => '0x'.mb_substr(substr($message, 2),0, 64),
          's' => '0x'.substr(mb_substr(substr($message, 2),64, 128),0,-2),
          'v' => '0x'.mb_substr(substr($message, 2),128, 130),
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


}
