<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TypeServiceController extends Controller
{
    public function getTypeServicesById(Request $request)
    {
        // Récupérer les id_service depuis les paramètres de la requête
        $id_services = $request->input('id_services');

        // Vérifier si le paramètre est présent
        if (!$id_services) {
            return response()->json([
                'error' => 'Le paramètre "id_services" est requis.'
            ], 400);
        }

        // Convertir les id_service en tableau
        $id_services = explode(',', $id_services);

        // Récupérer les noms des services correspondants
        $typeServices = DB::table('type_services')
            ->whereIn('id_service', $id_services)
            ->select('nom_type_service')
            ->get();

        // Retourner les résultats en JSON
        return response()->json($typeServices);
    }
}
