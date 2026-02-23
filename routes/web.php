<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AuthenticateController;
use App\Http\Controllers\BossController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\ControleCaisseController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ElementFacturationController;
use App\Http\Controllers\EntiteController;
use App\Http\Controllers\UserController;
// use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\NumerisationController;
use App\Http\Controllers\PdcController;
use App\Http\Controllers\VehiculeController;
use App\Http\Controllers\CorrectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\GestionnaireController;
use App\Http\Controllers\Mt1Controller;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\RafCaisseController;
use App\Http\Controllers\RejetController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TypeServiceController;
use App\Http\Controllers\Admin\MarqueController;
use App\Http\Controllers\Admin\ModelController;
use App\Models\Caisse;

//Route groupe des utilisteurs non authentifier
Route::middleware('guest')->group(function () {
    //Route de d'affichage du login 
    Route::get('/login', [AuthenticateController::class, "showLogin"])->name('login');
    Route::post('user/login', [AuthenticateController::class, "authenticate"])->name("login.user");
    //Route d'affichage de la page Partenaires Register
    // Route::get('/paretainer/register', function () {
    //     return inertia('Auth/PartenairesRegister');
    // })->name('register.partener');
    //Route d'affichage de la page Register des users
    // Route::get('/register', function () {
    //     return inertia('Auth/Register');
    // })->name('register');
});


//Route groupe des utilisteurs authentifier
Route::middleware('auth')->group(function () {
    //logout
    Route::post('/logout', [AuthenticateController::class, "logout"])->name('logout');

    Route::get('/countries', [CountryController::class, 'index']);


    // Entreprise data importante
    Route::get('/entreprises/{id}', [EntrepriseController::class, 'getData'])->name('entreprise.data');
    Route::get('/permissions/list', [PermissionController::class, "getPermissions"])->name('show.permissions.list');


    // Récupérer les permissions d'un utilisateur spécifique
    Route::get('/users/permissions/{id}', [PermissionController::class, 'getUserPermissions'])
        ->name('users.permissions.get');

    // Attribuer des permissions à un utilisateur
    Route::post('/users/permissions/{id}', [PermissionController::class, 'assignPermissions'])
        ->name('users.permissions.assign');

    // Modifier le mot de passe d'un utilisateur
    Route::post('/users/change-password/{id}', [UserController::class, 'changePassword'])
        ->name('users.password.change');
});


