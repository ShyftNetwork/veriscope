<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\User;

class UserCreatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow users where email matches token
        return User::where(
            [
              ['remember_token', $this->get('token')],
              ['email', $this->get('email')]
            ]
          )->count();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|confirmed',
            'legal_agree' => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'password.regex' => 'Password must contain one lowercase letter, one uppercase letter, and one number.',
            'legal_agree.required' => 'You must agree to the terms of use and privacy policy.',
        ];
    }
}
