<?php

namespace App\Http\Requests;

class PdcImmatriculationMessages
{
    public static function messages($typePersonne = null)
    {
        $messages = [
            'DateNaissance.required' => 'La date de naissance est obligatoire.',
            'DateNaissance.string' => 'La date de naissance doit être une chaîne de caractères.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'carrosserie.required' => 'La carrosserie est obligatoire.',
            'carrosserie.string' => 'La carrosserie doit être une chaîne de caractères.',
            'vin.required' => 'Le numéro VIN est obligatoire.',
            'vin.string' => 'Le numéro VIN doit être une chaîne de caractères.',
            'couleurVehicule.required' => 'La couleur du véhicule est obligatoire.',
            'couleurVehicule.string' => 'La couleur du véhicule doit être une chaîne de caractères.',
            'firstname.required' => 'Le prénom est obligatoire.',
            'firstname.string' => 'Le prénom doit être une chaîne de caractères.',
            'lastname.required' => 'Le nom est obligatoire.',
            'lastname.string' => 'Le nom doit être une chaîne de caractères.',
            'genre.required' => 'Le genre est obligatoire.',
            'genre.string' => 'Le genre doit être une chaîne de caractères.',
            'marqueVehicule.required' => 'La marque du véhicule est obligatoire.',
            'marqueVehicule.array' => 'La marque du véhicule doit être une structure valide.',
            'modelVehicule.required' => 'Le modèle du véhicule est obligatoire.',
            'modelVehicule.array' => 'Le modèle du véhicule doit être une structure valide.',
            'nombreEssieux.required' => 'Le nombre d’essieux est obligatoire.',
            'nombreEssieux.string' => 'Le nombre d’essieux doit être une chaîne de caractères.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'placesAssises.required' => 'Le nombre de places assises est obligatoire.',
            'placesAssises.integer' => 'Le nombre de places assises doit être un nombre entier.',
            'ptac.required' => 'Le poids total autorisé en charge (PTAC) est obligatoire.',
            'ptac.integer' => 'Le PTAC doit être un nombre entier.',
            'pu.required' => 'Le poids utile est obligatoire.',
            'pu.integer' => 'Le poids utile doit être un nombre entier.',
            'puissance.required' => 'La puissance administrative est obligatoire.',
            'puissance.string' => 'La puissance doit être une chaîne de caractères.',
            'pv.required' => 'Le poids à vide est obligatoire.',
            'pv.string' => 'Le poids à vide doit être une chaîne de caractères.',
            'sourcesEnergie.required' => 'La source d’énergie est obligatoire.',
            'sourcesEnergie.string' => 'La source d’énergie doit être une chaîne de caractères.',
            'sousPrefecture.string' => 'La sous-préfecture doit être une chaîne de caractères.',
            'prefecture.string' => 'La préfecture doit être une chaîne de caractères.',
            'type.required' => 'Le type est obligatoire.',
            'type.string' => 'Le type doit être une chaîne de caractères.',
            'typePersonne.required' => 'Le type de personne est obligatoire.',
            'typePersonne.string' => 'Le type de personne doit être une chaîne de caractères.',
            'typePersonne.in' => 'Le type de personne doit être "Morale" ou "Physique".',
            'typeTechnique.required' => 'Le type technique est obligatoire.',
            'typeTechnique.string' => 'Le type technique doit être une chaîne de caractères.',
            'villeNaissance.required' => 'La ville de naissance est obligatoire.',
            'villeNaissance.string' => 'La ville de naissance doit être une chaîne de caractères.',
            'anneeProduction.required' => 'L\'année de production est obligatoire.',
            'anneeProduction.string' => 'L\'année de production doit être une chaîne de caractères.',
            'dateCirculation.required' => 'La date de première mise en circulation est obligatoire.',
            'dateCirculation.string' => 'La date de circulation doit être une chaîne de caractères.',
        ];

        if ($typePersonne != 'Physique') {
            $messages = array_merge($messages, [
                'registreCommerce.required' => 'Le registre de commerce est obligatoire.',
                'registreCommerce.string' => 'Le registre de commerce doit être une chaîne de caractères.',
                'registreCommerce.unique' => 'Ce registre de commerce est déjà utilisé.',
                'nomEntreprise.required' => 'Le nom de l\'entreprise est obligatoire.',
                'nomEntreprise.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
                'representantLegal.required' => 'Le nom du représentant légal est obligatoire.',
                'representantLegal.string' => 'Le nom du représentant légal doit être une chaîne de caractères.',
                'numeroTelephone.required' => 'Le numéro de téléphone du représentant est obligatoire.',
                'numeroTelephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
                'compteContribuable.required' => 'Le numéro de compte contribuable est obligatoire.',
                'compteContribuable.string' => 'Le compte contribuable doit être une chaîne de caractères.',
            ]);
        }

        return $messages;
    }

