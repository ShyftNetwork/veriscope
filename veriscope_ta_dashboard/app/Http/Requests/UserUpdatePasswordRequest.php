<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdatePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return auth or jwt auth
        // return true if Auth:user()->isRole('Admin');
        // return true if jwt auth
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name'      => 'required|min:1|max:35',
            'last_name'       => 'required|min:1|max:35',
            // 'email'           => 'sometimes|unique:users',
            'password'        => 'sometimes|nullable|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|confirmed',
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
        ];
    }
}
