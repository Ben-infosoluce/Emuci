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
    Route::post('/update/statut/pose/plaque', [PdcController::class, 'updateStatutPosePlaque']);
    Route::post('/get/payment/status', [PdcController::class, 'getFdsOpsPaymentStatus']);
    Route::post('/relica/primo', [PdcController::class, 'SaveRelicaPrimo']);
});

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'API OK'
    ]);
});

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API OK'
    ]);
});
