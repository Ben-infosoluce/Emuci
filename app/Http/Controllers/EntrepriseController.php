<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;

class EntrepriseController extends Controller
{
    //
    public function getData($id)
    {
        // 🔍 Recherche de l'entreprise
        $entreprise = Entreprise::find($id);

        // 🚨 Si non trouvée -> erreur 404
        if (!$entreprise) {
            return response()->json([
                'success' => false,
                'message' => 'Entreprise non trouvée'
            ], 404);
        }

        // ✅ Retour des données en JSON
        return response()->json([
            'success' => true,
            'data' => $entreprise
        ]);
    }
}
