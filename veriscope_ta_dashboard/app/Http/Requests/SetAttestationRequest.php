<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Auth;
use App\Rules\CryptoAddress;

class SetAttestationRequest extends FormRequest
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
            'attestation_type'               => 'required|in:WALLET',
            'user_address'                   => ['required',new CryptoAddress('ETH')],
            'jurisdiction'                   => 'required|exists:countries,id',
            'effective_time'                 => 'numeric',
            'expiry_time'                    => 'numeric|gte:effective_time',
            'public_data'                    => 'required|in:WALLET',
            'documents_matrix_encrypted'     => 'required',
            'availability_address_encrypted' => 'required',
            'ta_address'                     => ['required', new CryptoAddress('ETH')]
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
        throw new HttpResponseException(response()->json(['message' => $validator->errors()->first()], 400));
    }

}
