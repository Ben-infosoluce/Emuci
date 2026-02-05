<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    //
    public function showRegister(Request $request)
    {
        return inertia('Auth/Register');
    }

    public function register(Request $request)
    {
        $credentials = $request->validate([
            'firstName' => 'required | min:3',
            'lastName' => 'required | min:3',
            'email' => 'required | email | unique:users |lowercase | max:255',
            'phone' => 'required | min:10 | max:10 | unique:users | numeric',
            'password' => 'required | min:8',
            'confirmPassword' => 'required | min:8 | same:password',
        ], [
            //en français
            'firstName.required' => 'Le prénom est requis.',
            'firstName.min' => 'Le prénom doit contenir au moins 3 caractères.',
            'lastName.required' => 'Le nom de famille est requis.',
            'lastName.min' => 'Le nom de famille doit contenir au moins 3 caractères.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'Adresse email non valide.',
            'email.unique' => 'L\'email existe déjà.',
            'email.lowercase' => 'L\'email doit être en minuscules.',
            'email.max' => 'L\'email doit contenir au maximum 255 caractères.',
            'phone.required' => 'Le numéro de téléphone est requis.',
            'phone.min' => 'Le numéro de téléphone doit contenir au moins 10 caractères.',
            'phone.max' => 'Le numéro de téléphone doit contenir au maximum 10 caractères.',
            'phone.unique' => 'Le numéro de téléphone existe déjà.',
            'phone.numeric' => 'Le numéro de téléphone doit être numérique.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'confirmPassword.required' => 'La confirmation du mot de passe est requise.',
            'confirmPassword.min' => 'La confirmation du mot de passe doit contenir au moins 8 caractères.',
        ]);
        //creation de l'utilisateur
        $user = User::create($credentials);

        //vérification de l'email
        Auth::login($user);

        // $user->sendEmailVerificationNotification();
        return redirect()->route('home');
        // return inertia('Auth/Register');
    }





    public function forgotPassword(Request $request)
    {
        return inertia('Auth/ForgotPassword');
    }
}
