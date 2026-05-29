<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PdcController;


Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    // Récupérer l'utilisateur connecté
    Route::get('/user', [AuthController::class, 'user']);
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    //FDS
    Route::post('/fds/ops', [PdcController::class, 'SaveFdsOps']);
    Route::post('/fds/ops/update', [PdcController::class, 'updateFdsOps']);
    Route::post('/fds/ops/update/statut/plaque', [PdcController::class, 'updateFdsOpsStatutPlaque']);
    Route::post('/fds/ops/payment/status', [PdcController::class, 'getFdsOpsPaymentStatus']);
});