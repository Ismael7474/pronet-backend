<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InterventionController;
use App\Http\Controllers\RapportVisiteController;
use App\Http\Controllers\RapportInterventionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;

// Routes publiques (sans authentification)
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées (authentification requise)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard (gérant uniquement)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Users (gérant uniquement)
    Route::apiResource('users', UserController::class);

    // Clients (gérant uniquement)
    Route::apiResource('clients', ClientController::class);
    Route::get('/clients/search', [ClientController::class, 'search']);

    // Interventions
    Route::apiResource('interventions', InterventionController::class);
    Route::put('/interventions/{id}/statut', [InterventionController::class, 'changerStatut']);
    Route::get('/interventions/statistiques', [InterventionController::class, 'statistiques']);
    Route::post(
    '/interventions/{id}/affecter',
    [InterventionController::class,
     'affecterTechniciens']
    );

    //Notifications
    Route::get('/notifications',[NotificationController::class, 'index']);
    Route::put('/notifications/{id}/lire',[NotificationController::class, 'marquerCommeLue']);
    Route::put('/notifications/lire-toutes',[NotificationController::class, 'toutMarquerCommeLu']);

    // Routes technicien
    Route::get('/mes-taches', [InterventionController::class, 'mesTaches']);

    // Rapports de visite
    Route::apiResource('rapport-visites', RapportVisiteController::class);
    Route::get('/rapport-visites/intervention/{id}', [RapportVisiteController::class, 'parIntervention']);

    // Rapports d'intervention
    Route::apiResource('rapport-interventions', RapportInterventionController::class);
    Route::get('/rapport-interventions/intervention/{id}', [RapportInterventionController::class, 'parIntervention']);

    // Tickets WiFi
    Route::apiResource('tickets', TicketController::class);
    Route::get('/tickets/revenus-par-mois', [TicketController::class, 'revenusParMois']);
    Route::get('/tickets/statistiques', [TicketController::class, 'statistiques']);

    // Abonnements
    Route::apiResource('abonnements', AbonnementController::class);
    Route::post('/abonnements/{id}/renouveler', [AbonnementController::class, 'renouveler']);
    Route::get('/abonnements/expirants', [AbonnementController::class, 'expirants']);
    Route::post('/abonnements/mise-a-jour-statuts', [AbonnementController::class, 'mettreAJourStatuts']);

    // Activites
    Route::get('/activites', [ActiviteController::class, 'index']);
    Route::get('/activites/recentes', [ActiviteController::class, 'recentes']);
    Route::get('/activites/technicien/{id}', [ActiviteController::class, 'parTechnicien']);
    Route::get('/activites/client/{id}', [ActiviteController::class, 'parClient']);
});