Route::middleware('auth')->group(function () {
    //logout
    Route::post('/logout', [AuthenticateController::class, "logout"])->name('logout');

    //*******************************Caisse***************************** */
    Route::get('/caisse', [CaisseController::class, "showCaisseDashboard"])->name('show.caisse.dashboard');
    Route::get('/caisse/dashboard/stats', [CaisseController::class, "getCaisseStats"])->name('show.caisse.dashboard.stats');
    Route::get('/caisse/dashboard/stats/by/date', [CaisseController::class, "getCaisseStatsByDate"])->name('get.caisse.dashboard.stats.by.date');
    Route::get('/caisse/data', [CaisseController::class, "showCaisseData"])->name('show.caisse.data');
    Route::get('/caisse/get/data', [CaisseController::class, "getCaisseData"])->name('get.caisse.data');
    Route::get('/caisse/new', [CaisseController::class, "showNewCaisse"])->name('show.new.caisse');
    Route::get('/caisse/get/data/{vin}', [CaisseController::class, "showCaisseGetData"])->name('show.new.caisse.get.data');
    Route::get('/caisse/get/data/modification/{vin}', [CaisseController::class, "showCaisseModificationGetData"])->name('show.modification.caisse.get.data');

    // gestion caisse
    Route::get('/caisse/statut', [CaisseController::class, 'statut'])->name('caisse.statut');
    Route::post('/caisses/ouvertures', [CaisseController::class, 'open'])->name('caisses.ouvertures.open');
    Route::put('/caisses/ouvertures/{id}/close', [CaisseController::class, 'close'])->name('caisses.ouvertures.close');


    //gestion paiement
    // routes/api.php
    Route::post('/statut/paiement', [PaiementController::class, 'updateStatutPaiement'])->name('statut.paiement');
    Route::post('/caisse/paiement/annuler', [CaisseController::class, 'annulerPaiement'])->name('caisse.paiement.annuler');
    // Route::get('/paiement/recu/{id}', [PaiementController::class, 'recu'])->name('caisse.recu');
    Route::get('/paiement/receipt/{id}', [PaiementController::class, 'receipt'])->name('paiement.receipt');
    //getPaiementDataForStat
    Route::get('/paiement/data/stat', [PaiementController::class, 'getPaiementDataForStat'])->name('paiement.data.stat');
    Route::get('/entites/actives', [PaiementController::class, 'getActiveEntites']);


    //*******************************Correction***************************** */
    Route::post('/correction/new', [CorrectionController::class, 'store'])->name('correction.new.store');


    //**********************************Minister*****************************************/
    Route::get('/minister/mt1', [Mt1Controller::class, "showMt1Dashboard"])->name('show.mt1.dashboard');
    Route::get('/mt1/dashboard/stats', [DashboardController::class, "getStats"])->name('show.mt1.dashboard.stats');
    Route::get('/mt1/dashboard/stats/by/date', [DashboardController::class, "getStatsByDate"])->name('get.mt1.dashboard.stats.by.date');

    Route::get('/minister/mt1/list', [Mt1Controller::class, "showMt1List"])->name('show.mt1.list');
    Route::get('/minister/mt1/get/data', [Mt1Controller::class, "getMt1Data"])->name('get.mt1.list.data');
    Route::get('/minister/mt1/get/data/{vin}', [Mt1Controller::class, "showMt1GetData"])->name('show.new.mt1.get.data');
    Route::get('/minister/mt2', [Mt1Controller::class, "showMt2Dashboard"])->name('show.mt2.dashboard');
    Route::get('/minister/mt2/list', [Mt1Controller::class, "showMt2List"])->name('show.mt2.list');
    Route::get('/minister/mt2/get/data', [Mt1Controller::class, "getMt2Data"])->name('get.mt2.list.data');
    Route::get('/minister/mt2/get/data/{vin}', [Mt1Controller::class, "showMt2GetData"])->name('show.new.mt2.get.data');
    Route::get('/minister/mt1/get/data/modification/{vin}', [Mt1Controller::class, "showModificationGetData"])->name('show.modification.mt1.get.data');
    Route::post('/minister/mt1/dossiers/rejeter', [Mt1Controller::class, 'rejeterDossier']);
    Route::post('/minister/mt1/dossiers/valider', [Mt1Controller::class, 'validerDossier']);
    Route::get('/minister/mt1/get/rejets/data', [RejetController::class, 'index']);
    Route::post('/minister/mt1/save/motif/rejets', [RejetController::class, 'store']);
    Route::get('/minister/mt1/get/motifs/rejets/{dossierId}', [RejetController::class, 'getMotifsRejets']);
    Route::get('/minister/mt1/dossier/{id_dossier}', [Mt1Controller::class, "listDossiers"])->name('show.mt1.dossier.details');


    //***********************************Administrateur**************************************** */

    //*********************************************end Administrateur**************************************** */


    //get marque & model
    Route::get('/pdc/get/marque', [VehiculeController::class, "getMarque"])->name('get.marque');
    Route::get('/pdc/get/model', [VehiculeController::class, "getModel"])->name('get.model');
    //verifier vin & chrono
    Route::get('/verifie/vin/{vin}', [PdcController::class, "verifieVin"])->name('verifie.vin');
    Route::get('/verifie/chrono/{vin}', [PdcController::class, "verifieChrono"])->name('verifie.chrono');





    //*********************************************Gestionnaire**************************************** */
    Route::get('/gestionnaire/dashboard', [GestionnaireController::class, "showGestionnaireDashboard"])->name('show.gestionnaire.dashboard');
    Route::get('/gestionnaire/dashboard/stats', [DashboardController::class, "getStats"])->name('show.gestionnaire.dashboard.stats');
    Route::get('/gestionnaire/dashboard/stats/by/date', [DashboardController::class, "getStatsByDate"])->name('get.gestionnaire.dashboard.stats.by.date');
    Route::get('/gestionnaire/list', [GestionnaireController::class, "showGestionnaireList"])->name('show.gestionnaire.list');
    Route::get('/gestionnaire/get/data', [GestionnaireController::class, "getGestionnaireData"])->name('get.gestionnaire.list.data');
    Route::get('/gestionnaire/get/data/{vin}', [GestionnaireController::class, "showGestionnaireGetData"])->name('show.new.gestionnaire.get.data');
    Route::get('/gestionnaire/get/data/modification/{vin}', [GestionnaireController::class, "showModificationGetData"])->name('show.modification.gestionnaire.get.data');
    Route::post('/gestionnaire/dossiers/rejeter', [GestionnaireController::class, 'rejeterDossier']);
    Route::post('/gestionnaire/dossiers/valider', [GestionnaireController::class, 'validerDossier']);
    Route::get('/gestionnaire/get/rejets/data', [RejetController::class, 'index']);
    Route::post('/gestionnaire/save/motif/rejets', [RejetController::class, 'store']);
    Route::get('/gestionnaire/get/motifs/rejets/{dossierId}', [RejetController::class, 'getMotifsRejets']);
    Route::get('/gestionnaire/dossier/{id_dossier}', [GestionnaireController::class, "listDossiers"])->name('show.gestionnaire.dossier.details');


    // 
    Route::get('/get/boss/paiement/global/stats', [BossController::class, "getGlobalPaiementStats"])->name('get.boss.paiement.global.stats');
    Route::get('/get/comparative/stats', [BossController::class, 'getComparativeStats'])->name('comparative.stats');

    // 
    Route::get('/get/controller/paiement/global/stats', [BossController::class, "getControllerGlobalPaiementStats"])->name('get.controller.paiement.global.stats');

    Route::get('/type/services', [TypeServiceController::class, 'getTypeServicesById']);
});


