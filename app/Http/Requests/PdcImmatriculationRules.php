<?php

namespace App\Http\Requests;

class PdcImmatriculationRules
{
    public static function rules($typePersonne = null)
    {
        $rules = [
            'DateNaissance' => 'required|string',
            'adresse' => 'required|string',
            'carrosserie' => 'required|string',
            'vin' => 'required|string',
            'couleurVehicule' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'genre' => 'required|string',
            'marqueVehicule' => 'required|array',
            'modelVehicule' => 'required|array',
            'nombreEssieux' => 'required|string',
            'phone' => 'required|string',
            'placesAssises' => 'required|integer',
            'ptac' => 'required|integer',
            'pu' => 'required|integer',
            'puissance' => 'required|string',
            'pv' => 'required|string',
            'sourcesEnergie' => 'required|string',
            'sousPrefecture' => 'nullable|string',
            'prefecture' => 'nullable|string',
            'type' => 'required|string',
            'typePersonne' => 'required|string',
            'typeTechnique' => 'required|string',
            'villeNaissance' => 'required|string',
            'anneeProduction' => 'required|string',
            'dateCirculation' => 'required|string',
            'codeRegion' => 'required|string',
        ];

        if ($typePersonne != 'Physique') {
            $rules = array_merge($rules, [
                'registreCommerce' => 'required|string|unique:entreprises,registre_commerce',
                'nomEntreprise' => 'required|string',
                'representantLegal' => 'required|string',
                'numeroTelephone' => 'required|string',
                'compteContribuable' => 'required|string',
            ]);
        }

        return $rules;
    }


    public static function updatePostImmatriculationRules($typePersonne = null)
    {
        $rules = [
            'DateNaissance' => 'required|string',
            'adresse' => 'required|string',
            'carrosserie' => 'required|string',
            'vin' => 'required|string',
            'couleurVehicule' => 'required|string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'genre' => 'required|string',
            'marqueVehicule' => 'required|array',
            'modelVehicule' => 'required|array',
            'nombreEssieux' => 'required|string',
            'phone' => 'required|string',
            'placesAssises' => 'required|integer',
            'ptac' => 'required|integer',
            'pu' => 'required|integer',
            'puissance' => 'required|string',
            'pv' => 'required|string',
            'sourcesEnergie' => 'required|string',
            'sousPrefecture' => 'nullable|string',
            'prefecture' => 'nullable|string',
            'type' => 'required|string',
            'typePersonne' => 'required|string',
            'typeTechnique' => 'required|string',
            'villeNaissance' => 'required|string',
            'anneeProduction' => 'required|string',
            'dateCirculation' => 'required|string',
            'codeRegion' => 'required|string',
        ];

        if ($typePersonne != 'Physique') {
            $rules = array_merge($rules, [
                'nomEntreprise' => 'required|string',
                // 'registreCommerce' => 'required|string|unique:entreprises,registre_commerce',
                'representantLegal' => 'required|string',
                'numeroTelephone' => 'required|string',
                'compteContribuable' => 'required|string',
            ]);
        }

        return $rules;
    }
}
