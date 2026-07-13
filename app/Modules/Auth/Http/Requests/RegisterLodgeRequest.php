<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterLodgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'address_zip_code' => preg_replace('/\D/', '', (string) $this->input('address_zip_code')),
            'address_state' => strtoupper((string) $this->input('address_state')),
            'admin_cim' => preg_replace('/\D/', '', (string) $this->input('admin_cim')),
            'admin_cpf' => preg_replace('/\D/', '', (string) $this->input('admin_cpf')),
            'admin_whatsapp' => preg_replace('/\D/', '', (string) $this->input('admin_whatsapp')),
        ]);
    }

    public function rules(): array
    {
        return [
            // Loja — passo 1
            'lodge_name' => ['required', 'string', 'max:180'],
            'lodge_slug' => ['nullable', 'string', 'max:180', 'alpha_dash', 'unique:lodges,slug'],
            'registration_number' => ['required', 'string', 'max:60'],
            'lodge_email' => ['required', 'email', 'max:180'],
            'lodge_phone' => ['nullable', 'string', 'max:30'],
            'potencia' => ['required', 'string', Rule::in(config('lodge.potencias'))],
            'rito' => ['required', 'string', Rule::in(config('lodge.ritos'))],
            'tipo' => ['required', 'string', Rule::in(config('lodge.tipos'))],
            'referral_source' => ['nullable', 'string', 'max:120'],

            // Endereço
            'address_zip_code' => ['required', 'digits:8'],
            'address_street' => ['required', 'string', 'max:180'],
            'address_number' => ['required', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:100'],
            'address_neighborhood' => ['required', 'string', 'max:120'],
            'address_city' => ['required', 'string', 'max:120'],
            'address_state' => ['required', 'string', 'size:2'],

            // Administrador — passo 2
            'admin_name' => ['required', 'string', 'max:180'],
            'admin_nickname' => ['nullable', 'string', 'max:60'],
            'admin_cim' => ['required', 'digits_between:1,8', 'unique:users,cim'],
            'admin_cpf' => ['required', 'digits:11', 'unique:users,cpf'],
            'admin_degree' => ['required', 'string', Rule::in(config('lodge.graus'))],
            'admin_email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'admin_whatsapp' => ['required', 'digits_between:10,11'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