    public static function updatePostImmatriculationMessages($typePersonne = null)
    {
        $messages = [
            'DateNaissance.required' => 'La date de naissance est obligatoire.',
            'DateNaissance.string' => 'La date de naissance doit être une chaîne de caractères.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'carrosserie.required' => 'La carrosserie est obligatoire.',
            'carrosserie.string' => 'La carrosserie doit être une chaîne de caractères.',
            'vin.required' => 'Le numéro VIN est obligatoire.',
            'vin.string' => 'Le numéro VIN doit être une chaîne de caractères.',
            'couleurVehicule.required' => 'La couleur du véhicule est obligatoire.',
            'couleurVehicule.string' => 'La couleur du véhicule doit être une chaîne de caractères.',
            'firstname.required' => 'Le prénom est obligatoire.',
            'firstname.string' => 'Le prénom doit être une chaîne de caractères.',
            'lastname.required' => 'Le nom est obligatoire.',
            'lastname.string' => 'Le nom doit être une chaîne de caractères.',
            'genre.required' => 'Le genre est obligatoire.',
            'genre.string' => 'Le genre doit être une chaîne de caractères.',
            'marqueVehicule.required' => 'La marque du véhicule est obligatoire.',
            'marqueVehicule.array' => 'La marque du véhicule doit être une structure valide.',
            'modelVehicule.required' => 'Le modèle du véhicule est obligatoire.',
            'modelVehicule.array' => 'Le modèle du véhicule doit être une structure valide.',
            'nombreEssieux.required' => 'Le nombre d’essieux est obligatoire.',
            'nombreEssieux.string' => 'Le nombre d’essieux doit être une chaîne de caractères.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'placesAssises.required' => 'Le nombre de places assises est obligatoire.',
            'placesAssises.integer' => 'Le nombre de places assises doit être un nombre entier.',
            'ptac.required' => 'Le poids total autorisé en charge (PTAC) est obligatoire.',
            'ptac.integer' => 'Le PTAC doit être un nombre entier.',
            'pu.required' => 'Le poids utile est obligatoire.',
            'pu.integer' => 'Le poids utile doit être un nombre entier.',
            'puissance.required' => 'La puissance administrative est obligatoire.',
            'puissance.string' => 'La puissance doit être une chaîne de caractères.',
            'pv.required' => 'Le poids à vide est obligatoire.',
            'pv.string' => 'Le poids à vide doit être une chaîne de caractères.',
            'sourcesEnergie.required' => 'La source d’énergie est obligatoire.',
            'sourcesEnergie.string' => 'La source d’énergie doit être une chaîne de caractères.',
            'type.required' => 'Le type est obligatoire.',
            'type.string' => 'Le type doit être une chaîne de caractères.',
            'typePersonne.required' => 'Le type de personne est obligatoire.',
            'typePersonne.string' => 'Le type de personne doit être une chaîne de caractères.',
            'typePersonne.in' => 'Le type de personne doit être "Morale" ou "Physique".',
            'typeTechnique.required' => 'Le type technique est obligatoire.',
            'typeTechnique.string' => 'Le type technique doit être une chaîne de caractères.',
            'villeNaissance.required' => 'La ville de naissance est obligatoire.',
            'villeNaissance.string' => 'La ville de naissance doit être une chaîne de caractères.',
            'anneeProduction.required' => 'L\'année de production est obligatoire.',
            'anneeProduction.string' => 'L\'année de production doit être une chaîne de caractères.',
            'dateCirculation.required' => 'La date de première mise en circulation est obligatoire.',
            'dateCirculation.string' => 'La date de circulation doit être une chaîne de caractères.',
            'codeRegion.required' => 'Le code de la région est obligatoire.',
            'codeRegion.string' => 'Le code de la région doit être une chaîne de caractères.',
        ];

        if ($typePersonne != 'Physique') {
            $messages = array_merge($messages, [
                'nomEntreprise.required' => 'Le nom de l\'entreprise est obligatoire.',
                'nomEntreprise.string' => 'Le nom de l\'entreprise doit être une chaîne de caractères.',
                'registreCommerce.required' => 'Le registre de commerce est obligatoire.',
                'registreCommerce.string' => 'Le registre de commerce doit être une chaîne de caractères.',
                'registreCommerce.unique' => 'Ce registre de commerce est déjà utilisé.',
                'representantLegal.required' => 'Le nom du représentant légal est obligatoire.',
                'representantLegal.string' => 'Le nom du représentant légal doit être une chaîne de caractères.',
                'numeroTelephone.required' => 'Le numéro de téléphone du représentant est obligatoire.',
                'numeroTelephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
                'compteContribuable.required' => 'Le numéro de compte contribuable est obligatoire.',
                'compteContribuable.string' => 'Le compte contribuable doit être une chaîne de caractères.',
            ]);
        }

        return $messages;
    }
}