Route::inertia('/', 'Index')->name('home');
// Route::get('/', [ClientController::class, "welcome"])->name('home');
Route::get('/register', [RegisterController::class, "showRegister"])->name('show.register');
Route::post('/register', [RegisterController::class, "register"])->name('user.register');

Route::get('/forgot-password', [RegisterController::class, "forgotPassword"])->name('forgot.password');

//Route des users du PoolControle
Route::group([
    // 'prefix' => 'PoolControle',
    'middleware' => ['auth', 'PoolControle']
], function () {


    //****************************************Immatriculation****************************************//
    Route::get('/pdc', [PdcController::class, "showPdcDashboard"])->name('show.pdc.dashboard');
    Route::get('/pdc/dashboard/stats', [DashboardController::class, "getStats"])->name('show.dashboard.stats');
    Route::get('/pdc/dashboard/stats/by/date', [DashboardController::class, "getStatsByDate"])->name('get.dashboard.stats.by.date');

    Route::get('/pdc/dashboard/global/stats', [DashboardController::class, "getGlobalStats"])->name('show.dashboard.global.stats');

    Route::prefix('stats')->group(function () {
        Route::get('/day', [DashboardController::class, 'getStatsByDay']);
        Route::get('/week', [DashboardController::class, 'getStatsByWeek']);
        Route::get('/month', [DashboardController::class, 'getStatsByMonth']);
    });

    Route::get('/pdc/immatriculation', [PdcController::class, "showPdcImmatriculation"])->name('show.pdc.immatriculation');
    Route::get('/pdc/immatriculation/select', [PdcController::class, "showPdcImmatriculationSelect"])->name('show.pdc.immatriculation.select');
    Route::get('/pdc/immatriculation/data', [PdcController::class, "getPdcImmatriculationData"])->name('get.pdc.immatriculation.data');
    Route::get('/pdc/immatriculation/new', [PdcController::class, "showNewPdcImmatriculation"])->name('show.new.pdc.immatriculation');
    Route::get('/pdc/immatriculation/new/add/data/{vin}', [PdcController::class, "showNewPdcImmatriculationAddData"])->name('show.new.pdc.immatriculation.add.data');
    Route::get('/pdc/immatriculation/edit/{id}', [PdcController::class, "showEditPdcImmatriculation"])->name('show.edit.pdc.immatriculation');
    Route::get('/pdc/immatriculation/view/{id}', [PdcController::class, "showViewPdcImmatriculation"])->name('show.view.pdc.immatriculation');
    Route::get('/pdc/immatriculation/receipt/{id}', [PdcController::class, "showReceiptPdcImmatriculation"])->name('show.receipt.pdc.immatriculation');
    Route::get('/pdc/immatriculation/edit/data/{vin}', [PdcController::class, "EditPdcImmatriculationAddData"])->name('edit.pdc.immatriculation.add.data');
    Route::post('/pdc/immatriculation/save/data', [PdcController::class, 'savePdcImmatriculationData'])->name('save.pdc.immatriculation.data');
    Route::put('/pdc/immatriculation/{id}/update', [PdcController::class, 'updatePdcImmatriculationData'])->name('update.pdc.immatriculation.data');

    //****************************************ReImmatriculation****************************************//
    Route::get('/pdc/re/immatriculation', [PdcController::class, "showPdcReImmatriculation"])->name('show.pdc.re.immatriculation');
    Route::get('/pdc/re/immatriculation/select', [PdcController::class, "showPdcReImmatriculationSelect"])->name('show.pdc.re.immatriculation.select');

    Route::get('/pdc/re/immatriculation/data', [PdcController::class, "getPdcReImmatriculationData"])->name('get.pdc.re.immatriculation.data');
    Route::get('/pdc/re/immatriculation/new', [PdcController::class, "showNewPdcReImmatriculation"])->name('show.new.pdc.re.immatriculation');
    Route::get('/pdc/re/immatriculation/new/add/data/{vin}', [PdcController::class, "showNewPdcReImmatriculationAddData"])->name('show.new.pdc.re.immatriculation.add.data');
    // Route::get('/pdc/new/re/immatriculation', [PdcController::class, "showPdcReImmatriculationNew"])->name('show.new.pdc.re.immatriculation');
    Route::post('/pdc/re/immatriculation/save/data', [PdcController::class, 'savePdcReImmatriculationData'])->name('save.pdc.re.immatriculation.data');
    Route::get('/pdc/re/immatriculation/receipt/{id}', [PdcController::class, "showReceiptPdcReImmatriculation"])->name('show.receipt.pdc.re.immatriculation');

    Route::get('/pdc/re/immatriculation/view/{id}', [PdcController::class, "showViewPdcReImmatriculation"])->name('show.view.pdc.re.immatriculation');
    Route::get('/pdc/re/immatriculation/edit/{id}', [PdcController::class, "showEditPdcReImmatriculation"])->name('show.edit.pdc.re.immatriculation');
    Route::put('/pdc/re/immatriculation/{id}/update', [PdcController::class, 'updatePdcReImmatriculationData'])->name('update.pdc.re.immatriculation.data');


    // Route::get('/pdc/re/immatriculation/edit/data/{vin}', [PdcController::class, "EditPdcReImmatriculationAddData"])->name('edit.pdc.re.immatriculation.add.data');
    // // Route::post('/pdc/immatriculation/save/data', [PdcController::class, 'savePdcImmatriculationData'])->name('save.pdc.immatriculation.data');
    // Route::put('/pdc/immatriculation/{id}/update', [PdcController::class, 'updatePdcImmatriculationData'])->name('update.pdc.immatriculation.data');

    //****************************************PostImmatriculation****************************************//
    Route::get('/pdc/post/immatriculation', [PdcController::class, "showPdcPostImmatriculation"])->name('show.pdc.post.immatriculation');
    Route::get('/pdc/post/immatriculation/select', [PdcController::class, "showPdcPostImmatriculationSelect"])->name('show.pdc.post.immatriculation.select');

    Route::get('/pdc/post/immatriculation/data', [PdcController::class, "getPdcPostImmatriculationData"])->name('get.pdc.post.immatriculation.data');
    Route::get('/pdc/post/immatriculation/new', [PdcController::class, "showNewPdcPostImmatriculation"])->name('show.new.pdc.post.immatriculation');
    Route::get('/pdc/post/immatriculation/new/add/data/{vin}', [PdcController::class, "showNewPdcPostImmatriculationAddData"])->name('show.new.pdc.post.immatriculation.add.data');
    Route::get('/pdc/immatriculation/edit/data/{vin}', [PdcController::class, "EditPdcImmatriculationAddData"])->name('edit.pdc.immatriculation.add.data');
    // Route::put('/pdc/post/immatriculation/{id}/update', [PdcController::class, 'updatePdcPostImmatriculationData'])->name('update.pdc.post.immatriculation.data');
    Route::put('/pdc/postimmatriculation/{id}/update', [PdcController::class, 'savePostImmatriculationDraft'])->name('update.pdc.post.immatriculation.data');
    Route::get('/pdc/post/immatriculation/receipt/{id}', [PdcController::class, "showReceiptPdcPostImmatriculation"])->name('show.receipt.pdc.post.immatriculation');
    Route::get('/pdc/post/immatriculation/view/{id}', [PdcController::class, "showPdcPostImmatriculationModificationLog"])->name('show.view.pdc.post.immatriculation');

    //****************************************Duplicata****************************************//
    Route::get('/pdc/duplicata', [PdcController::class, "showPdcDuplicata"])->name('show.pdc.duplicata');
    Route::get('/pdc/duplicata/select', [PdcController::class, "showPdcDuplicataSelect"])->name('show.pdc.duplicata.select');

    Route::get('/pdc/duplicata/data', [PdcController::class, "getPdcDuplicataData"])->name('get.pdc.duplicata.data');
    Route::get('/pdc/duplicata/new', [PdcController::class, "showNewPdcDuplicata"])->name('show.new.pdc.duplicata');
    Route::get('/pdc/duplicata/new/add/data/{vin}', [PdcController::class, "showNewPdcDuplicataAddData"])->name('show.new.pdc.duplicata.add.data');
    Route::get('/pdc/duplicata/edit/{id}', [PdcController::class, "showEditPdcDuplicata"])->name('show.edit.pdc.duplicata');
    Route::get('/pdc/duplicata/view/{id}', [PdcController::class, "showViewPdcDuplicata"])->name('show.view.pdc.duplicata');
    Route::get('/pdc/duplicata/receipt/{id}', [PdcController::class, "showReceiptPdcDuplicata"])->name('show.receipt.pdc.duplicata');
    Route::get('/pdc/duplicata/edit/data/{vin}', [PdcController::class, "EditPdcDuplicataAddData"])->name('edit.pdc.duplicata.add.data');
    Route::post('/pdc/duplicata/save/data', [PdcController::class, 'savePdcDuplicataData'])->name('save.pdc.duplicata.data');
    Route::put('/pdc/duplicata/{id}/update', [PdcController::class, 'updatePdcDuplicataData'])->name('update.pdc.duplicata.data');
    Route::get('/pdc/duplicata/detail/view/{vin}', [PdcController::class, "showViewPdcDuplicataData"])->name('show.detail.view.pdc.duplicata');
    Route::get('/pdc/duplicata/new/service/{data}', [PdcController::class, "showNewPdcDuplicataService"])->name('show.new.service.pdc.duplicata');
    Route::get('/pdc/duplicata/post/new/{vin?}', [PdcController::class, "showNewPdcDuplicataPost"])->name('show.new.pdc.duplicata.post');

    Route::get('/pdc/duplicata/post/new/add/data/{vin}', [PdcController::class, "showNewPdcDuplicataPostImmatriculationAddData"])->name('show.new.pdc.duplicata.post.immatriculation.add.data');

    //****************************************Correction****************************************//
    Route::get('/pdc/correction', [PdcController::class, "showPdcCorrection"])->name('show.pdc.correction');
    Route::get('/pdc/correction/data', [PdcController::class, "getPdcCorrectionData"])->name('get.pdc.correction.data');
});




