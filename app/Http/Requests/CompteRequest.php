<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteRequest extends FormRequest
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
             'numeroCompte' => 'nullable|string|unique:comptes,numeroCompte',
             'titulaire' => 'required|string|max:255',
             'type' => 'required|in:epargne,cheque',
             'solde' => 'required|numeric|min:0',
             'devise' => 'required|string|max:10',
             'dateCreation' => 'required|date',
             'statut' => 'required|in:actif,bloque,ferme',
             'metadata' => 'nullable|array',
             'metadata.derniereModification' => 'nullable|date',
             'metadata.version' => 'nullable|integer|min:1',
             'client_id' => 'required|uuid|exists:clients,id',
         ];
     }
}
