<?php

namespace App\Http\Requests\Auth;

use App\Rules\EthiopianPhoneRule;
use App\Support\EthiopianPhone;
use Illuminate\Foundation\Http\FormRequest;

class RequestPasswordResetRequest extends FormRequest
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
        // Deliberately no "exists" rule: the response is identical whether or
        // not the number is registered (account-enumeration protection).
        return [
            'phone' => ['required', 'string', new EthiopianPhoneRule()],
        ];
    }
}