//*******************************Numérisation***************************** */
//Route des users de Numérisation
Route::group([
    // 'prefix' => 'PoolControle',
    'middleware' => ['auth', 'Numerisation']
], function () {
    Route::get('/numerisation', [NumerisationController::class, "showNumerisationDashboard"])->name('show.numerisation.dashboard');
    Route::get('/numerisation/list', [NumerisationController::class, "showNumerisationList"])->name('show.numerisation.list');
    Route::get('/numerisation/get/data', [NumerisationController::class, "getNumerisationData"])->name('get.numerisation.list.data');
    Route::get('/numerisation/new', [NumerisationController::class, "showNewNumerisation"])->name('show.new.numerisation');
    Route::get('/numerisation/get/data/{vin}', [NumerisationController::class, "showNumerisationGetData"])->name('show.new.numerisation.get.data');
    Route::get('/numerisation/get/data/with/post/{dossier}', [NumerisationController::class, "showNumerisationGetDataWithPost"])->name('show.new.numerisation.get.data.with.post');
    Route::get('/numerisation/save-numerisation/{dossier}', [NumerisationController::class, "showFormNumerisation"])->name('show.form.numerisation');
    Route::get('/numerisation/save/ops/{dossier}', [NumerisationController::class, "showOpsFormNumerisation"])->name('show.ops.form.numerisation');
    Route::get('/numerisation/save/post/{dossier}', [NumerisationController::class, "showPostFormNumerisation"])->name('show.post.form.numerisation');

    Route::get('/numerisation/save/dupli/post/{dossier}', [NumerisationController::class, "showDupliPostFormNumerisation"])->name('show.dupli.post.form.numerisation');
    Route::get('/numerisation/select/dossier', [NumerisationController::class, "selectDossier"])->name('show.select.dossier.numerisation');


    Route::post('/numerisation/save', [NumerisationController::class, "saveNumerisation"])->name('save.form.numerisation');
    Route::post('/numerisation/ops/save', [NumerisationController::class, "saveOpsNumerisation"])->name('save.opa.form.numerisation');
    Route::post('/numerisation/post/save', [NumerisationController::class, "savePostNumerisation"])->name('save.post.form.numerisation');

    Route::post('/documents/update', [NumerisationController::class, 'updateNumerisation'])->name('update.form.numerisation');
    // Route::get('/numerisation/document/{id}', [NumerisationController::class, "getDocumentById"])->name('numerisation.document.show');
    // Liste des documents d'un dossier
    Route::get('/numerisation/dossier/{id_dossier}/documents', [NumerisationController::class, "getDocumentsByDossierId"])->name('numerisation.dossier.documents');
    Route::get('/numerisation/documents/dossier/{id_dossier}', [NumerisationController::class, "listDocuments"])->name('numerisation.dossier.documentslist');
    Route::get('/numerisation/documents/with/dossier/lier/{id_dossier}', [NumerisationController::class, "listDocumentsWithDossierLier"])->name('numerisation.dossier.documentslist.with.dossier.lier');

    Route::get('/numerisation/document/{id_document}/edit', [NumerisationController::class, "editDocument"])->name('numerisation.document.edit');
    Route::put('/numerisation/document/{id_document}', [NumerisationController::class, "updateDocument"])->name('numerisation.document.update');
    // Single edit numerisation
    Route::post('/numerisation/document/update/single', [NumerisationController::class, 'updateSingleField'])->name('numerisation.document.update.single');
    Route::get('/numerisation/documents/dossier/edit/{id_dossier}', [NumerisationController::class, "EditlistDocuments"])->name('numerisation.dossier.edit.documentslist');
    Route::get('numerisation/get/data/modification/{vin}', [NumerisationController::class, "showModificationGetData"])->name('show.modification.numerisation.get.data');
});




