<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Protected account fields must never be changed from the profile.
            'phone' => ['prohibited'],
            'status' => ['prohibited'],
            'role' => ['prohibited'],
            'phone_verified_at' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.prohibited' => 'The phone number cannot be changed from the profile.',
            'status.prohibited' => 'The account status cannot be changed from the profile.',
            'role.prohibited' => 'The role cannot be changed from the profile.',
            'phone_verified_at.prohibited' => 'Phone verification cannot be changed from the profile.',
        ];
    }
}
