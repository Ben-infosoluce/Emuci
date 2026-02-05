<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticateController extends Controller
{
    //
    public function showLogin(Request $request)
    {
        return inertia('Auth/Login');
    }

    //function d'authentification
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.email' => 'Entrez une adresse email valide',
            'email.required' => 'Le champ email est obligatoire',
            'password.required' => 'Le champ mot de passe est obligatoire',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Récupérer l'utilisateur authentifié
            $user = Auth::user();

            // Déterminer la route en fonction du rôle de l'utilisateur
            $route = $this->getRouteByUserRole($user->r_user_role->nom_role);
            // dd($route);

            // Rediriger vers la route déterminée
            // return Inertia::location(route($route));
            return Inertia::location(route($route, [
                'user' => $user->id,
                'role' => $user->r_user_role->nom_role
            ]));
        }

        return back()->withErrors([
            'error' => 'Identifiants incorrects',
        ]);
    }

    private function getRouteByUserRole($userRole)
    {
        switch ($userRole) {
            case "PoolControle":
                return 'show.pdc.dashboard'; // Utilisez le nom correct de la route
            case "Caisse":
                return 'show.caisse.dashboard';
            case "Numerisation":
                return 'show.numerisation.dashboard';
            case "MT1":
                return 'show.mt1.dashboard';
            case "Boss":
                return 'show.boss.dashboard';
                // case "MT2":
                //     return 'show.mt2.dashboard';
            case "Admin":
                return 'show.admin.dashboard';
            case "CaisseController":
                return 'show.caisse.controller.dashboard';
            case "Raf":
                return 'show.raf.dashboard';
            case "Gestionnaire":
                return 'show.gestionnaire.dashboard';
            default:
                return 'home'; // Route par défaut si le rôle n'est pas reconnu
        }
    }


    public function logout()
    {
        Auth::logout();
        return Inertia::location("/");
    }
}
