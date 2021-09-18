<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            // 'address'         => 'required|min:3|max:255',
            // 'city'            => 'required|min:2|max:255',
            // 'country'         => 'required|min:3|max:255',
            // 'dob'             => 'required|date',
            // 'gender'          => 'required',
            // 'occupation'      => 'required',
            // 'state'           => 'required',
            // 'telephone'       => 'required|min:7|max:20',
        ];
    }

}
