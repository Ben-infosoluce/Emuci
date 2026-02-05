<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function showSiteDashboard()
    {
        $currentDateTime = now();
        return inertia('Admin/Dashbord', ['currentDateTime' => $currentDateTime]);
    }

    public function showSiteData()
    {
        return inertia('Admin/site');
    }

    public function getSiteData(Request $request)
    {
        // Récupération des filtres depuis la requête
        $filtre_per_page = $request->input('filtre_per_page', 10);
        $statut = $request->input('statut');
        $search_data = $request->input('search_data');

        // Construction de la requête
        $query = Site::query();

        // Filtre par nom (recherche partielle)
        if (!empty($search_data)) {
            $query->where('nom_site', 'like', '%' . $search_data . '%');
        }

        // Filtre par statut
        if (!empty($statut)) {
            $query->where('statut', $statut);
        }

        // Récupération des données paginées triées par ID décroissant
        $sites = $query->orderBy('id', 'desc')->paginate($filtre_per_page);

        // Réponse JSON
        return response()->json([
            'sites' => $sites,
            'filtres' => $request->only(
                'filtre_per_page',
                'statut',
                'search_data'
            )
        ]);
    }




    public function showNewSite()
    {
        return inertia('Admin/new');
    }

    public function storeNewSite(Request $request)
    {
        $validated = $request->validate([
            'nom_site' => 'required|string',
            'type_site' => 'required|string',
            'region' => 'required|string',
            'heures_ouverture' => 'required|date_format:H:i:s',
            'heures_fermeture' => 'required|date_format:H:i:s',
        ]);

        Site::create([
            'nom_site' => $validated['nom_site'],
            'type_site' => $validated['type_site'],
            'region' => $validated['region'],
            'heures_ouverture' => $validated['heures_ouverture'],
            'heures_fermeture' => $validated['heures_fermeture'],
            'statut' => 1,
        ]);

        return redirect()->back();
    }

    public function showEditSite($id)
    {
        $site = Site::findOrFail($id);
        return response()->json($site);
    }

    public function updateSite(Request $request, $id)
    {
        $validated = $request->validate([
            'nom_site' => 'required|string',
            'type_site' => 'required|string',
            'region' => 'required|string',
            'heures_ouverture' => 'required|date_format:H:i:s',
            'heures_fermeture' => 'required|date_format:H:i:s',
        ]);

        $site = Site::findOrFail($id);
        $site->update($validated);

        return redirect()->back();
    }

    public function deleteSite($id)
    {
        $site = Site::findOrFail($id);
        $site->delete();
        return redirect()->back();
    }

    public function updateStatutSite(Request $request, $id)
    {
        $validated = $request->validate([
            'statut' => 'required|integer|in:1,2',
        ]);

        $site = Site::findOrFail($id);
        $site->statut = $validated['statut'];
        $site->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès.',
            'item' => $site
        ]);
    }
}
