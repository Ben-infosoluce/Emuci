<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class VehiculeController extends Controller
{
    // Attention : nom de la méthode corrigé
    public function getMarque()
    {
        $marques = DB::table('marque')->get(); // ou 'marques' selon ta base
        return response()->json($marques);
    }

    public function getModel(Request $request)
    {
        $marqueId = $request->query('marque_id');

        if (!$marqueId) {
            return response()->json(['error' => 'marque_id requis.'], 400);
        }

        $modeles = DB::table('model')
            ->where('marque_id', $marqueId)
            ->get();

        return response()->json($modeles);
    }
}
