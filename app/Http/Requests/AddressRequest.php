<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:50'], 'recipient' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'], 'street' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'], 'province' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:12'],
        ];
    }
}
