<?php

namespace App\Transformers;
use App\KycTemplate;
use League\Fractal\TransformerAbstract;

class KycTemplateTransformer extends TransformerAbstract
{



  /**
   * @param KycTemplate $kt
   *
   * @return array
   */
  public function transform(KycTemplate $kt)
  {
      return [
        'AttestationHash' => $kt->attestation_hash,
        'BeneficiaryTAAddress' => $kt->beneficiary_ta_address,
        'BeneficiaryTAPublicKey' => $kt->beneficiary_ta_public_key,
        'BeneficiaryUserAddress' => strtolower($kt->beneficiary_user_address),
        'BeneficiaryUserPublicKey' => $kt->beneficiary_user_public_key,
        'BeneficiaryTASignatureHash' => $kt->beneficiary_ta_signature_hash,
        'BeneficiaryTASignature' => json_decode($kt->beneficiary_ta_signature),
        'BeneficiaryUserSignatureHash' => $kt->beneficiary_user_signature_hash,
        'BeneficiaryUserSignature' => json_decode($kt->beneficiary_user_signature),
        'BeneficiaryUserAddressCryptoProof' => json_decode($kt->beneficiary_user_address_crypto_proof),
        'BeneficiaryUserAddressCryptoProofStatus' => $kt->beneficiary_user_address_crypto_proof_status,
        'CoinBlockchain' => $kt->coin_blockchain,
        'CoinToken' => $kt->coin_token,
        'CoinAddress' => $kt->coin_address,
        'CoinMemo' => $kt->coin_memo,
        'CoinTransactionHash' => $kt->coin_transaction_hash,
        'CoinTransactionValue' => $kt->coin_transaction_value,
        'SenderTAAddress' => strtolower($kt->sender_ta_address),
        'SenderTAPublicKey' => $kt->sender_ta_public_key,
        'SenderUserAddress' => strtolower($kt->sender_user_address),
        'SenderUserPublicKey' => $kt->sender_user_public_key,
        'SenderTASignatureHash' => $kt->sender_ta_signature_hash,
        'SenderTASignature' => json_decode($kt->sender_ta_signature),
        'SenderUserSignatureHash' => $kt->sender_user_signature_hash,
        'SenderUserSignature' => json_decode($kt->sender_user_signature),
        'BeneficiaryKYC' => $kt->beneficiary_kyc,
        'SenderKYC' => $kt->sender_kyc,
        'BeneficiaryTAUrl' => $kt->beneficiary_ta_url,
        'SenderTAUrl' => $kt->sender_ta_url
      ];
  }

}
