<?php

namespace App\Http\Requests\Auth;

use App\Rules\EthiopianPhoneRule;
use App\Support\EthiopianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => EthiopianPhone::normalize($this->input('phone')) ?? $this->input('phone'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', new EthiopianPhoneRule(), Rule::unique('users', 'phone')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
