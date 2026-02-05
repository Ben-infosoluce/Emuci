<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePdcReImmatriculationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ou false selon les permissions
    }

    public function rules(): array
    {
        $baseRules = [
            'DateNaissance' => 'required|string',
            'adresse' => 'required|string',
            'carrosserie' => 'required|string',
            'vin' => 'required|string|unique:vehicules,vin',
            'couleurVehicule' => 'required|string',
            'district' => 'nullable|string',
            'email' => 'nullable|email',
            'firstname' => 'required|string',
            'genre' => 'required|string',
            'lastname' => 'required|string',
            'marqueVehicule' => 'required|array',
            'modelVehicule' => 'required|array',
            'nombreEssieux' => 'required|string',
            'phone' => 'required|string',
            'placesAssises' => 'required|integer',
            'prefecture' => 'nullable|string',
            'sex' => 'nullable|string',
            'ptac' => 'required|integer',
            'pu' => 'required|integer',
            'puissance' => 'required|string',
            'pv' => 'required|string',
            'region' => 'nullable|string',
            'usage' => 'nullable|string',
            'sourcesEnergie' => 'required|string',
            'sousPrefecture' => 'nullable|string',
            'reserverNumero' => 'nullable|string',
            'numeroDiplomatique' => 'nullable|string',
            'type' => 'required|string',
            'typePersonne' => 'required|string',
            'typeTechnique' => 'required|string',
            'villeNaissance' => 'required|string',
            'anneeProduction' => 'required|string',
            'dateCirculation' => 'required|string',
        ];

        if ($this->input('type') != 'Physique') {
            $entrepriseRules = [
                'registreCommerce' => 'required|string',
                'nomEntreprise' => 'required|string',
                'representantLegal' => 'required|string',
                'numeroTelephone' => 'required|string',
                'compteContribuable' => 'required|string',
                'dateNaissanceRepresantant' => 'nullable|string',
                'professionRepresantant' => 'nullable|string',
            ];

            return array_merge($baseRules, $entrepriseRules);
        }

        return $baseRules;
    }

    public function messages(): array
    {
        return [
            'DateNaissance.required' => 'La date de naissance est obligatoire.',
            'adresse.required' => 'L\'adresse est requise.',
            'carrosserie.required' => 'La carrosserie est obligatoire.',
            'vin.required' => 'Le numéro VIN est requis.',
            'vin.unique' => 'Ce VIN existe déjà dans la base.',
            'couleurVehicule.required' => 'La couleur du véhicule est requise.',
            'firstname.required' => 'Le prénom du client est requis.',
            'lastname.required' => 'Le nom du client est requis.',
            'genre.required' => 'La civilité est requise.',
            'email.email' => 'L\'email n\'est pas valide.',
            'marqueVehicule.required' => 'La marque du véhicule est requise.',
            'modelVehicule.required' => 'Le modèle du véhicule est requis.',
            'placesAssises.required' => 'Le nombre de places assises est requis.',
            'placesAssises.integer' => 'Le nombre de places doit être un nombre entier.',
            'ptac.required' => 'Le poids total autorisé en charge est requis.',
            'pu.required' => 'Le poids utile est requis.',
            'pv.required' => 'Le poids à vide est requis.',
            'puissance.required' => 'La puissance administrative est requise.',
            'sourcesEnergie.required' => 'La source d\'énergie est requise.',
            'type.required' => 'Le type (Physique/Morale) est requis.',
            'typePersonne.required' => 'Le type de personne est requis.',
            'typeTechnique.required' => 'Le type technique est requis.',
            'villeNaissance.required' => 'La ville de naissance est requise.',
            'anneeProduction.required' => 'L\'année de production est requise.',
            'dateCirculation.required' => 'La date de mise en circulation est requise.',
            // Entreprise
            'registreCommerce.required' => 'Le registre de commerce est requis.',
            'nomEntreprise.required' => 'Le nom de l\'entreprise est requis.',
            'representantLegal.required' => 'Le représentant légal est requis.',
            'numeroTelephone.required' => 'Le numéro de téléphone du représentant est requis.',
            'compteContribuable.required' => 'Le compte contribuable est requis.',
        ];
    }
}