Route::group([
    // 'prefix' => 'PoolControle',
    'middleware' => ['auth', 'Admin']
], function () {

    //*******************************Site***************************** */
    Route::get('/admin', [SiteController::class, "showSiteDashboard"])->name('show.admin.dashboard');
    Route::get('/admin/dashboard/stats', [DashboardController::class, "getStats"])->name('show.admin.dashboard.stats');
    Route::get('/admin/dashboard/stats/by/date', [DashboardController::class, "getStatsByDate"])->name('get.admin.dashboard.stats.by.date');


    Route::get('/site/data', [SiteController::class, "showSiteData"])->name('show.admin.data');
    Route::get('/site/get/data', [SiteController::class, "getSiteData"])->name('get.admin.data');
    Route::get('/site/new', [SiteController::class, "showNewSite"])->name('show.new.admin');
    Route::put('/site/update/statut/{id}', [SiteController::class, "updateStatutSite"])->name('update.admin.statut');
    Route::post('/add/site/new', [SiteController::class, "storeNewSite"])->name('store.new.admin');
    Route::get('/site/edit/{id}', [SiteController::class, "showEditSite"])->name('show.edit.admin');
    Route::put('/site/update/{id}', [SiteController::class, "updateSite"])->name('update.admin');
    Route::delete('/site/delete/{id}', [SiteController::class, "deleteSite"])->name('delete.admin');
    Route::get('/site/view/{id}', [SiteController::class, "showViewSite"])->name('show.view.admin');

    //*******************************Marques & Modèles***************************** */

    // Marques routes
    Route::get('/marques/data', [MarqueController::class, "index"])->name('show.marques.data');
    Route::get('/marques/get/data', [MarqueController::class, "getData"])->name('get.marques.data');
    Route::get('/marques/{id}/models', [MarqueController::class, "getModels"])->name('get.marques.models');
    Route::get('/marques/new', [MarqueController::class, "create"])->name('show.new.marques');
    Route::post('/marques/new', [MarqueController::class, "store"])->name('store.new.marques');
    Route::get('/marques/edit/{id}', [MarqueController::class, "edit"])->name('show.edit.marques');
    Route::put('/marques/update/{id}', [MarqueController::class, "update"])->name('update.marques');
    Route::delete('/marques/delete/{id}', [MarqueController::class, "destroy"])->name('delete.marques');

    // Models routes
    Route::get('/models/data', [ModelController::class, "index"])->name('show.models.data');
    Route::get('/models/get/data', [ModelController::class, "getData"])->name('get.models.data');
    Route::get('/models/new', [ModelController::class, "create"])->name('show.new.models');
    Route::post('/models/new', [ModelController::class, "store"])->name('store.new.models');
    Route::get('/models/edit/{id}', [ModelController::class, "edit"])->name('show.edit.models');
    Route::put('/models/update/{id}', [ModelController::class, "update"])->name('update.models');
    Route::delete('/models/delete/{id}', [ModelController::class, "destroy"])->name('delete.models');

    // Element facturation
    Route::get('/element-facturation/data', [ElementFacturationController::class, 'showElementFacturationList'])->name('show.element.facturation.list');
    Route::get('/get/element/facturation', [ElementFacturationController::class, 'getElementFacturationList'])->name('get.element.facturation.list');
    Route::post('/element-facturation/create', [ElementFacturationController::class, "createElementFacturation"])->name('create.element.facturation');
    Route::get('/element-facturation/edit/{id}', [ElementFacturationController::class, "showElementFacturationEdit"])->name('show.element.facturation.edit');
    Route::put('/element-facturation/update/{id}', [ElementFacturationController::class, "updateElementFacturation"])->name('update.element.facturation');
    Route::delete('/element-facturation/delete/{id}', [ElementFacturationController::class, "deleteElementFacturation"])->name('delete.element.facturation');
    Route::put('/element-facturation/statut/{id}', [ElementFacturationController::class, "updateStatutElementFacturation"])->name('update.element.facturation.statut');


    // Rejet routes

    Route::get('/rejets', [RejetController::class, 'listRejets'])->name('rejets.index');
    Route::post('/rejets', [RejetController::class, 'storeRejets'])->name('rejets.store');
    Route::put('/rejets/{rejet}', [RejetController::class, 'updateRejets'])->name('rejets.update');
    Route::delete('/rejets/{rejet}', [RejetController::class, 'destroyRejets'])->name('rejets.destroy');

    // Entité
    Route::get('/entite/data', [EntiteController::class, "showEntiteList"])->name('show.entite.list');
    Route::post('/entite/create', [EntiteController::class, "createEntite"])->name('create.entite');
    Route::get('/entite/edit/{id}', [EntiteController::class, "showEntiteEdit"])->name('show.entite.edit');
    Route::put('/entite/update/{id}', [EntiteController::class, "updateEntite"])->name('update.entite');
    Route::delete('/entite/delete/{id}', [EntiteController::class, "deleteEntite"])->name('delete.entite');
    Route::put('/entite/statut/{id}', [EntiteController::class, "updateStatutEntite"])->name('update.entite.statut');

    // Utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::post('/users', [UserController::class, 'store'])->name('user.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::put('/users/{id}/statut', [UserController::class, 'updateStatut'])->name('user.statut');

    // Clients
    Route::get('/clients', [ClientController::class, 'clientListe'])->name('client.liste');
});


