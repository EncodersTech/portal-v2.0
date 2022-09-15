<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array The given data was invalid.
     */
    public function rules()
    {
        $id = $this->route('user')->id;
        $rules = User::ADMIN_UPDATE_USER_RULE;
        $rules['email'] = 'required|string|max:255|email|unique:users,email,'.$id;

        return $rules;
    }
}
