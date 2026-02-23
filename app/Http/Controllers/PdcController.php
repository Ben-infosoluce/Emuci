<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Dossier;
use App\Models\Entreprise;
use App\Models\Service;
use App\Models\Vehicule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Client;
use function Pest\Laravel\json;
use App\Http\Requests\PdcImmatriculationRules;
use App\Http\Requests\PdcImmatriculationMessages;
use App\Models\ModificationLog;
use App\Helpers\LogHelper;
use Dflydev\DotAccessData\Data;
use App\Http\Requests\SavePdcReImmatriculationRequest;
use App\Models\Correction;
use App\Models\Recu;

class PdcController extends Controller
{
    //**************** POOL DE CONTROLE *****************//

    //dashbord
    public function showPdcDashboard()
    {
        return inertia('Pdc/Dashbord',);
    }
    //verifie vin
    public function verifieVin($vin)
    {
        try {
            // Supprimer tous les espaces (y compris les espaces au milieu)
            $vin = str_replace(' ', '', $vin);
            // Ou pour supprimer tous les espaces blancs (espaces, tabulations, etc.)
            $vin = preg_replace('/\s+/', '', $vin);

            $exists = Vehicule::where('vin', $vin)->exists();

            return response()->json([
                'exists' => $exists,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifieChrono($vin)
    {
        // Supprimer tous les espaces (y compris les espaces au milieu)
        $vin = str_replace(' ', '', $vin);
        // Ou pour supprimer tous les espaces blancs (espaces, tabulations, etc.)
        $vin = preg_replace('/\s+/', '', $vin);
        try {
            // $exists = Vehicule::where('vin', $vin)->exists();
            $exists = Dossier::where('num_chrono', $vin)->exists();

            return response()->json([
                'exists' => $exists,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    //----------immatriculation------------//

    //show immatriculation
    public function showPdcImmatriculation()
    {
        return inertia('Pdc/Immatriculation/index');
    }

    public function showPdcImmatriculationSelect()
    {
        return inertia('Pdc/Immatriculation/selectService');
    }




    //get immatriculation data
    public function getPdcImmatriculationData(Request $request)
    {
        $query = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ])->where('id_service', 1);

        $filtre_per_page = $request->input('filtre_per_page', 10);
        $statut = $request->input('statut');
        $filtre_type = $request->input('filtre_type');
        $search_data = $request->input('search_data');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');

        // Filtre par VIN (via search_data)
        // if ($search_data) {
        //     $vehicule = Vehicule::where('vin', 'like', "%{$search_data}%")->first();

        //     if ($vehicule) {
        //         $query->where('id_vehicule', $vehicule->id);
        //     } else {
        //         $query->whereRaw('0 = 1'); // Aucun résultat si pas de match
        //     }
        // }
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

    //savePdcImmatriculationData
    // Assurez-vous d'importer le modèle Client

    public function savePdcImmatriculationData(Request $request)
    {
        // dd($request->all());

        // Définition des règles de validation
        $rules = [
            'DateNaissance' => 'required|string',
            'adresse' => 'required|string',
            'carrosserie' => 'required|string',
            'vin' => 'required|string',
            'couleurVehicule' => 'required|string',
            'district' => 'nullable|string',
            'email' => 'nullable|email',
            'firstname' => 'required|string',
            'genre' => 'required|string',
            'lastname' => 'required|string',
            'marqueVehicule' => 'required|array',
            'modelVehicule' => 'required|array',
            'nombreEssieux' => 'required|integer',
            'phone' => 'required|string',
            'placesAssises' => 'required|integer',
            'prefecture' => 'nullable|string',
            'ptac' => 'required|integer',
            'pu' => 'required|integer',
            'puissance' => 'required|string',
            'pv' => 'required|string',
            'region' => 'nullable|string',
            'usage' => 'nullable|string',
            'sourcesEnergie' => 'required|string',
            'sousPrefecture' => 'nullable|string',
            'type' => 'required|string',
            'typePersonne' => 'required|string',
            'typeTechnique' => 'required|string',
            'villeNaissance' => 'required|string',
            'anneeProduction' => 'required|string',
            'dateCirculation' => 'required|string',
            'cylindree' => 'required|string',
            'type_service' => 'required|string',
            // 'num_immatriculation_precedant' => 'required|string',
        ];
        $messages = [
            'DateNaissance.required' => 'La date de naissance est obligatoire.',
            'DateNaissance.string' => 'La date de naissance doit être une chaîne de caractères.',

            'adresse.required' => 'L’adresse est obligatoire.',
            'adresse.string' => 'L’adresse doit être une chaîne de caractères.',

            'carrosserie.required' => 'La carrosserie du véhicule est obligatoire.',
            'carrosserie.string' => 'La carrosserie doit être une chaîne de caractères.',

            'vin.required' => 'Le numéro de châssis (VIN) est obligatoire.',
            'vin.string' => 'Le VIN doit être une chaîne de caractères.',

            'couleurVehicule.required' => 'La couleur du véhicule est obligatoire.',
            'couleurVehicule.string' => 'La couleur du véhicule doit être une chaîne de caractères.',

            'district.string' => 'Le district doit être une chaîne de caractères.',

            'email.email' => 'L’adresse email n’est pas valide.',

            'firstname.required' => 'Le prénom est obligatoire.',
            'firstname.string' => 'Le prénom doit être une chaîne de caractères.',

            'genre.required' => 'Le genre est obligatoire.',
            'genre.string' => 'Le genre doit être une chaîne de caractères.',

            'lastname.required' => 'Le nom de famille est obligatoire.',
            'lastname.string' => 'Le nom doit être une chaîne de caractères.',

            'marqueVehicule.required' => 'La marque du véhicule est obligatoire.',
            'marqueVehicule.array' => 'La marque du véhicule doit être un tableau.',

            'modelVehicule.required' => 'Le modèle du véhicule est obligatoire.',
            'modelVehicule.array' => 'Le modèle du véhicule doit être un tableau.',

            'nombreEssieux.required' => 'Le nombre d’essieux est obligatoire.',
            'nombreEssieux.integer' => 'Le nombre d’essieux doit être un entier.',

            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.string' => 'Le téléphone doit être une chaîne de caractères.',

            'placesAssises.required' => 'Le nombre de places assises est obligatoire.',
            'placesAssises.integer' => 'Le nombre de places assises doit être un entier.',

            'prefecture.string' => 'La préfecture doit être une chaîne de caractères.',

            'ptac.required' => 'Le PTAC est obligatoire.',
            'ptac.integer' => 'Le PTAC doit être un entier.',

            'pu.required' => 'La PU est obligatoire.',
            'pu.integer' => 'La PU doit être un entier.',

            'puissance.required' => 'La puissance est obligatoire.',
            'puissance.string' => 'La puissance doit être une chaîne de caractères.',

            'pv.required' => 'Le PV est obligatoire.',
            'pv.string' => 'Le PV doit être une chaîne de caractères.',

            'region.string' => 'La région doit être une chaîne de caractères.',

            'usage.string' => 'L’usage doit être une chaîne de caractères.',

            'sourcesEnergie.required' => 'La source d’énergie est obligatoire.',
            'sourcesEnergie.string' => 'La source d’énergie doit être une chaîne de caractères.',

            'sousPrefecture.string' => 'La sous-préfecture doit être une chaîne de caractères.',

            'type.required' => 'Le type de véhicule est obligatoire.',
            'type.string' => 'Le type doit être une chaîne de caractères.',

            'typePersonne.required' => 'Le type de personne est obligatoire.',
            'typePersonne.string' => 'Le type de personne doit être une chaîne de caractères.',

            'typeTechnique.required' => 'Le type technique est obligatoire.',
            'typeTechnique.string' => 'Le type technique doit être une chaîne de caractères.',

            'villeNaissance.required' => 'La ville de naissance est obligatoire.',
            'villeNaissance.string' => 'La ville de naissance doit être une chaîne de caractères.',

            'anneeProduction.required' => 'L’année de production est obligatoire.',
            'anneeProduction.string' => 'L’année de production doit être une chaîne de caractères.',

            'dateCirculation.required' => 'La date de première mise en circulation est obligatoire.',
            'dateCirculation.string' => 'La date de circulation doit être une chaîne de caractères.',

            'cylindree.required' => 'La cylindrée est obligatoire.',
            'cylindree.string' => 'La cylindrée doit être une chaîne de caractères.',
            'type_service.required' => 'Le type de service est obligatoire.',
            'type_service.string' => 'Le type de service doit être une chaîne de caractères.',

        ];

        if ($request->input('type') != 'Physique') {
            $rules = array_merge($rules, [
                'registreCommerce' => 'required|string',
                'nomEntreprise' => 'required|string',
                'representantLegal' => 'required|string',
                'numeroTelephone' => 'required|string',
                'compteContribuable' => 'required|string',
                'dateNaissanceRepresantant' => 'nullable|string',
                'professionRepresantant' => 'nullable|string',
            ]);
        }


        // Validation
        $validated = $request->validate($rules, $messages);
        //je veux verfier s'il n'y a pas dejas une immatriculation pour ce vehicule en cours
        $existingDossier = Dossier::where('id_service', 2)
            ->whereHas('r_dossier_vehicule', function ($query) use ($validated) {
                $query->where('vin', $validated['vin']);
            })
            ->whereIn('statut', [1, 4]) // Statuts en cours 1: En attente, 2: Valider, 3: Refuser, 4: En cours	
            ->first();
        if ($existingDossier) {
            return response()->json([
                'success' => false,
                'message' => 'Oups! une Opération Speciale est déjà en cours pour ce véhicule.'
            ], 409); // 409 Conflict
        }

        //récupérer le genre pour nb_plaque
        $nbPlaque = DB::table('genre')
            ->where('nom', $request->input('genre'))
            ->value('nb_plaque');


        try {
            DB::beginTransaction();

            // Création du client
            $client = Client::create([
                'nom' => $request->input('firstname'),
                'prenom' => $request->input('lastname'),
                'date_naissance' => $request->input('DateNaissance'),
                'ville_naissance' => $request->input('villeNaissance'),
                'adresse' => $request->input('adresse'),
                'telephone' => $request->input('phone'),
                'email' => $request->input('email'),
                'civilite' => $request->input('sex'),
                'district_client' => $request->input('district'),
                'prefecture_client'  => $request->input('prefecture_client'),
                'sous_prefecture_client'  => $request->input('sousPrefectureClient'),
                'password' => generateStrongPassword(8),
                'statut' => 1,
                'validite' => Carbon::now()->addMonths(3),
            ]);

            // Création de l'entreprise si type Morale
            $entreprise = null;
            if ($request->type != 'Physique') {
                $entreprise = Entreprise::create([
                    'nom_entreprise' => $request->input('nomEntreprise'),
                    'nom_representant_legal' => $request->input('representantLegal'),
                    'telephone_representant_legal' => $request->input('numeroTelephone'),
                    'registre_commerce' => $request->input('registreCommerce'),
                    'compte_contribuale' => $request->input('compteContribuable'),
                    'prefecture' => $request->input('prefecture'),
                    'sous_prefecture' => $request->input('sousPrefecture'),
                    'date_de_naissance_representant_legal' => $request->input('dateNaissanceRepresantant'),
                    'profession_representant_legal' => $request->input('professionRepresantant'),
                    'region' => $request->input('region'),
                ]);
            }

            // Création du véhicule
            $vehicule = new Vehicule();
            $vehicule->annee_production = $request->input('anneeProduction');
            $vehicule->vin = $request->input('vin');
            $vehicule->marque = $request->marqueVehicule['nom'];
            $vehicule->modele = $request->modelVehicule['nom'];
            $vehicule->couleur = $request->input('couleurVehicule');
            $vehicule->source_energie = $request->input('sourcesEnergie');
            $vehicule->genre_vehicule = $request->input('genre');
            $vehicule->poids_total_charge = $request->input('ptac');
            $vehicule->poids_utile = $request->input('pu');
            $vehicule->poids_vide = $request->input('pv');
            $vehicule->nb_plaque = $nbPlaque;
            $vehicule->puissance_administrative = $request->input('puissance');
            $vehicule->places_assises = intval(preg_replace('/\D/', '', $request->input('placesAssises')));
            $vehicule->nombre_essieux = intval(preg_replace('/\D/', '', $request->input('nombreEssieux')));
            $vehicule->date_mise_circulation = $request->input('dateCirculation');
            $vehicule->id_site = getIdSite();
            $vehicule->cylindree = $request->input('cylindree');
            //
            $vehicule->carrosserie = $request->input('carrosserie');
            $vehicule->type_technique = $request->input('typeTechnique');
            $vehicule->code_de_region = $request->input('codeRegion');
            $vehicule->usage_vehicule = $request->input('usage');
            $vehicule->physique_morale = $request->input('typePersonne');
            $vehicule->entreprise_id = $entreprise?->id;
            $vehicule->id_client = $client->id;
            $vehicule->model_id = $request->modelVehicule['id'];
            $vehicule->marque_id = $request->marqueVehicule['id'];
            $vehicule->save();

            // $detail = "Immatriculation Spéciale";
            //création du dossier
            $dossier = new Dossier();
            $dossier->id_vehicule = $vehicule->id;
            $dossier->id_user = getConnectedUserId();
            $dossier->num_chrono = generateChronoNumber('OPSP'); // OPSP generateCodeChrono();
            $dossier->id_client = $client->id;
            $dossier->id_site = getIdSite();
            $dossier->id_service = 1;
            $dossier->id_type_service = 1;
            $dossier->detail = '["' . $request->input('type_service') . '"]';
            $dossier->statut = 1;
            $dossier->date_creation = now();
            $dossier->save();

            DB::commit();
            //ici
            return response()->json([
                'success' => true,
                'message' => 'Enregistrement réussi.',
                'data' => $dossier
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePdcImmatriculationData(Request $request, $id)
    {

        $typePersonne = $request->input('type');
        $rules = PdcImmatriculationRules::updatePostImmatriculationRules($typePersonne);
        $messages = PdcImmatriculationMessages::updatePostImmatriculationMessages($typePersonne);

        $validated = $request->validate($rules, $messages);
        // dd($validated);
        try {
            DB::beginTransaction();

            $vehicule = Vehicule::findOrFail($id);
            $client = $vehicule->r_vehicule_client;
            $entreprise = $vehicule->r_vehicule_entreprise;

            $client->update([
                'nom' => $request->input('firstname'),
                'prenom' => $request->input('lastname'),
                'date_naissance' => $request->input('DateNaissance'),
                'ville_naissance' => $request->input('villeNaissance'),
                'adresse' => $request->input('adresse'),
                'telephone' => $request->input('phone'),
                'email' => $request->input('email'),
                'civilite' => $request->input('sex'),
                'district_client' => $request->input('districtClient'),
                'prefecture_client'  => $request->input('prefecture_client'),
                'sous_prefecture_client'  => $request->input('sousPrefectureClient'),
            ]);

            $modificationsEntreprise = [];

            if ($request->typePersonne != 'Physique') {
                $donneesEntreprise = [
                    'nom_entreprise' => $request->input('nomEntreprise'),
                    'nom_representant_legal' => $request->input('representantLegal'),
                    'telephone_representant_legal' => $request->input('numeroTelephone'),
                    'registre_commerce' => $request->input('registreCommerce'),
                    'compte_contribuale' => $request->input('compteContribuable'),
                    'prefecture' => $request->input('prefecture'),
                    'sous_prefecture' => $request->input('sousPrefecture'),
                    'region' => $request->input('region'),
                    'date_de_naissance_representant_legal' => $request->input('dateNaissanceRepresantant'),
                    'profession_representant_legal' => $request->input('professionRepresantant'),
                ];

                if (!$entreprise) {
                    $entreprise = Entreprise::create($donneesEntreprise);
                } else {
                    $entreprise->fill($donneesEntreprise);
                    $modificationsEntreprise = $entreprise->getDirty();
                    $entreprise->update($donneesEntreprise);
                }
            }

            $vehicule->update([
                'annee_production' => $request->input('anneeProduction'),
                'vin' => $request->input('vin'),
                'marque' => $request->marqueVehicule['nom'],
                'modele' => $request->modelVehicule['nom'],
                'couleur' => $request->input('couleurVehicule'),
                'source_energie' => $request->input('sourcesEnergie'),
                'genre_vehicule' => $request->input('genre'),
                'poids_total_charge' => $request->input('ptac'),
                'poids_utile' => $request->input('pu'),
                'poids_vide' => $request->input('pv'),
                'puissance_administrative' => $request->input('puissance'),
                'places_assises' => intval(preg_replace('/\D/', '', $request->input('placesAssises'))),
                'nombre_essieux' => intval(preg_replace('/\D/', '', $request->input('nombreEssieux'))),
                'date_mise_circulation' => $request->input('dateCirculation'),
                'carrosserie' => $request->input('carrosserie'),
                'type_technique' => $request->input('typeTechnique'),
                'code_de_region' => $request->input('codeRegion'),
                'usage_vehicule' => $request->input('usage'),
                'physique_morale' => $request->input('type'),
                'model_id' => $request->modelVehicule['id'],
                'marque_id' => $request->marqueVehicule['id'],
                'entreprise_id' => $entreprise ? $entreprise->id : null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Les informations ont été mises à jour avec succès.',
                'data' => ['modificationsEntreprise' => $modificationsEntreprise, 'vehicule' => $vehicule]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //edit immatriculation
    public function showEditPdcImmatriculation($id)
    {
        //rechercher le dossier qui a id $id et le retourner
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        $vehicule = $dossier->r_dossier_vehicule;
        $client = $dossier->r_dossier_client;

        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;
        // dd($client);
        $data = [
            'immatriculation' => [
                'id' => $vehicule->id,
                'type_personne' => $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'civilite' => $client->civilite ?? null,
                'firstname' => $client->nom ?? null,
                'lastname' => $client->prenom ?? null,
                'phone' => $client->telephone ?? null,
                'adresse' => $client->adresse ?? null,
                'DateNaissance' => $client->date_naissance ?? null,
                'villeNaissance' => $client->ville_naissance ?? null,
                'email' => $client->email ?? null,
                'prefecture_client'  => $client->prefecture_client ?? null,
                'sous_prefecture_client'  => $client->sous_prefecture_client ?? null,
                'district_client' => $client->district_client ?? null,




                // Informations véhicule
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
                'couleurVehicule' => $vehicule->couleur,
                'carrosserie' => $vehicule->carrosserie,
                'typeTechnique' => $vehicule->type_technique,
                'typePersonne' => $vehicule->physique_morale,
                'type' =>  $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'genre' => $vehicule->genre_vehicule,
                'ptac' => $vehicule->poids_total_charge,
                'pu' => $vehicule->poids_utile,
                'pv' => $vehicule->poids_vide,
                'puissance' => $vehicule->puissance_administrative,
                'placesAssises' => $vehicule->places_assises,
                'usage' => $vehicule->usage_vehicule,
                'sourcesEnergie' => $vehicule->source_energie,
                'nombreEssieux' => $vehicule->nombre_essieux,
                'codeRegion' => $vehicule->code_de_region,
                'DateCirculation' => $vehicule->date_mise_circulation,
                'AnneeProduction' => $vehicule->annee_production,
                'nb_plaque' => $vehicule->nb_plaque,
                'cylindree' => $vehicule->cylindree,

                // Informations entreprise (si morale)
                'nomEntreprise' => $entreprise->nom_entreprise ?? null,
                'registreCommerce' => $entreprise->registre_commerce ?? null,
                'representantLegal' => $entreprise->nom_representant_legal ?? null,
                'numeroTelephone' => $entreprise->telephone_representant_legal ?? null,
                'compteContribuable' => $entreprise->compte_contribuale ?? null,
                'DateNaissanceRepresantant' => $entreprise->date_de_naissance_representant_legal ?? null,
                'ProfessionRepresantant' => $entreprise->profession_representant_legal ?? null,
                'prefecture' => $entreprise->prefecture ?? null,
                'sousPrefecture' => $entreprise->sous_prefecture ?? null,
                'region' =>  null,
                // 'marqueVehicule' => $vehicule->marque, // Adapté à votre structure
            ],
            'marques' => DB::table('marque')->get(), // Vous devrez importer le modèle Marque
            'modeles' => $vehicule->model_id ? DB::table('model')->where('marque_id', $vehicule->marque_id)->get() : []
        ];

        // return response()->json($data);

        return inertia('Pdc/Immatriculation/components/editForm', ['dossier' => $dossier, 'entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }
    public function showViewPdcImmatriculation($id)
    {
        //rechercher le dossier qui a id $id et le retourner

        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        $vehicule = $dossier->r_dossier_vehicule;
        $client = $dossier->r_dossier_client;

        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;
        // dd($client);
        $data = [
            'immatriculation' => [
                'id' => $vehicule->id,
                'type_personne' => $vehicule->physique_morale,
                'civilite' => $client->civilite ?? null,
                'firstname' => $client->nom ?? null,
                'lastname' => $client->prenom ?? null,
                'phone' => $client->telephone ?? null,
                'adresse' => $client->adresse ?? null,
                'DateNaissance' => $client->date_naissance ?? null,
                'villeNaissance' => $client->ville_naissance ?? null,
                'email' => $client->email ?? null,
                'prefecture_client'  => $client->prefecture_client ?? null,
                'sous_prefecture_client'  => $client->sous_prefecture_client ?? null,
                'district_client' => $client->district_client ?? null,


                // Informations véhicule
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
                'couleurVehicule' => $vehicule->couleur,
                'carrosserie' => $vehicule->carrosserie,
                'typeTechnique' => $vehicule->type_technique,
                'typePersonne' => $vehicule->physique_morale,
                'type' =>  $vehicule->physique_morale,
                'genre' => $vehicule->genre_vehicule,
                'ptac' => $vehicule->poids_total_charge,
                'pu' => $vehicule->poids_utile,
                'pv' => $vehicule->poids_vide,
                'puissance' => $vehicule->puissance_administrative,
                'placesAssises' => $vehicule->places_assises,
                'usage' => $vehicule->usage_vehicule,
                'sourcesEnergie' => $vehicule->source_energie,
                'nombreEssieux' => $vehicule->nombre_essieux,
                'codeRegion' => $vehicule->code_de_region,
                'DateCirculation' => $vehicule->date_mise_circulation,
                'AnneeProduction' => $vehicule->annee_production,
                'region' => null,


                // Informations entreprise (si morale)
                'nomEntreprise' => $entreprise->nom_entreprise ?? null,
                'registreCommerce' => $entreprise->registre_commerce ?? null,
                'representantLegal' => $entreprise->nom_representant_legal ?? null,
                'numeroTelephone' => $entreprise->telephone_representant_legal ?? null,
                'compteContribuable' => $entreprise->compte_contribuale ?? null,
                'DateNaissanceRepresantant' => $entreprise->date_de_naissance_representant_legal ?? null,
                'ProfessionRepresantant' => $entreprise->profession_representant_legal ?? null,
                'prefecture' => $entreprise->prefecture ?? null,
                'sousPrefecture' => $entreprise->sous_prefecture ?? null,
                // 'marqueVehicule' => $vehicule->marque, // Adapté à votre structure
            ],
            'marques' => DB::table('marque')->get(), // Vous devrez importer le modèle Marque
            'modeles' => $vehicule->model_id ? DB::table('model')->where('marque_id', $vehicule->marque_id)->get() : []
        ];

        // return response()->json($data);

        return inertia('Pdc/Immatriculation/components/viewForm', ['dossier' => $dossier, 'entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }
    public function showReceiptPdcImmatriculation($id)
    {
        //rechercher le dossier qui a id $id et le retourner
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        $vehicule = $dossier->r_dossier_vehicule;
        $client = $dossier->r_dossier_client;
        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;
        $data = [
            'receipt' => [
                'num_immatriculation' => $vehicule->num_immatriculation,
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
            ],
        ];

        // return response()->json($data);

        return inertia('Pdc/Immatriculation/components/receipt', ['dossier' => $dossier, 'entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }


    //new immatriculation
    public function showNewPdcImmatriculation()
    {
        return inertia('Pdc/Immatriculation/new');
    }

    //add immatriculation
    public function showNewPdcImmatriculationAddData($vin)
    {
        // dd($vin);
        // dd('Rti/Immatriculation/new/' . $id);
        return inertia('Pdc/Immatriculation/components/createForm', ['vin' => $vin]);
    }


    //----------------re-immatriculation-----------------//

    //show re-immatriculation
    public function showPdcReImmatriculation()
    {
        return inertia('Pdc/ReImmatriculation/index');
    }

    public function showPdcReImmatriculationSelect()
    {
        return inertia('Pdc/ReImmatriculation/selectService');
    }



    //get re-immatriculation data
    public function getPdcReImmatriculationData(Request $request)
    {
        $query = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ])->where('id_service', 2);

        $filtre_per_page = $request->input('filtre_per_page', 10);
        $statut = $request->input('statut');
        $filtre_type = $request->input('filtre_type');
        $search_data = $request->input('search_data');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');

        // Filtre par VIN (via search_data)
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

    public function savePdcReImmatriculationData(Request $request)
    {

        // dd($request->all());
        // Définition des règles de validation
        $rules = [
            'DateNaissance' => 'required|string',
            'adresse' => 'required|string',
            'carrosserie' => 'required|string',
            'vin' => 'required|string',
            'couleurVehicule' => 'required|string',
            'district' => 'nullable|string',
            'email' => 'nullable|email',
            'firstname' => 'required|string',
            'genre' => 'required|string',
            'lastname' => 'required|string',
            'marqueVehicule' => 'required|array',
            'modelVehicule' => 'required|array',
            'nombreEssieux' => 'required|integer',
            'phone' => 'required|string',
            'placesAssises' => 'required|integer',
            'prefecture' => 'nullable|string',
            'ptac' => 'required|integer',
            'pu' => 'required|integer',
            'puissance' => 'required|string',
            'pv' => 'required|string',
            'region' => 'nullable|string',
            'usage' => 'nullable|string',
            'sourcesEnergie' => 'required|string',
            'sousPrefecture' => 'nullable|string',
            'type' => 'required|string',
            'typePersonne' => 'required|string',
            'typeTechnique' => 'required|string',
            'controleurTechnique' => 'required|string',
            'villeNaissance' => 'required|string',
            'anneeProduction' => 'required|string',
            'dateCirculation' => 'required|string',
            'cylindree' => 'required|string',
            'num_immatriculation_precedant' => 'required|string',
            'codeRegion' => 'required|string',
            'sex' => 'required|string',
        ];
        $messages = [
            'DateNaissance.required' => 'La date de naissance est obligatoire.',
            'DateNaissance.string' => 'La date de naissance doit être une chaîne de caractères.',

            'adresse.required' => 'L’adresse est obligatoire.',
            'adresse.string' => 'L’adresse doit être une chaîne de caractères.',

            'carrosserie.required' => 'La carrosserie du véhicule est obligatoire.',
            'carrosserie.string' => 'La carrosserie doit être une chaîne de caractères.',

            'vin.required' => 'Le numéro de châssis (VIN) est obligatoire.',
            'vin.string' => 'Le VIN doit être une chaîne de caractères.',

            'couleurVehicule.required' => 'La couleur du véhicule est obligatoire.',
            'couleurVehicule.string' => 'La couleur du véhicule doit être une chaîne de caractères.',

            'district.string' => 'Le district doit être une chaîne de caractères.',

            'email.email' => 'L’adresse email n’est pas valide.',

            'firstname.required' => 'Le prénom est obligatoire.',
            'firstname.string' => 'Le prénom doit être une chaîne de caractères.',

            'genre.required' => 'Le genre est obligatoire.',
            'genre.string' => 'Le genre doit être une chaîne de caractères.',

            'lastname.required' => 'Le nom de famille est obligatoire.',
            'lastname.string' => 'Le nom doit être une chaîne de caractères.',

            'marqueVehicule.required' => 'La marque du véhicule est obligatoire.',
            'marqueVehicule.array' => 'La marque du véhicule doit être un tableau.',

            'modelVehicule.required' => 'Le modèle du véhicule est obligatoire.',
            'modelVehicule.array' => 'Le modèle du véhicule doit être un tableau.',

            'nombreEssieux.required' => 'Le nombre d’essieux est obligatoire.',
            'nombreEssieux.integer' => 'Le nombre d’essieux doit être un entier.',

            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.string' => 'Le téléphone doit être une chaîne de caractères.',

            'placesAssises.required' => 'Le nombre de places assises est obligatoire.',
            'placesAssises.integer' => 'Le nombre de places assises doit être un entier.',

            'prefecture.string' => 'La préfecture doit être une chaîne de caractères.',

            'ptac.required' => 'Le PTAC est obligatoire.',
            'ptac.integer' => 'Le PTAC doit être un entier.',

            'pu.required' => 'La PU est obligatoire.',
            'pu.integer' => 'La PU doit être un entier.',

            'puissance.required' => 'La puissance est obligatoire.',
            'puissance.string' => 'La puissance doit être une chaîne de caractères.',

            'pv.required' => 'Le PV est obligatoire.',
            'pv.string' => 'Le PV doit être une chaîne de caractères.',

            'region.string' => 'La région doit être une chaîne de caractères.',

            'usage.string' => 'L’usage doit être une chaîne de caractères.',

            'sourcesEnergie.required' => 'La source d’énergie est obligatoire.',
            'sourcesEnergie.string' => 'La source d’énergie doit être une chaîne de caractères.',

            'sousPrefecture.string' => 'La sous-préfecture doit être une chaîne de caractères.',

            'type.required' => 'Le type de véhicule est obligatoire.',
            'type.string' => 'Le type doit être une chaîne de caractères.',

            'typePersonne.required' => 'Le type de personne est obligatoire.',
            'typePersonne.string' => 'Le type de personne doit être une chaîne de caractères.',

            'typeTechnique.required' => 'Le type technique est obligatoire.',
            'typeTechnique.string' => 'Le type technique doit être une chaîne de caractères.',

            'controleurTechnique' => 'Le controleur technique doit être une chaîne de caractères.',
            'controleurTechnique.required' => 'Le controleur technique est obligatoire.',

            'villeNaissance.required' => 'La ville de naissance est obligatoire.',
            'villeNaissance.string' => 'La ville de naissance doit être une chaîne de caractères.',

            'anneeProduction.required' => 'L’année de production est obligatoire.',
            'anneeProduction.string' => 'L’année de production doit être une chaîne de caractères.',

            'dateCirculation.required' => 'La date de première mise en circulation est obligatoire.',
            'dateCirculation.string' => 'La date de circulation doit être une chaîne de caractères.',

            'cylindree.required' => 'La cylindrée est obligatoire.',
            'cylindree.string' => 'La cylindrée doit être une chaîne de caractères.',

            'num_immatriculation_precedant.required' => "Le numéro d'immatriculation précédente est obligatoire.",
            'num_immatriculation_precedant.string' => "Le numéro d'immatriculation précédente doit être une chaîne de caractères.",

            'codeRegion.required' => 'Le code de région est obligatoire.',
            'codeRegion.string' => 'Le code de région doit être une chaîne de caractères.',
        ];
        // Validation des données
        $validated = $request->validate($rules, $messages);
        //je veux verfier s'il n'y a pas dejas de re-immatriculation pour ce vehicule en cours
        $existingDossier = Dossier::where('id_service', 2)
            ->whereHas('r_dossier_vehicule', function ($query) use ($validated) {
                $query->where('vin', $validated['vin']);
            })
            ->whereIn('statut', [1, 4]) // Statuts en cours 1: En attente, 2: Valider, 3: Refuser, 4: En cours	
            ->first();
        if ($existingDossier) {
            return response()->json([
                'success' => false,
                'message' => 'Oups! une ré-immatriculation est déjà en cours pour ce véhicule.'
            ], 409); // 409 Conflict
        }
        // dd($validated);

        try {
            DB::beginTransaction();

            // Création du client
            $client = Client::create([
                'nom' => $validated['firstname'],
                'prenom' => $validated['lastname'],
                'date_naissance' => $validated['DateNaissance'],
                'ville_naissance' => $validated['villeNaissance'],
                'adresse' => $validated['adresse'],
                'telephone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'civilite' => $validated['sex'],
                'district_client' => $validated['district'] ?? null,
                'prefecture_client' => $validated['prefecture'] ?? null,
                'sous_prefecture_client' => $validated['sousPrefecture'] ?? null,
                'password' => generateStrongPassword(8),
                'statut' => 1,
                'validite' => Carbon::now()->addMonths(3),
            ]);

            // Création ou mise à jour de l'entreprise si type Morale
            $entreprise = null;
            if ($validated['type'] !== 'Physique') {
                $entreprise = Entreprise::firstOrCreate(
                    ['registre_commerce' => $request->input('registreCommerce')],
                    [
                        'nom_entreprise' => $request->input('nomEntreprise'),
                        'nom_representant_legal' => $request->input('representantLegal'),
                        'telephone_representant_legal' => $request->input('numeroTelephone'),
                        'compte_contribuale' => $request->input('compteContribuable'),
                        'prefecture' => $request->input('prefecture'),
                        'sous_prefecture' => $request->input('sousPrefecture'),
                        'region' => $request->input('region'),
                        'date_de_naissance_representant_legal' => $request->input('dateNaissanceRepresantant'),
                        'profession_representant_legal' => $request->input('professionRepresantant'),
                    ]
                );
            }
            //récupérer le genre pour nb_plaque
            $nbPlaque = DB::table('genre')
                ->where('nom', $request->input('genre'))
                ->value('nb_plaque');

            // Création ou mise à jour du véhicule
            $vehicule = Vehicule::updateOrCreate(
                // Critère de recherche (ici, le VIN doit être unique)
                ['vin' => $validated['vin']],

                // Données à créer ou mettre à jour
                [
                    'annee_production' => $validated['anneeProduction'],
                    'marque' => $validated['marqueVehicule']['nom'],
                    'modele' => $validated['modelVehicule']['nom'],
                    'couleur' => $validated['couleurVehicule'],
                    'source_energie' => $validated['sourcesEnergie'],
                    'genre_vehicule' => $validated['genre'],
                    'poids_total_charge' => $validated['ptac'],
                    'poids_utile' => $validated['pu'],
                    'poids_vide' => $validated['pv'],
                    'puissance_administrative' => $validated['puissance'],
                    'places_assises' => intval(preg_replace('/\D/', '', $validated['placesAssises'])),
                    'nombre_essieux' => intval(preg_replace('/\D/', '', $validated['nombreEssieux'])),
                    'date_mise_circulation' => $validated['dateCirculation'],
                    'id_site' => getIdSite(),
                    'num_immatriculation_precedant' => $validated['num_immatriculation_precedant'],
                    'carrosserie' => $validated['carrosserie'],
                    'type_technique' => $validated['typeTechnique'],
                    'controleur_technique' => $validated['controleurTechnique'],
                    'code_de_region' => $validated['codeRegion'],
                    'usage_vehicule' => $validated['usage'],
                    'physique_morale' => $validated['type'],
                    'entreprise_id' => $entreprise?->id,
                    'id_client' => $client->id,
                    'model_id' => $validated['modelVehicule']['id'],
                    'marque_id' => $validated['marqueVehicule']['id'],
                    'cylindree' => $validated['cylindree'],
                    'nb_plaque' => $nbPlaque,
                    'gage' => $request->input('choixGage') ?? null,
                ]
            );

            $detail = []; // tableau vide
            $detail[] = $request->input('detail');
            $id_type_service = 0;
            if ($request->input('detail') == 'Ré-immatriculation Diplomatique') {
                $id_type_service = 1;
            } else if ($request->input('detail') == 'Ré-immatriculation Ordinaire') {
                $id_type_service = 2;
            }

            // Création du dossier
            $dossier = Dossier::create([
                'id_vehicule' => $vehicule->id,
                'id_user' => getConnectedUserId(),
                'num_chrono' =>  generateChronoNumber('REIM'),
                'id_client' => $client->id,
                'reserverNumero' => $request->input('reserverNumero') ?? null,
                'numeroDiplomatique' => $request->input('numeroDiplomatique') ?? null,
                'id_service' => 2, // service de réimmatriculation
                'id_type_service' => $id_type_service,
                'id_site' => getIdSite(),
                'detail' => json_encode($detail, JSON_UNESCAPED_UNICODE),
                'statut' => 1,
                'date_creation' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enregistrement réussi.',
                'data' => $dossier,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showReceiptPdcReImmatriculation($id)
    {
        //rechercher le dossier qui a id $id et le retourner
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        $vehicule = $dossier->r_dossier_vehicule;
        $client = $dossier->r_dossier_client;
        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;
        $data = [
            'receipt' => [
                'num_immatriculation' => $vehicule->num_immatriculation,
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
            ],
        ];

        // return response()->json($data);

        return inertia('Pdc/ReImmatriculation/components/receipt', ['dossier' => $dossier, 'entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }

    public function showEditPdcReImmatriculation($id)
    {
        //rechercher le dossier qui a id $id et le retourner
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        $vehicule = $dossier->r_dossier_vehicule;
        // dd($vehicule);
        $client = $dossier->r_dossier_client;

        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;
        // dd($client);
        $data = [
            'immatriculation' => [
                'id' => $vehicule->id,
                'type_personne' => $vehicule->physique_morale,
                'civilite' => $client->civilite ?? null,
                'firstname' => $client->nom ?? null,
                'lastname' => $client->prenom ?? null,
                'phone' => $client->telephone ?? null,
                'adresse' => $client->adresse ?? null,
                'DateNaissance' => $client->date_naissance ?? null,
                'villeNaissance' => $client->ville_naissance ?? null,
                'email' => $client->email ?? null,
                'prefecture_client'  => $client->prefecture_client ?? null,
                'sous_prefecture_client'  => $client->sous_prefecture_client ?? null,
                'district_client' => $client->district_client ?? null,

                // Informations véhicule
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
                'couleurVehicule' => $vehicule->couleur,
                'carrosserie' => $vehicule->carrosserie,
                'typeTechnique' => $vehicule->type_technique,
                'typePersonne' => $vehicule->physique_morale,
                'type' =>  $vehicule->physique_morale,
                'genre' => $vehicule->genre_vehicule,
                'ptac' => $vehicule->poids_total_charge,
                'pu' => $vehicule->poids_utile,
                'pv' => $vehicule->poids_vide,
                'puissance' => $vehicule->puissance_administrative,
                'placesAssises' => $vehicule->places_assises,
                'usage' => $vehicule->usage_vehicule,
                'sourcesEnergie' => $vehicule->source_energie,
                'nombreEssieux' => $vehicule->nombre_essieux,
                'codeRegion' => $vehicule->code_de_region,
                'DateCirculation' => $vehicule->date_mise_circulation,
                'AnneeProduction' => $vehicule->annee_production,
                'num_immatriculation_precedant' => $vehicule->num_immatriculation_precedant,
                // Informations entreprise (si morale)
                'nomEntreprise' => $entreprise->nom_entreprise ?? null,
                'registreCommerce' => $entreprise->registre_commerce ?? null,
                'representantLegal' => $entreprise->nom_representant_legal ?? null,
                'numeroTelephone' => $entreprise->telephone_representant_legal ?? null,
                'compteContribuable' => $entreprise->compte_contribuale ?? null,
                'DateNaissanceRepresantant' => $entreprise->date_de_naissance_representant_legal ?? null,
                'ProfessionRepresantant' => $entreprise->profession_representant_legal ?? null,
                'prefecture' => $entreprise->prefecture ?? null,
                'sousPrefecture' => $entreprise->sous_prefecture ?? null,
                'region' =>  null,
                // 'marqueVehicule' => $vehicule->marque, // Adapté à votre structure
            ],
            'marques' => DB::table('marque')->get(), // Vous devrez importer le modèle Marque
            'modeles' => $vehicule->model_id ? DB::table('model')->where('marque_id', $vehicule->marque_id)->get() : []
        ];

        // return response()->json($data);

        return inertia('Pdc/ReImmatriculation/components/editForm', ['dossier' => $dossier, 'entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }

    public function showViewPdcReImmatriculation($id)
    {
        //rechercher le dossier qui a id $id et le retourner

        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        $vehicule = $dossier->r_dossier_vehicule;

        $client = $dossier->r_dossier_client;

        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;
        // dd($client);
        $data = [
            'immatriculation' => [
                'id' => $vehicule->id,
                'type_personne' => $vehicule->physique_morale,
                'civilite' => $client->civilite ?? null,
                'firstname' => $client->nom ?? null,
                'lastname' => $client->prenom ?? null,
                'phone' => $client->telephone ?? null,
                'adresse' => $client->adresse ?? null,
                'DateNaissance' => $client->date_naissance ?? null,
                'villeNaissance' => $client->ville_naissance ?? null,
                'email' => $client->email ?? null,
                'prefecture_client'  => $client->prefecture_client ?? null,
                'sous_prefecture_client'  => $client->sous_prefecture_client ?? null,
                'district_client' => $client->district_client ?? null,


                // Informations véhicule
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
                'couleurVehicule' => $vehicule->couleur,
                'carrosserie' => $vehicule->carrosserie,
                'typeTechnique' => $vehicule->type_technique,
                'typePersonne' => $vehicule->physique_morale,
                'type' =>  $vehicule->physique_morale,
                'genre' => $vehicule->genre_vehicule,
                'ptac' => $vehicule->poids_total_charge,
                'pu' => $vehicule->poids_utile,
                'pv' => $vehicule->poids_vide,
                'puissance' => $vehicule->puissance_administrative,
                'placesAssises' => $vehicule->places_assises,
                'usage' => $vehicule->usage_vehicule,
                'sourcesEnergie' => $vehicule->source_energie,
                'nombreEssieux' => $vehicule->nombre_essieux,
                'codeRegion' => $vehicule->code_de_region,
                'DateCirculation' => $vehicule->date_mise_circulation,
                'AnneeProduction' => $vehicule->annee_production,
                'num_immatriculation_precedant' => $vehicule->num_immatriculation_precedant,
                'cylindree' => $vehicule->cylindree,


                // Informations entreprise (si morale)
                'nomEntreprise' => $entreprise->nom_entreprise ?? null,
                'registreCommerce' => $entreprise->registre_commerce ?? null,
                'representantLegal' => $entreprise->nom_representant_legal ?? null,
                'numeroTelephone' => $entreprise->telephone_representant_legal ?? null,
                'compteContribuable' => $entreprise->compte_contribuale ?? null,
                'DateNaissanceRepresantant' => $entreprise->date_de_naissance_representant_legal ?? null,
                'ProfessionRepresantant' => $entreprise->profession_representant_legal ?? null,
                'prefecture' => $entreprise->prefecture ?? null,
                'sousPrefecture' => $entreprise->sous_prefecture ?? null,
                // 'marqueVehicule' => $vehicule->marque, // Adapté à votre structure
            ],
            'marques' => DB::table('marque')->get(), // Vous devrez importer le modèle Marque
            'modeles' => $vehicule->model_id ? DB::table('model')->where('marque_id', $vehicule->marque_id)->get() : []
        ];

        // return response()->json($data);

        return inertia('Pdc/ReImmatriculation/components/viewForm', ['dossier' => $dossier, 'entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }

    public function updatePdcReImmatriculationData(Request $request, $id)
    {
        // dd($request->all());
        $typePersonne = $request->input('type');
        $rules = PdcImmatriculationRules::rules($typePersonne);
        $messages = PdcImmatriculationMessages::messages($typePersonne);

        $validated = $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            $vehicule = Vehicule::findOrFail($id);
            $client = $vehicule->r_vehicule_client;
            $entreprise = $vehicule->r_vehicule_entreprise;

            $client->update([
                'nom' => $request->input('firstname'),
                'prenom' => $request->input('lastname'),
                'date_naissance' => $request->input('DateNaissance'),
                'ville_naissance' => $request->input('villeNaissance'),
                'adresse' => $request->input('adresse'),
                'telephone' => $request->input('phone'),
                'email' => $request->input('email'),
                'civilite' => $request->input('sex'),
                'district_client' => $request->input('districtClient'),
                'prefecture_client'  => $request->input('prefecture_client'),
                'sous_prefecture_client'  => $request->input('sousPrefectureClient'),
            ]);

            $modificationsEntreprise = [];

            if ($request->typePersonne != 'Physique') {
                $donneesEntreprise = [
                    'nom_entreprise' => $request->input('nomEntreprise'),
                    'nom_representant_legal' => $request->input('representantLegal'),
                    'telephone_representant_legal' => $request->input('numeroTelephone'),
                    'registre_commerce' => $request->input('registreCommerce'),
                    'compte_contribuale' => $request->input('compteContribuable'),
                    'prefecture' => $request->input('prefecture'),
                    'sous_prefecture' => $request->input('sousPrefecture'),
                    'region' => $request->input('region'),
                    'date_de_naissance_representant_legal' => $request->input('dateNaissanceRepresantant'),
                    'profession_representant_legal' => $request->input('professionRepresantant'),
                ];

                if (!$entreprise) {
                    $entreprise = Entreprise::create($donneesEntreprise);
                } else {
                    $entreprise->fill($donneesEntreprise);
                    $modificationsEntreprise = $entreprise->getDirty();
                    $entreprise->update($donneesEntreprise);
                }
            }

            $vehicule->update([
                'annee_production' => $request->input('anneeProduction'),
                'vin' => $request->input('vin'),
                'marque' => $request->marqueVehicule['nom'],
                'modele' => $request->modelVehicule['nom'],
                'couleur' => $request->input('couleurVehicule'),
                'source_energie' => $request->input('sourcesEnergie'),
                'genre_vehicule' => $request->input('genre'),
                'poids_total_charge' => $request->input('ptac'),
                'poids_utile' => $request->input('pu'),
                'poids_vide' => $request->input('pv'),
                'puissance_administrative' => $request->input('puissance'),
                'places_assises' => intval(preg_replace('/\D/', '', $request->input('placesAssises'))),
                'nombre_essieux' => intval(preg_replace('/\D/', '', $request->input('nombreEssieux'))),
                'date_mise_circulation' => $request->input('dateCirculation'),
                'carrosserie' => $request->input('carrosserie'),
                'type_technique' => $request->input('typeTechnique'),
                'code_de_region' => $request->input('codeRegion'),
                'usage_vehicule' => $request->input('usage'),
                'physique_morale' => $request->input('type'),
                'model_id' => $request->modelVehicule['id'],
                'marque_id' => $request->marqueVehicule['id'],
                'entreprise_id' => $entreprise ? $entreprise->id : null,
                'num_immatriculation_precedant' => $request->num_immatriculation_precedant,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Les informations ont été mises à jour avec succès.',
                'data' => ['modificationsEntreprise' => $modificationsEntreprise, 'vehicule' => $vehicule]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    //new re-immatriculation
    public function showNewPdcReImmatriculation()
    {
        $typeServices = DB::table('type_services')->where('id_service', 2)->get();
        return inertia('Pdc/ReImmatriculation/new', compact('typeServices'));
    }

    //add re-immatriculation
    public function showNewPdcReImmatriculationAddData($vin)
    {
        // dd($vin);
        // dd('Rti/Immatriculation/new/' . $id);
        return inertia('Pdc/ReImmatriculation/components/createForm', ['vin' => $vin]);
    }

    //-----------------post immatriculation---------------//

    //show post immatriculation
    public function showPdcPostImmatriculation()
    {
        return inertia('Pdc/PostImmatriculation/index');
    }

    public function showPdcPostImmatriculationSelect()
    {
        return inertia('Pdc/PostImmatriculation/selectService');
    }


    //get post immatriculation data
    public function getPdcPostImmatriculationData(Request $request)
    {
        $query = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ])->where('id_service', 3);

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
    //new post-immatriculation
    public function showNewPdcPostImmatriculation()
    {

        $typeServices = DB::table('type_services')->where('id_service', 3)->get();
        return inertia('Pdc/PostImmatriculation/new', compact('typeServices'));
    }
    //add post-immatriculation
    public function showNewPdcPostImmatriculationAddData($vin)
    {
        // 
        $vehicule = Vehicule::where('vin', $vin)->first();
        if (!$vehicule) {
            abort(404, 'Véhicule non rencontré');
        }

        $client = $vehicule->r_vehicule_client;
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;
        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;

        $data = [
            'immatriculation' => [
                'id' => $vehicule->id,
                'type_personne' => $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'civilite' => $client->civilite ?? null,
                'firstname' => $client->nom ?? null,
                'lastname' => $client->prenom ?? null,
                'phone' => $client->telephone ?? null,
                'adresse' => $client->adresse ?? null,
                'DateNaissance' => $client->date_naissance ?? null,
                'villeNaissance' => $client->ville_naissance ?? null,
                'email' => $client->email ?? null,
                'district' => $client->district_client ?? null,
                'prefecture' => $entreprise->prefecture ?? null,
                'sousPrefecture' => $entreprise->sous_prefecture ?? null,
                'region' =>  null,

                // Informations véhicule
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
                'couleurVehicule' => $vehicule->couleur,
                'carrosserie' => $vehicule->carrosserie,
                'typeTechnique' => $vehicule->type_technique,
                'typePersonne' => $vehicule->physique_morale,
                'type' =>  $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'genre' => $vehicule->genre_vehicule,
                'ptac' => $vehicule->poids_total_charge,
                'pu' => $vehicule->poids_utile,
                'pv' => $vehicule->poids_vide,
                'puissance' => $vehicule->puissance_administrative,
                'placesAssises' => $vehicule->places_assises,
                'usage' => $vehicule->usage_vehicule,
                'sourcesEnergie' => $vehicule->source_energie,
                'nombreEssieux' => $vehicule->nombre_essieux,
                'codeRegion' => $vehicule->code_de_region,
                'DateCirculation' => $vehicule->date_mise_circulation,
                'AnneeProduction' => $vehicule->annee_production,

                // Informations entreprise (si morale)
                'nomEntreprise' => $entreprise->nom_entreprise ?? null,
                'registreCommerce' => $entreprise->registre_commerce ?? null,
                'representantLegal' => $entreprise->nom_representant_legal ?? null,
                'numeroTelephone' => $entreprise->telephone_representant_legal ?? null,
                'compteContribuable' => $entreprise->compte_contribuale ?? null,
                'DateNaissanceRepresantant' => $entreprise->date_de_naissance_representant_legal ?? null,
                'ProfessionRepresantant' => $entreprise->profession_representant_legal ?? null,
                'gage' => $vehicule->gage ?? null,
            ],
            'marques' => DB::table('marque')->get(), // Vous devrez importer le modèle Marque
            'modeles' => $vehicule->model_id ? DB::table('model')->where('marque_id', $vehicule->marque_id)->get() : []
        ];

        return inertia('Pdc/PostImmatriculation/components/editForm', ['entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }

    public function showPdcPostImmatriculationModificationLog1($id)
    {
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);
        $log_id = $dossier->modification_log_id;
        $log = DB::table('modification_logs')->where('id', $log_id)->first();
        if ($log) {
            $old = json_decode($log->old_values, true);
            $new = json_decode($log->new_values, true);
        } else {
            $old = $new = [];
        }

        return inertia('Pdc/PostImmatriculation/components/viewLog', ['log' => $log, 'old' => $old, 'new' => $new]);
    }

    public function showPdcPostImmatriculationModificationLog($id)
    {
        $dossiers = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions',
        ])->findOrFail($id);

        if (!$dossiers) {
            abort(404, 'Dossier non trouvé');
        }
        $log_id = $dossiers->modification_log_id;
        $log = DB::table('modification_logs')->where('id', $log_id)->first();
        if ($log) {
            $old = json_decode($log->old_values, true);
            $new = json_decode($log->new_values, true);
        } else {
            $old = $new = [];
        }

        return inertia('Pdc/PostImmatriculation/components/viewLog', [
            'dossier' => $dossiers,
            'log' => $log,
            'old' => $old,
            'new' => $new
        ]);
    }


    public function savePostImmatriculationDraft(Request $request, $vehiculeId)
    {
        $typePersonne = $request->input('typePersonne');

        // Récupération des modèles
        $vehicule   = Vehicule::findOrFail($vehiculeId);
        $client     = $vehicule->r_vehicule_client;
        $entreprise = $vehicule->r_vehicule_entreprise;

        // Préparer les données comme avant
        $donneesClient = [
            'nom'             => $request->input('firstname'),
            'prenom'          => $request->input('lastname'),
            'date_naissance'  => $request->input('DateNaissance'),
            'ville_naissance' => $request->input('villeNaissance'),
            'adresse'         => $request->input('adresse'),
            'telephone'       => $request->input('phone'),
            'email'           => $request->input('email'),
            'civilite'        => $request->input('sex'),
        ];

        $donneesEntreprise = null;
        if ($typePersonne != 'Physique') {
            $donneesEntreprise = [
                'nom_entreprise' => $request->input('nomEntreprise'),
                'nom_representant_legal' => $request->input('representantLegal'),
                'telephone_representant_legal' => $request->input('numeroTelephone'),
                'registre_commerce' => $request->input('registreCommerce'),
                'compte_contribuale' => $request->input('compteContribuable'),
                'prefecture' => $request->input('prefecture'),
                'sous_prefecture' => $request->input('sousPrefecture'),
                'region' => $request->input('region'),
                'date_de_naissance_representant_legal' => $request->input('dateNaissanceRepresantant'),
                'profession_representant_legal' => $request->input('professionRepresantant'),
            ];
        }

        $donneesVehicule = [
            'annee_production' => $request->input('anneeProduction'),
            'vin' => $request->input('vin'),
            'marque' => $request->marqueVehicule['nom'],
            'modele' => $request->modelVehicule['nom'],
            'couleur' => $request->input('couleurVehicule'),
            'source_energie' => $request->input('sourcesEnergie'),
            'genre_vehicule' => $request->input('genre'),
            'poids_total_charge' => $request->input('ptac'),
            'poids_utile' => $request->input('pu'),
            'poids_vide' => $request->input('pv'),
            'puissance_administrative' => $request->input('puissance'),
            'places_assises' => intval(preg_replace('/\D/', '', $request->input('placesAssises'))),
            'nombre_essieux' => intval(preg_replace('/\D/', '', $request->input('nombreEssieux'))),
            'date_mise_circulation' => $request->input('dateCirculation'),
            'carrosserie' => $request->input('carrosserie'),
            'type_technique' => $request->input('typeTechnique'),
            'code_de_region' => $request->input('codeRegion'),
            'usage_vehicule' => $request->input('usage'),
            'physique_morale' => $typePersonne,
            'model_id' => $request->modelVehicule['id'],
            'marque_id' => $request->marqueVehicule['id'],
            'entreprise_id' => $typePersonne != 'Physique' && $entreprise ? $entreprise->id : null,
            'gage' => $request->input('gage') === 'gage' ? 'gage' : null,
        ];

        // Création du log
        $modification_log = ModificationLog::create([
            'model_type' => 'global',
            'model_id'   => $vehicule->id,
            'user_id'    => getConnectedUserId(),
            'old_values' => json_encode([
                'vehicule' => $vehicule->toArray(),
                'client' => $client->toArray(),
                'entreprise' => $entreprise ? $entreprise->toArray() : null,
            ], JSON_UNESCAPED_UNICODE),
            'new_values' => json_encode([
                'vehicule' => $donneesVehicule,
                'client' => $donneesClient,
                'entreprise' => $donneesEntreprise,
            ], JSON_UNESCAPED_UNICODE),
        ]);

        // Créer le dossier directement
        $selected = $request->selected ?? [];
        $mutation = $request->mutation ?? [];
        if (!is_array($mutation)) $mutation = [$mutation];
        $detail = array_merge($selected, $mutation);

        $dossier = Dossier::create([
            'id_vehicule'        => $vehicule->id,
            'id_user'            => getConnectedUserId(),
            'id_client'          => $client->id,
            'id_service'         => 3, // post immatriculation
            'detail'             => json_encode($detail, JSON_UNESCAPED_UNICODE),
            'id_site'            => getIdSite(),
            'modification_log_id' => $modification_log->id,
            'statut'             => 1, // en cours
            'num_chrono'         => generateChronoNumber('POST'),
            'date_creation'      => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informations enregistré avec success.',
            'modification_log_id' => $modification_log->id,
            'dossier_id' => $dossier->id,
        ]);
    }




    public function validatePostImmatriculation($modificationLogId)
    {
        $log = ModificationLog::findOrFail($modificationLogId);
        $newValues = json_decode($log->new_values, true);

        DB::beginTransaction();
        try {
            // Mise à jour client
            $client = Client::findOrFail($newValues['client']['id'] ?? null);
            $client->update($newValues['client']);

            // Mise à jour entreprise si nécessaire
            if (isset($newValues['entreprise'])) {
                $entreprise = Entreprise::find($newValues['entreprise']['id'] ?? null);
                if ($entreprise) {
                    $entreprise->update($newValues['entreprise']);
                } else {
                    $entreprise = Entreprise::create($newValues['entreprise']);
                }
            }

            // Mise à jour véhicule
            $vehicule = Vehicule::findOrFail($newValues['vehicule']['id']);
            $vehicule->update($newValues['vehicule']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Données réelles mises à jour avec succès.',
                'vehicule' => $vehicule,
                'client' => $client,
                'entreprise' => $entreprise ?? null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function showReceiptPdcPostImmatriculation($id)
    {
        //rechercher le dossier qui a id $id et le retourner
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        $vehicule = $dossier->r_dossier_vehicule;
        $client = $dossier->r_dossier_client;
        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;
        $data = [
            'receipt' => [
                'num_immatriculation' => $vehicule->num_immatriculation,
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
            ],
        ];

        // return response()->json($data);

        return inertia('Pdc/PostImmatriculation/components/receipt', ['dossier' => $dossier, 'entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }

    //------------------duplicata----------------------//
    //show duplicata
    public function showPdcDuplicata()
    {
        return inertia('Pdc/Duplicata/index');
    }

    public function showPdcDuplicataSelect()
    {
        return inertia('Pdc/Duplicata/selectService');
    }
    //get immatriculation data
    public function getPdcDuplicataData(Request $request)
    {
        $query = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_documents',
            'r_dossier_services',
            'r_dossier_services.r_service_types',
            'r_dossier_transactions'
        ])->where('id_service', 4);

        $filtre_per_page = $request->input('filtre_per_page', 10);
        $statut = $request->input('statut');
        $filtre_type = $request->input('filtre_type');
        $search_data = $request->input('search_data');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');

        // Filtre par VIN (via search_data)
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

    //savePdcImmatriculationData
    // Assurez-vous d'importer le modèle Client icila1

    public function savePdcDuplicataData(Request $request)
    {

        // Récupération des blocs de données
        $postImtData  = $request->input('postImtData') ?? [];
        $payload      = $request->input('payload') ?? []; // vehicule_id, client_id, detail
        $vehicule_id  = $payload['vehicule_id'] ?? null;
        $detailPayload = $payload['detail'] ?? [];
        $client_id    = $payload['client_id'] ?? null;

        $selected = $postImtData['selected'] ?? [];
        $mutation = $postImtData['mutation'] ?? null;

        // Validation du payload
        $validated = $request->validate([
            'payload.vehicule_id' => 'required|integer|exists:vehicules,id',
            'payload.detail'      => 'required|array|min:1',
            'payload.client_id'   => 'required|integer|exists:clients,id',
        ], [
            'payload.vehicule_id.required' => 'Le véhicule est obligatoire.',
            'payload.vehicule_id.integer'  => 'L\'ID du véhicule doit être un entier.',
            'payload.vehicule_id.exists'   => 'Le véhicule sélectionné est introuvable.',

            'payload.detail.required' => 'Les détails du service sont requis.',
            'payload.detail.array'    => 'Le format des détails du service est invalide.',
            'payload.detail.min'      => 'Au moins un service doit être sélectionné.',

            'payload.client_id.required' => 'Le client est obligatoire.',
            'payload.client_id.integer'  => 'L\'ID du client doit être un entier.',
            'payload.client_id.exists'   => 'Le client sélectionné est introuvable.',
        ]);

        //ajouter validation de Duplicata si elle existe verifier avec vehicule_id

        $existingDossier = Dossier::where('id_service', 4) // 4 pour Duplicata
            ->whereHas('r_dossier_vehicule', function ($query) use ($validated) {
                $query->where('id', $validated['payload']['vehicule_id']);
            })
            ->whereIn('statut', [1, 4]) // Statuts en cours 1: En attente, 2: Valider, 3: Refuser, 4: En cours	
            ->first();
        if ($existingDossier) {
            return response()->json([
                'success' => false,
                'message' => 'Oups! un duplicata est déjà en cours pour ce véhicule.'
            ], 409); // 409 Conflict
        }
        // dd($request->all());
        try {
            DB::beginTransaction();

            $vehicule = Vehicule::findOrFail($vehicule_id);
            $client   = $vehicule->r_vehicule_client;
            $entreprise = $vehicule->r_vehicule_entreprise;

            $dossierPost = null;
            $modification_log = null;

            // --------------------------------------------------------------------
            //  PARTIE POST-IMMATRICULATION — EXECUTÉE SEULEMENT SI postImtData NON VIDE
            // --------------------------------------------------------------------
            if (!empty($postImtData)) {

                $typePersonne = $postImtData['typePersonne'] ?? 'Physique';

                // === Mise à jour CLIENT ===
                $donneesClient = [
                    'nom' => $postImtData['firstname'],
                    'prenom' => $postImtData['lastname'],
                    'date_naissance' => $postImtData['DateNaissance'],
                    'ville_naissance' => $postImtData['villeNaissance'],
                    'adresse' => $postImtData['adresse'],
                    'telephone' => $postImtData['phone'],
                    'email' => $postImtData['email'],
                    'civilite' => $postImtData['sex'],
                ];

                $client->fill($donneesClient);
                $oldClientData = $client->getOriginal();
                $client->update($donneesClient);

                // === Mise à jour ENTREPRISE (si personne morale)
                $oldEntrepriseData = null;
                $donneesEntreprise = null;

                if ($typePersonne != 'Physique') {
                    $donneesEntreprise = [
                        'nom_entreprise' => $postImtData['nomEntreprise'],
                        'nom_representant_legal' => $postImtData['representantLegal'],
                        'telephone_representant_legal' => $postImtData['numeroTelephone'],
                        'registre_commerce' => $postImtData['registreCommerce'],
                        'compte_contribuale' => $postImtData['compteContribuable'],
                        'prefecture' => $postImtData['prefecture'],
                        'sous_prefecture' => $postImtData['sousPrefecture'],
                        'region' => $postImtData['region'],
                        'date_de_naissance_representant_legal' => $postImtData['dateNaissanceRepresantant'],
                        'profession_representant_legal' => $postImtData['professionRepresantant'],
                    ];

                    if (!$entreprise) {
                        $entreprise = Entreprise::create($donneesEntreprise);
                    } else {
                        $entreprise->fill($donneesEntreprise);
                        $oldEntrepriseData = $entreprise->getOriginal();
                        $entreprise->update($donneesEntreprise);
                    }
                }

                // === Mise à jour VEHICULE ===
                $donneesVehicule = [
                    'annee_production' => $postImtData['anneeProduction'] ?? null,
                    'vin'               => $postImtData['vin'] ?? null,
                    'marque'            => $postImtData['marqueVehicule']['nom'] ?? null,
                    'modele'            => $postImtData['modelVehicule']['nom'] ?? null,
                    'couleur'           => $postImtData['couleurVehicule'] ?? null,
                    'source_energie'    => $postImtData['sourcesEnergie'] ?? null,
                    'genre_vehicule'    => $postImtData['genre'] ?? null,
                    'poids_total_charge' => $postImtData['ptac'] ?? null,
                    'poids_utile'       => $postImtData['pu'] ?? null,
                    'poids_vide'        => $postImtData['pv'] ?? null,
                    'puissance_administrative' => $postImtData['puissance'] ?? null,
                    'places_assises' => intval(preg_replace('/\D/', '', $postImtData['placesAssises'] ?? '0')),
                    'nombre_essieux' => intval(preg_replace('/\D/', '', $postImtData['nombreEssieux'] ?? '0')),
                    'date_mise_circulation' => $postImtData['dateCirculation'] ?? null,
                    'carrosserie'           => $postImtData['carrosserie'] ?? null,
                    'type_technique'        => $postImtData['typeTechnique'] ?? null,
                    'code_de_region'        => $postImtData['codeRegion'] ?? null,
                    'usage_vehicule'        => $postImtData['usage'] ?? null,
                    'physique_morale' => $typePersonne,
                    'model_id'  => $postImtData['modelVehicule']['id'] ?? null,
                    'marque_id' => $postImtData['marqueVehicule']['id'] ?? null,
                    'entreprise_id' => $typePersonne != 'Physique' && $entreprise ? $entreprise->id : null,
                ];

                $vehicule->fill($donneesVehicule);
                $oldVehiculeData = $vehicule->getOriginal();
                $vehicule->update($donneesVehicule);

                if ($typePersonne === 'Physique' && $vehicule->entreprise_id !== null) {
                    $vehicule->entreprise_id = null;
                    $vehicule->save();
                }

                // === Journal global ===
                $modification_log = ModificationLog::create([
                    'model_type' => 'global',
                    'model_id' => $vehicule->id,
                    'user_id' => getConnectedUserId(),
                    'old_values' => json_encode([
                        'vehicule' => $oldVehiculeData,
                        'client' => $oldClientData,
                        'entreprise' => $typePersonne != 'Physique' ? $oldEntrepriseData : null,
                    ], JSON_UNESCAPED_UNICODE),
                    'new_values' => json_encode([
                        'vehicule' => $donneesVehicule,
                        'client' => $donneesClient,
                        'entreprise' => $typePersonne != 'Physique' ? $donneesEntreprise : null,
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                // === Création du dossier Post-Immatriculation ===
                if (!is_array($mutation)) {
                    $mutation = [$mutation];
                }

                $detail = array_merge($selected, $mutation);

                $dossierPost = new Dossier();
                $dossierPost->id_vehicule = $vehicule->id;
                $dossierPost->id_user = getConnectedUserId();
                $dossierPost->id_client = $client->id;
                $dossierPost->id_service = 3;
                $dossierPost->detail = json_encode($detail, JSON_UNESCAPED_UNICODE);
                $dossierPost->id_site = getIdSite();
                $dossierPost->modification_log_id = $modification_log->id;
                $dossierPost->statut = 1;
                $dossierPost->num_chrono = generateChronoNumber('POST');
                $dossierPost->date_creation = now();
                $dossierPost->save();
            }

            // --------------------------------------------------------------------
            //  PARTIE DUPLICATA — S’EXÉCUTE TOUJOURS
            // --------------------------------------------------------------------
            $detailDuplicata = !empty($detailPayload) ? $detailPayload : $detail ?? [];

            $dossierDuplicata = new Dossier();
            $dossierDuplicata->id_vehicule   = $vehicule->id;
            $dossierDuplicata->id_user       = getConnectedUserId();
            $dossierDuplicata->num_chrono    = generateChronoNumber('DUPL');
            $dossierDuplicata->id_client     = $client_id ?? $client->id;
            $dossierDuplicata->id_site       = getIdSite();
            $dossierDuplicata->detail        = json_encode($detailDuplicata, JSON_UNESCAPED_UNICODE);
            $dossierDuplicata->id_service    = 4;
            $dossierDuplicata->statut        = 1;
            $dossierDuplicata->id_dossier_lier = $dossierPost ? $dossierPost->id : null;
            $dossierDuplicata->date_creation = now();
            $dossierDuplicata->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enregistrement réussi.',
                'data'    => $dossierDuplicata,
            ], 201);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function updatePdcDuplicataData(Request $request, $id)
    {
        $typePersonne = $request->input('typePersonne');
        $rules = PdcImmatriculationRules::rules($typePersonne);
        $messages = PdcImmatriculationMessages::messages($typePersonne);

        $validated = $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            $vehicule = Vehicule::findOrFail($id);
            $client = $vehicule->r_vehicule_client;
            $entreprise = $vehicule->r_vehicule_entreprise;

            $client->update([
                'nom' => $request->input('firstname'),
                'prenom' => $request->input('lastname'),
                'date_naissance' => $request->input('DateNaissance'),
                'ville_naissance' => $request->input('villeNaissance'),
                'adresse' => $request->input('adresse'),
                'telephone' => $request->input('phone'),
                'email' => $request->input('email'),
                'civilite' => $request->input('sex'),
            ]);

            $modificationsEntreprise = [];

            if ($request->typePersonne === 'Morale') {
                $donneesEntreprise = [
                    'nom_entreprise' => $request->input('nomEntreprise'),
                    'nom_representant_legal' => $request->input('representantLegal'),
                    'telephone_representant_legal' => $request->input('numeroTelephone'),
                    'registre_commerce' => $request->input('registreCommerce'),
                    'compte_contribuale' => $request->input('compteContribuable'),
                    'prefecture' => $request->input('prefecture'),
                    'sous_prefecture' => $request->input('sousPrefecture'),
                    'region' => $request->input('region'),
                    'date_de_naissance_representant_legal' => $request->input('dateNaissanceRepresantant'),
                    'profession_representant_legal' => $request->input('professionRepresantant'),
                ];

                if (!$entreprise) {
                    $entreprise = Entreprise::create($donneesEntreprise);
                } else {
                    $entreprise->fill($donneesEntreprise);
                    $modificationsEntreprise = $entreprise->getDirty();
                    $entreprise->update($donneesEntreprise);
                }
            }

            $vehicule->update([
                'annee_production' => $request->input('anneeProduction'),
                'vin' => $request->input('vin'),
                'marque' => $request->marqueVehicule['nom'],
                'modele' => $request->modelVehicule['nom'],
                'couleur' => $request->input('couleurVehicule'),
                'source_energie' => $request->input('sourcesEnergie'),
                'genre_vehicule' => $request->input('genre'),
                'poids_total_charge' => $request->input('ptac'),
                'poids_utile' => $request->input('pu'),
                'poids_vide' => $request->input('pv'),
                'puissance_administrative' => $request->input('puissance'),
                'places_assises' => intval(preg_replace('/\D/', '', $request->input('placesAssises'))),
                'nombre_essieux' => intval(preg_replace('/\D/', '', $request->input('nombreEssieux'))),
                'date_mise_circulation' => $request->input('dateCirculation'),
                'carrosserie' => $request->input('carrosserie'),
                'type_technique' => $request->input('typeTechnique'),
                'code_de_region' => $request->input('codeRegion'),
                'usage_vehicule' => $request->input('usage'),
                'physique_morale' => $request->input('typePersonne') === 'Morale' ? 2 : 1,
                'model_id' => $request->modelVehicule['id'],
                'marque_id' => $request->marqueVehicule['id'],
                'entreprise_id' => $entreprise ? $entreprise->id : null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Les informations ont été mises à jour avec succès.',
                'data' => ['modificationsEntreprise' => $modificationsEntreprise, 'vehicule' => $vehicule]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //edit duplicata
    public function showEditDuplicata($id)
    {
        //rechercher le dossier qui a id $id et le retourner
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        $vehicule = $dossier->r_dossier_vehicule;
        $client = $dossier->r_dossier_client;

        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;

        $data = [
            'immatriculation' => [
                'id' => $vehicule->id,
                'type_personne' => $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'civilite' => $client->civilite ?? null,
                'firstname' => $client->nom ?? null,
                'lastname' => $client->prenom ?? null,
                'phone' => $client->telephone ?? null,
                'adresse' => $client->adresse ?? null,
                'DateNaissance' => $client->date_naissance ?? null,
                'villeNaissance' => $client->ville_naissance ?? null,
                'email' => $client->email ?? null,
                'district' => $client->district_client ?? null,


                // Informations véhicule
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
                'couleurVehicule' => $vehicule->couleur,
                'carrosserie' => $vehicule->carrosserie,
                'typeTechnique' => $vehicule->type_technique,
                'typePersonne' => $vehicule->physique_morale,
                'type' =>  $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'genre' => $vehicule->genre_vehicule,
                'ptac' => $vehicule->poids_total_charge,
                'pu' => $vehicule->poids_utile,
                'pv' => $vehicule->poids_vide,
                'puissance' => $vehicule->puissance_administrative,
                'placesAssises' => $vehicule->places_assises,
                'usage' => $vehicule->usage_vehicule,
                'sourcesEnergie' => $vehicule->source_energie,
                'nombreEssieux' => $vehicule->nombre_essieux,
                'codeRegion' => $vehicule->code_de_region,
                'DateCirculation' => $vehicule->date_mise_circulation,
                'AnneeProduction' => $vehicule->annee_production,

                // Informations entreprise (si morale)
                'nomEntreprise' => $entreprise->nom_entreprise ?? null,
                'registreCommerce' => $entreprise->registre_commerce ?? null,
                'representantLegal' => $entreprise->nom_representant_legal ?? null,
                'numeroTelephone' => $entreprise->telephone_representant_legal ?? null,
                'compteContribuable' => $entreprise->compte_contribuale ?? null,
                'DateNaissanceRepresantant' => $entreprise->date_de_naissance_representant_legal ?? null,
                'ProfessionRepresantant' => $entreprise->profession_representant_legal ?? null,
                'prefecture' => $entreprise->prefecture ?? null,
                'sousPrefecture' => $entreprise->sous_prefecture ?? null,
                'region' =>  null, //ici
                // 'marqueVehicule' => $vehicule->marque, // Adapté à votre structure
            ],
            'marques' => DB::table('marque')->get(), // Vous devrez importer le modèle Marque
            'modeles' => $vehicule->model_id ? DB::table('model')->where('marque_id', $vehicule->marque_id)->get() : []
        ];

        // return response()->json($data);

        return inertia('Pdc/Duplicata/components/editForm', ['dossier' => $dossier, 'entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }
    public function showViewPdcDuplicata($id)
    {
        //rechercher le dossier qui a id $id et le retourner

        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        $vehicule = $dossier->r_dossier_vehicule;
        $client = $dossier->r_dossier_client;

        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;

        $data = [
            'immatriculation' => [
                'id' => $vehicule->id,
                'type_personne' => $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'civilite' => $client->civilite ?? null,
                'firstname' => $client->nom ?? null,
                'lastname' => $client->prenom ?? null,
                'phone' => $client->telephone ?? null,
                'adresse' => $client->adresse ?? null,
                'DateNaissance' => $client->date_naissance ?? null,
                'villeNaissance' => $client->ville_naissance ?? null,
                'email' => $client->email ?? null,
                'district' => $client->district_client ?? null,
                'prefecture' => $entreprise->prefecture ?? null,
                'sousPrefecture' => $entreprise->sous_prefecture ?? null,
                'region' =>  null, //ici

                // Informations véhicule
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
                'couleurVehicule' => $vehicule->couleur,
                'carrosserie' => $vehicule->carrosserie,
                'typeTechnique' => $vehicule->type_technique,
                'typePersonne' => $vehicule->physique_morale,
                'type' =>  $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'genre' => $vehicule->genre_vehicule,
                'ptac' => $vehicule->poids_total_charge,
                'pu' => $vehicule->poids_utile,
                'pv' => $vehicule->poids_vide,
                'puissance' => $vehicule->puissance_administrative,
                'placesAssises' => $vehicule->places_assises,
                'usage' => $vehicule->usage_vehicule,
                'sourcesEnergie' => $vehicule->source_energie,
                'nombreEssieux' => $vehicule->nombre_essieux,
                'codeRegion' => $vehicule->code_de_region,
                'DateCirculation' => $vehicule->date_mise_circulation,
                'AnneeProduction' => $vehicule->annee_production,

                // Informations entreprise (si morale)
                'nomEntreprise' => $entreprise->nom_entreprise ?? null,
                'registreCommerce' => $entreprise->registre_commerce ?? null,
                'representantLegal' => $entreprise->nom_representant_legal ?? null,
                'numeroTelephone' => $entreprise->telephone_representant_legal ?? null,
                'compteContribuable' => $entreprise->compte_contribuale ?? null,
                'DateNaissanceRepresantant' => $entreprise->date_de_naissance_representant_legal ?? null,
                'ProfessionRepresantant' => $entreprise->profession_representant_legal ?? null,
                // 'marqueVehicule' => $vehicule->marque, // Adapté à votre structure
            ],
            'marques' => DB::table('marque')->get(), // Vous devrez importer le modèle Marque
            'modeles' => $vehicule->model_id ? DB::table('model')->where('marque_id', $vehicule->marque_id)->get() : []
        ];

        // return response()->json($data);

        return inertia('Pdc/Duplicata/components/viewForm', ['dossier' => $dossier, 'entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data]);
    }
    public function showReceiptPdcDuplicata($id)
    {
        // Charger le dossier principal
        $dossier = Dossier::with([
            'r_dossier_vehicule',
            'r_dossier_user',
            'r_dossier_client',
            'r_dossier_services',
            'r_dossier_services.r_service_types'
        ])->findOrFail($id);

        // Charger le dossier lié (post-immatriculation) seulement s'il existe
        $dossier_lier = null;

        if (!empty($dossier->id_dossier_lier)) {
            $dossier_lier = Dossier::with([
                'r_dossier_vehicule',
                'r_dossier_user',
                'r_dossier_client',
                'r_dossier_services',
                'r_dossier_services.r_service_types'
            ])->find($dossier->id_dossier_lier); // find() au lieu de findOrFail()
        }

        // Récupération véhicule / client
        $vehicule = $dossier->r_dossier_vehicule;
        $client   = $dossier->r_dossier_client;

        // Charger l’entreprise seulement si elle existe
        $entreprise = $vehicule->entreprise_id
            ? Entreprise::find($vehicule->entreprise_id)
            : null;

        // Structure des données envoyées au reçu
        $data = [
            'receipt' => [
                'num_immatriculation' => $vehicule->num_immatriculation,
                'vin'                 => $vehicule->vin,
                'marqueVehicule'      => [
                    'id'  => $vehicule->marque_id,
                    'nom' => $vehicule->marque,
                ],
                'modelVehicule'       => [
                    'id'  => $vehicule->model_id,
                    'nom' => $vehicule->modele,
                ],
            ],
        ];

        return inertia('Pdc/Duplicata/components/receipt', [
            'dossier'      => $dossier,
            'dossier_lier' => $dossier_lier,
            'entreprise'   => $entreprise,
            'vehicule'     => $vehicule,
            'client'       => $client,
            'data'         => $data,
        ]);
    }


    //new duplicata
    public function showNewPdcDuplicata()
    {
        return inertia('Pdc/Duplicata/new');
    }

    //add duplicata
    public function showNewPdcDuplicataAddData($donne)
    {
        $donne = json_decode(urldecode($donne), true);
        // dd($donne);
        $vin = $donne['vin'];
        $selected = $donne['selected'];
        $postImtData = $donne['postImtData'];
        $vehicule = Vehicule::with([
            'r_vehicule_client',
            'r_vehicule_entreprise',
        ])->where('vin', $vin)->get();

        // dd('Rti/Immatriculation/new/' . $id);
        return inertia('Pdc/Duplicata/components/createForm', ['vehicule' => $vehicule[0], 'selected' => $selected, 'postImtData' => $postImtData]);
    }
    public function showViewPdcDuplicataData($vin)
    {
        // dd($vin);
        // Rechercher le véhicule avec ses relations
        $vehicule = Vehicule::with([
            'r_vehicule_client',
            'r_vehicule_entreprise',
        ])->where('vin', $vin)->first();

        // Si aucun véhicule trouvé, retourner une erreur 404
        if (!$vehicule) {
            abort(404, 'Véhicule non trouvé.');
        }

        // Récupérer le genre associé
        $genre = null;
        if (!empty($vehicule->genre_vehicule)) {
            $genre = DB::table('genre')
                ->where('nom', $vehicule->genre_vehicule)
                ->first();
        }

        // Retourner la vue Inertia avec les données
        return inertia('Pdc/Duplicata/components/detail', [
            'vehicule' => $vehicule,
            'vin' => $vin,
            'data' => [
                'immatriculation' => [
                    ...$vehicule->toArray(),
                    'genre' => $genre,
                ]
            ],
        ]);
    }

    public function showNewPdcDuplicataService($donne)
    {
        // Décodage du JSON envoyé dans l’URL
        $donne = json_decode(urldecode($donne), true);

        // dd($donne['postImtData']);

        // Vérification sécurité : si le JSON n'est pas valide
        if (!$donne || !isset($donne['vinWithGenre'])) {
            abort(400, 'Paramètre invalide');
        }

        // Récupération vin + nb_plaque
        $vinWithGenre = $donne['vinWithGenre'];

        // Séparer vin et nb_plaque via _
        [$vin, $nb_plaque] = explode('_', $vinWithGenre);

        // Convertir en entier
        $nb_plaque = (int) $nb_plaque;

        // Récupérer les services
        $typeServices = DB::table('type_services')
            ->where('id_service', 4)
            ->get();

        // Retour vers Inertia
        return inertia('Pdc/Duplicata/newService', [
            'typeServices' => $typeServices,
            'postImtData' => $donne['postImtData'] ?? null,
            'data' => [
                'vin' => $vin,
                'nb_plaque' => $nb_plaque,
            ],
        ]);
    }

    public function showNewPdcDuplicataPost($vin)
    {
        // dd($vin);
        $typeServices = DB::table('type_services')
            ->where('id_service', 3)
            ->whereNotIn('nom_type_service', ['Changement de Couleur', 'Usage', "Changement de zone (Code région)"])
            ->get();

        return inertia('Pdc/Duplicata/newPost', ['typeServices' => $typeServices, 'vin' => $vin ?? null]);
    }

    public function showNewPdcDuplicataPostImmatriculationAddData($vin)
    {
        // 
        $vehicule = Vehicule::where('vin', $vin)->first();
        if (!$vehicule) {
            abort(404, 'Véhicule non rencontré');
        }

        $client = $vehicule->r_vehicule_client;
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;
        // Récupérer l'entreprise si elle existe
        $entreprise = $vehicule->entreprise_id ? Entreprise::find($vehicule->entreprise_id) : null;

        // On récupère le genre
        $genre = null;

        if (!empty($vehicule->genre_vehicule)) {
            $genre = DB::table('genre')
                ->where('nom', $vehicule->genre_vehicule)
                ->first();
        }

        $data = [
            'immatriculation' => [
                'id' => $vehicule->id,
                'type_personne' => $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'civilite' => $client->civilite ?? null,
                'firstname' => $client->nom ?? null,
                'lastname' => $client->prenom ?? null,
                'phone' => $client->telephone ?? null,
                'adresse' => $client->adresse ?? null,
                'DateNaissance' => $client->date_naissance ?? null,
                'villeNaissance' => $client->ville_naissance ?? null,
                'email' => $client->email ?? null,
                'district' => $client->district_client ?? null,
                'prefecture' => $entreprise->prefecture ?? null,
                'sousPrefecture' => $entreprise->sous_prefecture ?? null,
                'region' =>  null, //ici

                // Informations véhicule
                'vin' => $vehicule->vin,
                'marqueVehicule' => ['id' => $vehicule->marque_id, 'nom' => $vehicule->marque], // Adapté à votre structure
                'modelVehicule' => ['id' => $vehicule->model_id, 'nom' => $vehicule->modele],
                'couleurVehicule' => $vehicule->couleur,
                'carrosserie' => $vehicule->carrosserie,
                'typeTechnique' => $vehicule->type_technique,
                'typePersonne' => $vehicule->physique_morale,
                'type' =>  $vehicule->physique_morale == 1 ? 'Physique' : 'Morale',
                'genre' => $vehicule->genre_vehicule,
                'ptac' => $vehicule->poids_total_charge,
                'pu' => $vehicule->poids_utile,
                'pv' => $vehicule->poids_vide,
                'puissance' => $vehicule->puissance_administrative,
                'placesAssises' => $vehicule->places_assises,
                'usage' => $vehicule->usage_vehicule,
                'sourcesEnergie' => $vehicule->source_energie,
                'nombreEssieux' => $vehicule->nombre_essieux,
                'codeRegion' => $vehicule->code_de_region,
                'DateCirculation' => $vehicule->date_mise_circulation,
                'AnneeProduction' => $vehicule->annee_production,

                // Informations entreprise (si morale)
                'nomEntreprise' => $entreprise->nom_entreprise ?? null,
                'registreCommerce' => $entreprise->registre_commerce ?? null,
                'representantLegal' => $entreprise->nom_representant_legal ?? null,
                'numeroTelephone' => $entreprise->telephone_representant_legal ?? null,
                'compteContribuable' => $entreprise->compte_contribuale ?? null,
                'DateNaissanceRepresantant' => $entreprise->date_de_naissance_representant_legal ?? null,
                'ProfessionRepresantant' => $entreprise->profession_representant_legal ?? null,
                // 'marqueVehicule' => $vehicule->marque, // Adapté à votre structure
            ],
            'marques' => DB::table('marque')->get(), // Vous devrez importer le modèle Marque
            'modeles' => $vehicule->model_id ? DB::table('model')->where('marque_id', $vehicule->marque_id)->get() : []
        ];

        return inertia('Pdc/Duplicata/editPost', ['entreprise' => $entreprise, 'vehicule' => $vehicule, 'client' => $client, 'data' => $data, 'genreVehicule' => $genre]);
    }

    //correction
    //show immatriculation
    public function showPdcCorrection()
    {
        return inertia('Pdc/Correction/index');
    }

    //get immatriculation data
    public function getPdcCorrectionData(Request $request)
    {
        $query = Correction::with([
            'dossier.r_dossier_vehicule',
            'user',
            'site',
        ])->where('origine', 'caisse');

        $filtre_per_page = $request->input('filtre_per_page', 10);
        $statut = $request->input('statut');
        $filtre_type = $request->input('filtre_type');
        $search_data = $request->input('search_data');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');

        // Filtre par VIN (via search_data)
        // if ($search_data) {
        //     $vehicule = Vehicule::where('vin', 'like', "%{$search_data}%")->first();

        //     if ($vehicule) {
        //         $query->where('id_vehicule', $vehicule->id);
        //     } else {
        //         $query->whereRaw('0 = 1'); // Aucun résultat si pas de match
        //     }
        // }
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














    //gestion sites
    public function showPdcGestionSites()
    {
        return inertia('Pdc/GestionSite');
    }

    //gestion users
    public function showPdcGestionUsers()
    {
        return inertia('Pdc/GestionUtilisateur');
    }

    //**************** FIN POOL DE CONTROLE *****************//
}