//Route des users du PoolControle
Route::group([
    // 'prefix' => 'PoolControle',
    'middleware' => ['auth', 'Boss']
], function () {
    //****************************************Immatriculation****************************************//
    Route::get('/boss', [BossController::class, "showBossDashboard"])->name('show.boss.dashboard');
    Route::get('/boss/dashboard/stats', [BossController::class, "getStats"])->name('show.boss.dashboard.stats');
    Route::get('/boss/new/dashboard', [BossController::class, "showBossNewDashboard"])->name('show.boss.new.dashboard.stats');

    Route::get('/boss/dashboard/stats/by/date', [BossController::class, "getStatsByDate"])->name('get.boss.dashboard.stats.by.date');
    Route::get('/boss/dashboard/global/stats', [BossController::class, "getGlobalStats"])->name('show.boss.dashboard.global.stats');
    Route::get('/boss/statistics/dossiers', [BossController::class, "showBossDossiersStatistics"])->name('show.boss.statistics.dossiers');
    Route::get('/boss/statistics/caisses', [BossController::class, "showBossCaissesStatistics"])->name('show.boss.statistics.caisses');
    Route::get('/boss/statistics/global', [BossController::class, "getMontantTotal"])->name('get.boss.statistics.global');
    Route::get('/boss/statistics/finances', [BossController::class, "showBossFinancesStatistics"])->name('show.boss.statistics.finances');
    Route::get('/boss/statistics/comparative', [BossController::class, "showBossComparativeStatistics"])->name('show.boss.statistics.comparative');
});

