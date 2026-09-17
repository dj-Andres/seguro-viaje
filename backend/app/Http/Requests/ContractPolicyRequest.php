<?php

namespace App\Http\Requests;

use App\Models\Policy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ContractPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $policy = $this->route('policy');

            if ($policy instanceof Policy && $policy->estado === Policy::STATUS_CONTRACTED) {
                $validator->errors()->add('estado', 'La cotización ya se encuentra contratada.');
            }
        });
    }
}
