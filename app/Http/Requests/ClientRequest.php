<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
             'numeroCompte' => 'required|string|unique:clients,numeroCompte',
             'titulaire' => 'required|string|max:255',
             'type' => 'required|string|in:epargne,courant',
             'solde' => 'required|numeric|min:0',
             'devise' => 'required|string|max:10',
             'dateCreation' => 'required|date',
             'statut' => 'required|string|in:actif,bloque,suspendu',
         ];
     }
}
