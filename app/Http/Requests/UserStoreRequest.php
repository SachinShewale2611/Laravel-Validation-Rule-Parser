<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
            'age' => ['required', 'integer', 'min:18', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'avatar' => ['nullable', 'file', 'image', 'max:2048'],
            'role' => ['required', 'string', 'in:admin,user,moderator'],
            'birth_date' => ['required', 'date', 'before:today'],
            'terms' => ['required', 'boolean', 'accepted'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.unique' => 'This email is already taken.',
            'password.min' => 'Password must be at least 8 characters long.',
            'phone.regex' => 'Phone number must be 10 digits.',
        ];
    }
}
