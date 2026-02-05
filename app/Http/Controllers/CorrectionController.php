<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Correction;

class CorrectionController extends Controller
{
    //
    public function store(Request $request)
    {

        $request->validate([
            'dossier_id' => 'required|exists:dossiers,id',
            'corrections' => 'required|array',
            'origine' => 'required|string',
            'message' => 'nullable|string',
        ]);
        $siteId = getIdSite();
        $userId = getConnectedUserId();

        $newCorrection = Correction::create([
            'dossier_id' => $request->dossier_id,
            'user_id' => $userId, // utilisateur connecté
            'site_id' => $siteId,
            'origine' => $request->origine,
            'corrections' => $request->corrections,
            'message' => $request->message ?? null,
            'status' => 1,
        ]);

        return response()->json([
            'message' => 'Correction enregistrée avec succès.',
            'correction' => [$newCorrection]
        ]);
    }

    /**
     * Voir une correction spécifique
     */
    public function show($id)
    {
        $correction = Correction::with(['dossier', 'user', 'site'])->findOrFail($id);

        return response()->json($correction);
    }

    /**
     * Supprimer une correction (optionnel)
     */
    public function destroy($id)
    {
        $correction = Correction::findOrFail($id);
        $correction->delete();

        return response()->json(['message' => 'Correction supprimée.']);
    }

    /**
     * Metrer à jour le statut d'une correction spécifique
     */
    public function update(Request $request, $id)
    {
        $correction = Correction::findOrFail($id);
        $correction->status = $request->status;
        $correction->save();

        return response()->json(['message' => 'Statut mis à jour.']);
    }
}
