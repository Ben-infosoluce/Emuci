<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Dossier;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    //show welcome page
    public function clientListe(Request $request)
    {
        $query = Client::query();

        // Filtre recherche
        if ($request->filled('search_data')) {
            $search = $request->search_data;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%$search%")
                    ->orWhere('prenom', 'like', "%$search%")
                    ->orWhere('adresse', 'like', "%$search%");
            });
        }

        //  Filtre statut
        if ($request->filled('statut') && $request->statut != "0") {
            $query->where('statut', $request->statut);
        }

        //  Pagination
        // $clients = $query->paginate(10)->withQueryString();
        $clients = $query->paginate(10)->appends($request->query());


        return inertia('Admin/client', [
            'clients' => $clients->items(),
            'meta'    => [
                'current_page' => $clients->currentPage(),
                'last_page'    => $clients->lastPage(),
                'per_page'     => $clients->perPage(),
                'total'        => $clients->total(),
            ],
        ]);
    }
    // return view('welcome');

    public function chrono(Request $request)
    {
        /* ---------------- Validation ---------------- */
        $validated = $request->validate([
            'num_chrono' => ['required', 'string', 'max:50'],
            'telephone' => ['required', 'string', 'max:50'],
        ]);

        /* ---------------- Client ---------------- */
        $client = Client::select('id', 'nom', 'prenom')
            ->where('telephone', $validated['telephone'])
            ->first();

        if (!$client) {
            return response()->json([
                'message' => 'Client introuvable',
            ], 404);
        }

        /* ---------------- Dossier sécurisé ---------------- */
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_documents',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions',
        ])
            ->where('id_client', $client->id)
            ->where('num_chrono', $validated['num_chrono'])
            ->first();

        if (!$dossier) {
            return response()->json([
                'message' => 'Aucun dossier trouvé pour ce numéro chrono',
            ], 404);
        }

        /* ---------------- Success ---------------- */
        return response()->json([
            'message' => 'Dossier récupéré avec succès',
            'client'  => $client,
            'dossier' => $dossier,
        ]);
    }
}
