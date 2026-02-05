<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    public function getPermissions()
    {
        $services = DB::table('services')->get();

        $permissions = DB::table('permissions')->get()->groupBy('service_id');

        $typeServices = DB::table('type_services')->get()->groupBy('id_service');

        $typeServicePermissions = DB::table('permissions')
            ->whereNotNull('type_service_id')
            ->get()
            ->groupBy('type_service_id');

        return response()->json(
            $services->map(fn($service) => [
                'service' => $service,
                'permissions' => $permissions[$service->id] ?? collect(),
                'type_services' => $typeServices[$service->id] ?? collect(),
                'type_service_permissions' => ($typeServices[$service->id] ?? collect())
                    ->flatMap(fn($ts) => $typeServicePermissions[$ts->id] ?? collect())
                    ->values(),
            ])
        );
    }

    public function getUserPermissions($userId)
    {
        $user = DB::table('users')
            ->select('id', 'nom', 'prenom', 'email')
            ->where('id', $userId)
            ->first();

        if (! $user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $permissions = DB::table('user_permission')
            ->where('id_user', $userId)
            ->pluck('id_Permission');

        return response()->json([
            'user' => $user,
            'permissions' => $permissions,
        ]);
    }

    public function assignPermissions(Request $request, int $userId)
    {
        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        if (! DB::table('users')->where('id', $userId)->exists()) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        DB::transaction(function () use ($userId, $data) {
            DB::table('user_permission')->where('id_user', $userId)->delete();

            if (! empty($data['permissions'])) {
                DB::table('user_permission')->insert(
                    collect($data['permissions'])->map(fn($permissionId) => [
                        'id_user' => $userId,
                        'id_Permission' => $permissionId,
                    ])->toArray()
                );
            }
        });

        return response()->json([
            'message' => 'Permissions mises à jour',
            'count' => count($data['permissions'] ?? []),
        ]);
    }
}
