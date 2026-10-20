<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Site;
use App\Models\Caisse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // Rôles nécessitant une caisse (le contrôleur est rattaché au site, pas à une caisse)
    private const CAISSE_ROLE_NAMES = ['Caisse', 'CaisseFinancier'];

    /**
     * Détermine si un rôle nécessite l'affectation d'une caisse.
     * On se base sur le nom du rôle et non sur son ID, qui peut varier selon les bases.
     */
    private function isCaisseRole(int $roleId): bool
    {
        $role = Role::find($roleId);

        return $role !== null && in_array($role->nom_role, self::CAISSE_ROLE_NAMES, true);
    }

    public function index()
    {
        $users = User::with(['r_user_role', 'r_user_site', 'r_user_caisse'])
            ->orderBy('id_site')
            ->get();

        return inertia('Admin/user', [
            'users'   => $users,
            'roles'   => Role::all(),
            'sites'   => Site::all(),
            'caisses' => Caisse::select('id', 'code', 'libelle', 'site_id')->where('is_active', 1)->get(),
        ]);
    }

    /**
     * Retourne les caisses actives liées à un site (pour le select dynamique).
     */
    public function caissesBySite($siteId)
    {
        $caisses = Caisse::select('id', 'code', 'libelle', 'site_id')
            ->where('site_id', $siteId)
            ->where('is_active', 1)
            ->get();

        return response()->json($caisses);
    }


    public function store(Request $request)
    {
        $roleId = (int) $request->input('id_role');
        $isCaisseRole = $this->isCaisseRole($roleId);

        $validated = $request->validate([
            'nom'       => 'required|string',
            'prenom'    => 'required|string',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6',
            'id_role'   => 'required|exists:roles,id',
            'id_site'   => 'required|exists:sites,id',
            'caisse_id' => $isCaisseRole
                ? ['required', Rule::exists('caisses', 'id')->where(fn ($query) => $query->where('site_id', $request->input('id_site')))]
                : ['nullable', 'exists:caisses,id'],
        ], [
            'nom.required'        => 'Le nom est obligatoire.',
            'prenom.required'     => 'Le prénom est obligatoire.',
            'email.required'      => 'L’adresse e-mail est obligatoire.',
            'email.email'         => 'L’adresse e-mail n’est pas valide.',
            'email.unique'        => 'Cette adresse e-mail est déjà utilisée par un autre utilisateur.',
            'password.required'   => 'Le mot de passe est obligatoire.',
            'password.min'        => 'Le mot de passe doit contenir au moins 6 caractères.',
            'id_role.required'    => 'Le rôle est obligatoire.',
            'id_role.exists'      => 'Le rôle sélectionné est invalide.',
            'id_site.required'    => 'Le site est obligatoire.',
            'id_site.exists'      => 'Le site sélectionné est invalide.',
            'caisse_id.required'  => 'La caisse est obligatoire pour ce rôle.',
            'caisse_id.exists'    => 'La caisse sélectionnée ne correspond pas au site choisi.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['statut']   = 1;

        // Forcer caisse_id à null pour les rôles non-caisse
        if (!$isCaisseRole) {
            $validated['caisse_id'] = null;
        }

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $roleId = (int) $request->input('id_role');
        $isCaisseRole = $this->isCaisseRole($roleId);

        $validated = $request->validate([
            'nom'       => 'required|string',
            'prenom'    => 'required|string',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'id_role'   => 'required|exists:roles,id',
            'id_site'   => 'required|exists:sites,id',
            'password'  => 'nullable|min:6',
            'caisse_id' => $isCaisseRole
                ? ['required', Rule::exists('caisses', 'id')->where(fn ($query) => $query->where('site_id', $request->input('id_site')))]
                : ['nullable', 'exists:caisses,id'],
        ], [
            'nom.required'        => 'Le nom est obligatoire.',
            'prenom.required'     => 'Le prénom est obligatoire.',
            'email.required'      => 'L’adresse e-mail est obligatoire.',
            'email.email'         => 'L’adresse e-mail n’est pas valide.',
            'email.unique'        => 'Cette adresse e-mail est déjà utilisée par un autre utilisateur.',
            'password.min'        => 'Le mot de passe doit contenir au moins 6 caractères.',
            'id_role.required'    => 'Le rôle est obligatoire.',
            'id_role.exists'      => 'Le rôle sélectionné est invalide.',
            'id_site.required'    => 'Le site est obligatoire.',
            'id_site.exists'      => 'Le site sélectionné est invalide.',
            'caisse_id.required'  => 'La caisse est obligatoire pour ce rôle.',
            'caisse_id.exists'    => 'La caisse sélectionnée ne correspond pas au site choisi.',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Forcer caisse_id à null pour les rôles non-caisse
        if (!$isCaisseRole) {
            $validated['caisse_id'] = null;
        }

        $user->update($validated);
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    }

    public function updateStatut(Request $request, $id)
    {
        $validated = $request->validate([
            'statut' => 'required|integer|in:1,2',
        ]);

        $user = User::findOrFail($id);
        $user->statut = $validated['statut'];
        $user->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'user' => $user,
        ]);
    }

    public function changePassword(Request $request, $userId)
    {
        // dd($request->all());
        $validated = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'new_password.min' => 'Le nouveau mot de passe doit comporter au moins 8 caractères.',
        ]);


        $user = User::findOrFail($userId);

        // Vérification de l’ancien mot de passe
        if (! Hash::check($validated['old_password'], $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => 'L’ancien mot de passe est incorrect.',
            ]);
        }

        // Empêcher la réutilisation du même mot de passe
        if (Hash::check($validated['new_password'], $user->password)) {
            throw ValidationException::withMessages([
                'new_password' => 'Le nouveau mot de passe doit être différent de l’ancien.',
            ]);
        }

        // Mise à jour
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'message' => 'Mot de passe modifié avec succès.',
        ]);
    }
}
