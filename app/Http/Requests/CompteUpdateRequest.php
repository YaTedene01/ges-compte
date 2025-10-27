<?php

namespace App\Http\Requests;

use App\Rules\SenegalesePhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompteUpdateRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulaire' => 'sometimes|string|max:255',
            'informationsClient' => 'sometimes|array',
            'informationsClient.telephone' => [
                'sometimes',
                'string',
                new SenegalesePhoneNumber
            ],
            'informationsClient.email' => [
                'sometimes',
                'email'
            ],
            'informationsClient.password' => 'sometimes|string|min:8',
            'informationsClient.nci' => [
                'sometimes',
                'string',
                'max:13'
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'informationsClient.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'informationsClient.email.unique' => 'Cette adresse email est déjà utilisée.',
            'informationsClient.email.email' => 'L\'adresse email n\'est pas valide.',
            'informationsClient.password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'informationsClient.nci.unique' => 'Ce numéro NCI est déjà utilisé.',
            'informationsClient.nci.max' => 'Le numéro NCI ne peut pas dépasser 13 caractères.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            // Check if at least one field is provided
            $hasTitulaire = isset($data['titulaire']);
            $hasClientInfo = isset($data['informationsClient']) && is_array($data['informationsClient']) &&
                             (isset($data['informationsClient']['telephone']) ||
                              isset($data['informationsClient']['email']) ||
                              isset($data['informationsClient']['password']) ||
                              isset($data['informationsClient']['nci']));

            if (!$hasTitulaire && !$hasClientInfo) {
                $validator->errors()->add('general', 'Au moins un champ de modification doit être fourni.');
            }
        });
    }
}