//Route controoleur de caisse
Route::group(
    [
        'middleware' => ['auth', 'CaisseController']
    ],
    function () {
        //****************************************Immatriculation****************************************//
        Route::get('/caisse/controller', [ControleCaisseController::class, "showCaisseControllerDashboard"])->name('show.caisse.controller.dashboard');
        Route::get('/caisse/controller/dashboard/stats', [ControleCaisseController::class, "getStatsCaisseController"])->name('get.caisse.controller.dashboard.stats');
        Route::get('/caisse/controller/dashboard/stats/by/date', [ControleCaisseController::class, "getStatsByDate"])->name('get.caisse.controller.dashboard.stats.by.date');
        Route::get('/caisse/controller/dashboard/global/stats', [ControleCaisseController::class, "getGlobalStats"])->name('get.caisse.controller.dashboard.global.stats');
        Route::get('/caisse/controller/statistics/dossiers', [ControleCaisseController::class, "showCaisseDossiersStatistics"])->name('show.caisse.controller.statistics.dossiers');
        Route::get('/caisse/controller/statistics/caisses', [ControleCaisseController::class, "showCaisseCaissesStatistics"])->name('show.caisse.controller.statistics.caisses');
        Route::get('/caisse/controller/statistics/global', [ControleCaisseController::class, "getMontantTotal"])->name('get.caisse.controller.statistics.global');
        Route::post('/caisse/controller/validate', [ControleCaisseController::class, 'validateMontant'])->name('caisse.controller.validate');
        Route::post('/verification/validate/caisse/controller/montant', [ControleCaisseController::class, 'validateCotrolleurMontant'])->name('verification.validate.caisse.controller.montant');
        Route::get('/caisse/liste', [ControleCaisseController::class, 'getCaisses']);
        Route::get('/caisse/of/user', [ControleCaisseController::class, 'getCaisseOfAuthenticatedUser']);
    }
);



