<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;


class AuthController extends Controller
{
    //


    //logout de l'utilisateur
    // public function logout()
    // {
    //     Auth::logout();
    //     return Inertia::location("/");
    // }
}
