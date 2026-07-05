<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PdcController;
use App\Http\Controllers\PaiementController;  // Ajout de l'import


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

    // PRIMO-ESPECE - Création de dossier avec paiement total
    Route::post('/primo/espece', [PdcController::class, 'SavePrimoEspece']);

    //Route pour importer les donnee de primo (relica et espece) vers pdc
    Route::post('/importer/primo/relica/caisse', [PdcController::class, 'ImportPrimoRelicaCaisse']);
});

// Sync externe CIL - accessible sans auth Sanctum (appelé depuis l'interface interne)
Route::post('/primo/espece/sync', [PdcController::class, 'syncPrimoEspeceExternal']);

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
