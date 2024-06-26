<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Auth;
use App\Rules\{CryptoAddress, CryptoPublicKey, CryptoSignature, CryptoSignatureHash};


class CreateKycTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attestation_hash'      => 'required|exists:App\SmartContractAttestation,attestation_hash',
            'ta_account'            => ['required','exists:App\TrustAnchor,account_address', new CryptoAddress('ETH')],
            'user_account'          => ['required', new CryptoAddress('ETH')],
            'user_public_key'       => ['required', new CryptoPublicKey($this->get('user_account'))],
            'user_signature'        => 'required',
            'user_signature_hash'   => ['required', new CryptoSignatureHash($this->get('user_account'), $this->get('user_public_key'), $this->get('user_signature'))]
        ];
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['error' => $validator->errors() ], 400));
    }

}
