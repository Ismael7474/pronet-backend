<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Intervention;
use App\Models\Ticket;
use App\Models\Abonnement;
use App\Models\Activite;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [

            // Clients
            'total_clients' => Client::count(),

            // Tickets WiFi
            'tickets_ce_mois' => Ticket::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count(),
            'revenus_ce_mois' => Ticket::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->sum('mon_revenu'),

            // Interventions
            'interventions_en_cours' => Intervention::where('statut', 'en_cours')
                                                    ->count(),
            'interventions_urgentes' => Intervention::where('statut', 'en_cours')
                                                    ->where('priorite', 'urgente')
                                                    ->count(),
            'interventions_terminees' => Intervention::where('statut', 'termine')
                                                     ->whereMonth('created_at', now()->month)
                                                     ->count(),

            // Abonnements
            'abonnements_expirants' => Abonnement::expirantDans(15)->count(),
            'abonnements_expires'   => Abonnement::expires()->count(),
            'abonnements_actifs'    => Abonnement::where('statut', 'actif')->count(),
        ];

        // Interventions récentes
        $interventions_recentes = Intervention::with(['client', 'techniciens'])
                                              ->orderBy('created_at', 'desc')
                                              ->take(5)
                                              ->get();

        // Abonnements expirants
        $abonnements_expirants = Abonnement::with('client')
                                           ->expirantDans(15)
                                           ->orderBy('date_expiration', 'asc')
                                           ->take(5)
                                           ->get();

        // Dernières ventes tickets
        $derniers_tickets = Ticket::with('client')
                                  ->orderBy('created_at', 'desc')
                                  ->take(5)
                                  ->get();

        // Activités récentes
        $activites_recentes = Activite::with(['user', 'client'])
                                      ->orderBy('created_at', 'desc')
                                      ->take(10)
                                      ->get();

        // Courbe revenus tickets par mois
        $revenus_par_mois = Ticket::selectRaw(
            'MONTH(created_at) as mois,
             SUM(mon_revenu) as total'
        )
        ->whereYear('created_at', now()->year)
        ->groupBy('mois')
        ->orderBy('mois')
        ->get();

        // Courbe interventions par mois
        $interventions_par_mois = Intervention::selectRaw(
            'MONTH(created_at) as mois,
             SUM(CASE WHEN type = "panne"
                 THEN 1 ELSE 0 END) as pannes,
             SUM(CASE WHEN type IN (
                 "installation_wifi",
                 "installation_camera")
                 THEN 1 ELSE 0 END) as installations'
        )
        ->whereYear('created_at', now()->year)
        ->groupBy('mois')
        ->orderBy('mois')
        ->get();

        return response()->json([
            'stats'                  => $stats,
            'interventions_recentes' => $interventions_recentes,
            'abonnements_expirants'  => $abonnements_expirants,
            'derniers_tickets'       => $derniers_tickets,
            'activites_recentes'     => $activites_recentes,
            'revenus_par_mois'       => $revenus_par_mois,
            'interventions_par_mois' => $interventions_par_mois,
        ], 200);
    }
}
