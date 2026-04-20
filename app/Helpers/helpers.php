<?php

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Caisse;
use App\Models\DetailTypeService;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


function generateImmatriculation($prefix = 'CI', $length = 8)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomPart = '';

    for ($i = 0; $i < $length; $i++) {
        $randomPart .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $prefix . '-' . $randomPart;
}


function getIdSite()
{
    $user = Auth::user();
    return $user->id_site;
}

function getIdCaisse()
{
    $site_id = getIdSite();
    $caisse = Caisse::where('site_id', $site_id)->first();
    return $caisse->id;
}
function getConnectedUserId()
{
    $user = Auth::user();
    return $user->id;
}

function getConnectedUserRole()
{
    $user = Auth::user();
    $role = $user->r_user_role->nom_role;
    return $role;
}


function getUserWithRelation($id)
{
    $user = User::with(["r_user_role", "r_user_site", 'r_user_permissions'])->find($id);
    if ($user) {
        return $user;
    } else {
        return null;
    }
}


//recupere les permissions
function getUserPermissions()
{
    $userId = Auth::id();

    $permissions = DB::table('user_permission')
        ->where('id_user', $userId)
        ->pluck('id_Permission')
        ->toArray();

    return $permissions;
}


function generateStrongPassword($length = 8)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $password;
}


//generate numero chrono 

function generateChronoNumber(string $service)
{
    $sites = [
        'POST' => 'ABJ1',
        'REIM' => 'KGO',
        'DUPL' => 'BKE',
        'OPSP' => 'ABJ2',
    ];

    $codeSite = $sites[$service] ?? 'ABJ1';

    $year = now()->format('y');
    $month = now()->format('m');

    // On utilise une transaction pour éviter les doublons
    return DB::transaction(function () use ($service, $codeSite, $year, $month) {

        // Récupère ou crée la ligne pour ce mois/service
        $row = DB::table('chronos')
            ->where('service', $service)
            ->where('year', $year)
            ->where('month', $month)
            ->lockForUpdate() // 🔒 empêche deux accès en même temps
            ->first();

        if (!$row) {
            // Crée la ligne avec counter = 1
            DB::table('chronos')->insert([
                'service' => $service,
                'year' => $year,
                'month' => $month,
                'counter' => 1,
            ]);
            $counter = 1;
        } else {
            // Incrémente en base (atomic)
            DB::table('chronos')
                ->where('id', $row->id)
                ->increment('counter');
            $counter = $row->counter + 1;
        }

        $serial = str_pad($counter, 5, '0', STR_PAD_LEFT);

        return strtoupper($service . $codeSite . $year . $month . $serial);
    });
}


//récupérer les permissions de l'utilisateur connecté
function getPermissions()
{
    $user = Auth::user();
    return $user->r_permissions;
}


function servicesAccessibles()
{
    $user = Auth::user();

    if (!$user) {
        return collect(); // retourne une collection vide si pas connecté
    }

    // Retourne les IDs uniques des services pour le site de l'utilisateur
    return DetailTypeService::where('id_site', $user->id_site)
        ->distinct()         // sélectionne seulement les valeurs uniques
        ->pluck('id_service');
}


// update numérisation site_id

function updateFdsSite($num_chrono, $site_id)
{
    try {
        // Mapping des site_id
        $siteMapping = [
            8 => 1,
            7 => 2,
            9 => 3,
        ];

        // Conversion du site_id selon le mapping
        $mappedSiteId = $siteMapping[$site_id] ?? $site_id;

        // Si le site_id n'est pas dans le mapping, on garde la valeur originale
        // ou vous pouvez lever une erreur selon vos besoins

        $token = '511|trMIwkcCAMbfia2sg7ZMPMcx1Ybtrdn4TxyGGRXt6307e6da';

        // 🔍 LOG AVANT ENVOI
        Log::info('API REQUEST - updateFdsSite', [
            'url' => 'https://placenett.net/api/fds/ops/update/site',
            'original_site_id' => $site_id,
            'mapped_site_id' => $mappedSiteId,
            'payload' => [
                'num_chrono' => $num_chrono,
                'site_id' => $mappedSiteId,
            ]
        ]);

        $response = Http::timeout(10)
            ->withToken($token)
            ->acceptJson()
            ->post(
                'https://placenett.net/api/fds/ops/update/site',
                [
                    'num_chrono' => $num_chrono,
                    'site_id' => $mappedSiteId,
                ]
            );

        // 🔍 LOG RÉPONSE
        Log::info('API RESPONSE - updateFdsSite', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        // ❌ Si erreur API
        if (!$response->successful()) {
            Log::error('API ERROR - updateFdsSite', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'status' => $response->status(),
                'message' => 'Erreur API distante',
                'data' => $response->json()
            ];
        }

        // ✅ SUCCESS
        return [
            'success' => true,
            'status' => $response->status(),
            'data' => $response->json()
        ];
    } catch (\Exception $e) {

        // ❌ EXCEPTION
        Log::error('API EXCEPTION - updateFdsSite', [
            'message' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Erreur connexion API',
        ];
    }
}
