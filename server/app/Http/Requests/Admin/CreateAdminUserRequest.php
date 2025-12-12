<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9][a-z0-9-]*\.[a-z0-9][a-z0-9-]*@imt-atlantique\.net$/i',
            ],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
        ];
    }
}
