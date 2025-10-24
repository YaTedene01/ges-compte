<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SenegalesePhoneNumber;
use App\Rules\SenegaleseNCI;

class CompteCreationRequest extends FormRequest
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
             'type' => 'required|in:cheque,epargne',
             'soldeInitial' => 'required|numeric|min:10000',
             'devise' => 'required|string',
             'client.id' => 'nullable|uuid',
             'client.titulaire' => 'required|string|max:255',
             'client.nci' => ['nullable', 'string', new SenegaleseNCI],
             'client.email' => 'required|email|unique:clients,email',
             'client.telephone' => ['required', 'string', new SenegalesePhoneNumber, 'unique:clients,telephone'],
             'client.adresse' => 'required|string',
         ];
     }

     /**
      * Get custom messages for validator errors.
      *
      * @return array<string, string>
      */
     public function messages(): array
     {
         return [
             'type.required' => 'Le type de compte est obligatoire.',
             'type.in' => 'Le type de compte doit être "cheque" ou "epargne".',
             'soldeInitial.required' => 'Le solde initial est obligatoire.',
             'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
             'soldeInitial.min' => 'Le solde initial doit être au moins 10000 FCFA.',
             'devise.required' => 'La devise est obligatoire.',
             'client.titulaire.required' => 'Le nom du titulaire est obligatoire.',
             'client.email.required' => 'L\'email est obligatoire.',
             'client.email.email' => 'L\'email doit être valide.',
             'client.email.unique' => 'Cet email est déjà utilisé.',
             'client.telephone.required' => 'Le téléphone est obligatoire.',
             'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
             'client.adresse.required' => 'L\'adresse est obligatoire.',
         ];
     }
}
