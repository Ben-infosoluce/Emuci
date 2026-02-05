<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['r_user_role', 'r_user_site'])
            ->orderBy('id_site')
            ->get();

        return inertia('Admin/user', [
            'users' => $users,
            'roles' => Role::all(),
            'sites' => Site::all(),
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'id_role' => 'required|exists:roles,id',
            'id_site' => 'required|exists:sites,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['statut'] = 1;

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

        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'id_role' => 'required|exists:roles,id',
            'id_site' => 'required|exists:sites,id',
            'password' => 'nullable|min:6',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
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
