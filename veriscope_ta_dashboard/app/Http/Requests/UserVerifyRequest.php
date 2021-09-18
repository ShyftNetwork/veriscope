<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Auth;

class UserVerifyRequest extends FormRequest
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
            'first_name'      => 'required|min:1|max:255',
            'last_name'       => 'required|min:1|max:255',
            'address'         => 'required|min:3|max:255',
            'city'            => 'required|min:2|max:255',
            'country'         => 'required|min:3|max:255',
            'dob'             => 'required|integer',
            'gender'          => 'required',
            'occupation'      => 'required',
            'state'           => 'required',
            'telephone'       => 'required|min:7|max:20',
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
        if ($this->ajax() || $this->wantsJson())
        {
            throw new HttpResponseException(response()->json(['message' => $validator->errors()->first()], 403));
        }
    }

}
