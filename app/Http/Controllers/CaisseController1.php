<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use App\Models\CaisseOuverture;
use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Service;
use App\Models\Vehicule;
use Illuminate\Support\Facades\Auth;

class CaisseController extends Controller
{
    //
    public function showCaisseDashboard()
    {
        $currentDateTime = now();
        return inertia('Caisse/Dashbord', ['currentDateTime' => $currentDateTime]);
    }

    public function showCaisseData()
    {
        return inertia('Caisse/index');
    }

    public function showNewCaisse()
    {
        return inertia('Caisse/new');
    }

    public function showCaisseGetData($vin)
    {
        $dossiers = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ])->where('num_chrono', $vin)->first();

        if (!$dossiers) {
            abort(404, 'Dossier non trouvé');
        }

        return inertia('Caisse/components/createForm', [
            'chrono' => $vin,
            'dossier' => $dossiers,
        ]);
    }
    public function getCaisseData(Request $request)
    {
        $query = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ]);

        $filtre_per_page = $request->input('filtre_per_page', 10);
        $statut = $request->input('statut');
        $filtre_type = $request->input('filtre_type');
        $search_data = $request->input('search_data');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');

        // Filtre par VIN (via search_data) et num_chrono
        if ($search_data) {
            // Récupérer les IDs des véhicules qui correspondent au VIN
            $vehicules = Vehicule::where('vin', 'like', "%{$search_data}%")->pluck('id');

            // Appliquer le filtre sur les dossiers
            $query->where(function ($q) use ($search_data, $vehicules) {
                $q->whereIn('id_vehicule', $vehicules)
                    ->orWhere('num_chrono', 'like', "%{$search_data}%");
            });
        }

        // Filtre par statut
        if ($statut) {
            $query->where("statut", $statut);
        }

        // Filtre par date de création (created_at)
        if ($date_start && $date_end) {
            // On s'assure que ce sont des dates valides
            try {
                // $start = Carbon::parse($date_start)->startOfDay();
                //  $end = Carbon::parse($date_end)->endOfDay();
                $start = $date_start;
                $end = $date_end;

                $query->whereBetween('date_creation', [$start, $end]);
            } catch (\Exception $e) {
                // Optionnel : log ou ignorer si erreur de date
            }
        }

        $dossiers = $query->latest()->paginate($filtre_per_page);

        return response()->json([
            'dossiers' => $dossiers,
            'filtres' => $request->only(
                "filtre_per_page",
                "statut",
                "search_data",
                "filtre_type",
                "date_start",
                "date_end"
            )
        ]);
    }



    // *************************Gestion ouverture & Fermeture de la caisse *************************//

    public function open()
    {
        $user = Auth::user();

        // On récupère la caisse du site de l'utilisateur
        $caisse = Caisse::where('site_id', $user->id_site)->firstOrFail();

        // Vérifier qu’aucune ouverture active n’existe sur cette caisse
        $alreadyOpen = CaisseOuverture::where('caisse_id', $caisse->id)
            ->where('statut', 'ouvert')
            ->exists();

        if ($alreadyOpen) {
            return response()->json([
                'message' => 'Cette caisse est déjà ouverte.',
            ], 403);
        }

        CaisseOuverture::create([
            'user_id' => $user->id,
            'caisse_id' => $caisse->id,
            'date_ouverture' => now(),
            'statut' => 'ouvert',
        ]);

        return response()->json(['message' => 'Caisse ouverte avec succès.']);
    }

    public function close(Request $request, $id)
    {
        $user = Auth::user();

        $ouverture = CaisseOuverture::where('id', $id)
            ->where('statut', 'ouvert')
            ->where('user_id', $user->id) // 🔒 Seul celui qui a ouvert peut fermer
            ->firstOrFail();

        $request->validate([
            'montant_fermeture' => 'required|numeric|min:0',
            // 'observations' => 'nullable|string',
        ]);

        $ouverture->update([
            'date_fermeture' => now(),
            'montant_fermeture' => $request->montant_fermeture,
            'statut' => 'fermé',
            // 'observations' => $request->observations,
        ]);

        return response()->json(['message' => 'Caisse fermée.']);
    }

    public function statut()
    {
        $userSiteId = getIdSite();
        $caisse = Caisse::where('site_id', $userSiteId)->first();

        if (!$caisse) {
            return response()->json([
                'statut' => 'aucune caisse définie pour ce site',
            ]);
        }

        $ouverture = $caisse->ouvertures()
            ->where('statut', 'ouvert')
            ->latest()
            ->with('user')
            ->first();

        if ($ouverture) {
            return response()->json([
                'statut' => 'ouverte',
                'ouvert_par' => $ouverture->user->nom,
                'date_ouverture' => $ouverture->date_ouverture,
                'caisse_id' => $ouverture->id,
            ]);
        } else {
            return response()->json([
                'statut' => 'fermée',
            ]);
        }
    }
}