//Route controoleur de caisse
Route::group(
    [
        'middleware' => ['auth', 'Raf']
    ],
    function () {
        Route::get('/raf/dashboard', [RafCaisseController::class, "showRafDashboard"])->name('show.raf.dashboard');
        Route::get('/raf/caisse/controller/statistics/caisses', [RafCaisseController::class, "showRafCaisseCaissesStatistics"])->name('show.raf.caisse.controller.statistics.caisses');
        Route::get('/raf/caisse/liste', [RafCaisseController::class, 'getRafCaisseListe']);
        // Infos RAF pour une caisse spécifique
        Route::get('/raf/caisse/info', [RafCaisseController::class, 'getRafCaisseInfo']);
        // Validation ouverture/fermeture RAF
        Route::post('/raf/caisse/controller/validate', [RafCaisseController::class, 'validateRafControlleurMontant']);
        // Statistiques paiements RAF
        Route::get('/raf/paiement/data/stat', [RafCaisseController::class, 'getRafPaiementDataStat']);
        Route::get('/raf/controller/statistics/global', [ControleCaisseController::class, "getMontantTotal"])->name('get.raf.caisse.controller.statistics.global');
    }
);

// Liste des caisses RAF/raf/caisse/controller/statistics/caisses


//client 
Route::post('/', [ClientController::class, "clientLogin"])->name('client.login');
Route::post('/client/chrono', [ClientController::class, "chrono"])->name('client.chrono');
