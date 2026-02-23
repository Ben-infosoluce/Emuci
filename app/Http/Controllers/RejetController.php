<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use Illuminate\Http\Request;
use App\Models\Rejet;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RejetController extends Controller
{
    /**
     * Retourner tous les rejets (GET /api/rejets)
     */
    public function index(Request $request)
    {
        $idService = $request->query('id_service');

        $query = Rejet::query();

        if ($idService) {
            $query->where('service_id', $idService);
        }

        return response()->json($query->get());
    }

    /**
     * Enregistrer la sélection de rejets (POST /api/save-selection)
     */
    public function store(Request $request)
    {
        // Valider les données
        $validated = $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'integer|exists:rejets,id',
            'id' => 'required|integer|exists:dossiers,id', // Vérifie que l'ID du dossier existe
        ], [
            'selected.required' => 'Veuillez sélectionner au moins un motif de rejet.',
            'selected.array' => 'Le format des motifs sélectionnés est invalide.',
            'selected.*.integer' => 'Chaque motif sélectionné doit être un entier valide.',
            'selected.*.exists' => 'Un ou plusieurs motifs sélectionnés sont invalides.',
            'id.required' => 'L\'ID du dossier est requis.',
            'id.integer' => 'L\'ID du dossier doit être un entier valide.',
            'id.exists' => 'Le dossier spécifié n\'existe pas.',
        ]);

        // Récupérer le dossier
        $dossier = Dossier::findOrFail($request->id);
        //si le dossier n'existe pas, renvoyer une erreur
        if (!$dossier) {
            return response()->json(['message' => 'Dossier non trouvé'], 404);
        }

        // Mettre à jour le champ motif_rejet (par exemple, en JSON)
        $dossier->motif_rejet = $validated['selected'];
        $dossier->dossier_rejeted_by = Auth::id();
        $dossier->date_rejet = now();
        $dossier->statut = 3;

        $dossier->save();

        // Retourner une réponse
        return response()->json([
            'message' => 'Dossier rejeté avec succès !',
            'selected' => $validated['selected'],
            'dossier_id' => $dossier->id,
        ]);
    }


    public function getMotifsRejets($dossierId)
    {
        // 1. Récupérer le dossier
        $dossier = Dossier::findOrFail($dossierId);

        // 2. Récupérer le tableau des IDs de motifs de rejet (décoder le JSON si nécessaire)
        $motifsRejetIds = json_decode($dossier->motif_rejet, true); // true pour obtenir un tableau associatif

        // 3. Vérifier que $motifsRejetIds est bien un tableau
        if (!is_array($motifsRejetIds)) {
            return response()->json(['motifs' => []], 200);
        }

        // 4. Récupérer les motifs sélectionnés
        $motifsSelectionnes = Rejet::whereIn('id', $motifsRejetIds)->get();

        // 5. Retourner les motifs
        return response()->json([
            'motifs' => $motifsSelectionnes,
            'dossier_id' => $dossier->id,
        ]);
    }


    // Gestion admin rejets

    // public function listRejets()
    // {
    //     return Inertia::render('Admin/Rejets/index', [
    //         'rejets' => Rejet::orderBy('id', 'desc')->get()
    //     ]);
    // }
    public function listRejets()
    {
        return Inertia::render('Admin/Rejets/index', [
            'rejets' => Rejet::orderBy('id', 'desc')->get(),
            'services' => \App\Models\Service::all(['id', 'nom_service']),
            'typeServices' => \App\Models\TypeService::all(['id', 'nom_type_service'])
        ]);
    }

    public function storeRejets(Request $request)
    {
        $validated = $request->validate([
            'motif' => 'required|string|max:255',
            'service_id' => 'required|integer',
            'id_type_services' => 'required|integer',
        ]);

        Rejet::create($validated);

        return back()->with('success', 'Rejet ajouté');
    }

    public function updateRejets(Request $request, Rejet $rejet)
    {
        $validated = $request->validate([
            'motif' => 'required|string|max:255',
            'service_id' => 'required|integer',
            'id_type_services' => 'required|integer',
        ]);

        $rejet->update($validated);

        return back()->with('success', 'Rejet modifié');
    }

    public function destroyRejets(Rejet $rejet)
    {
        $rejet->delete();

        return back()->with('success', 'Rejet supprimé');
    }
}
