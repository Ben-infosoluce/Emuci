<?php

namespace App\Http\Controllers;

use App\Models\Entite;
use Illuminate\Http\Request;

class EntiteController extends Controller
{
    public function showEntiteList(Request $request)
    {
        $query = Entite::query();

        // Filtres
        if ($request->search_data) {
            $query->where('nom', 'like', "%{$request->search_data}%");
        }
        if ($request->statut) {
            $query->where('statut', $request->statut);
        }

        $entites = $query->orderBy('id', 'desc')->paginate(10);

        return inertia('Admin/entite', [
            'entites' => $entites->items(),
            'meta' => $entites->toArray(),
        ]);
    }


    public function createEntite(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
        ]);

        Entite::create([
            'nom' => $validated['nom'],
            'statut' => 1,
        ]);

        return redirect()->back();
    }

    public function showEntiteEdit($id)
    {
        $entite = Entite::findOrFail($id);
        return response()->json($entite);
    }

    public function updateEntite(Request $request, $id)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
        ]);

        $entite = Entite::findOrFail($id);
        $entite->update($validated);

        return response()->json($entite);
    }

    public function deleteEntite($id)
    {
        $entite = Entite::findOrFail($id);
        $entite->delete();
        return response()->json(['message' => 'Supprimé avec succès.']);
    }

    public function updateStatutEntite(Request $request, $id)
    {
        $validated = $request->validate([
            'statut' => 'required|integer|in:1,2',
        ]);

        $entite = Entite::findOrFail($id);
        $entite->statut = $validated['statut'];
        $entite->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'item' => $entite
        ]);
    }
}
