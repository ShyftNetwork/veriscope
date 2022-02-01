<?php

namespace App\Traits;

use DateTimeImmutable;
use Laravel\Passport\Passport;
use Lcobucci\JWT\Configuration;
use League\OAuth2\Server\CryptKey;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Laravel\Passport\PersonalAccessTokenResult;

/**
 * Trait PassportTokenGenerate
 *
 * @package App\Traits
 */
trait PassportTokenGenerate
{


  public function getPersonalAccessTokenResult($client_id, $token_id, $user_id, $expires_at, $scopes = [])
  {
      $privateKey = new CryptKey(
          'file://' . Passport::keyPath('oauth-private.key'),
          null,
          false
      );

      $configuration = Configuration::forSymmetricSigner(
          new Sha256(),
          InMemory::file($privateKey->getKeyPath()),
      );

      $now = new DateTimeImmutable();
      $expiresAt = new DateTimeImmutable($expires_at->toDateTimeString());

      $token = $configuration->builder()
          ->permittedFor($client_id)
          ->issuedBy('self')
          ->identifiedBy($token_id)
          ->issuedAt($now)
          ->canOnlyBeUsedAfter($now)
          ->expiresAt($expiresAt)
          ->relatedTo($user_id)
          ->withClaim('scopes', $scopes)
          ->getToken($configuration->signer(), $configuration->signingKey());

      return new PersonalAccessTokenResult($token->toString(), $this);
  }


}
