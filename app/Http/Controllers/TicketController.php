<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Activite;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // Liste tous les tickets
    public function index()
    {
        $tickets = Ticket::with('client')
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json($tickets, 200);
    }

    // Créer un ticket
    public function store(Request $request)
    {
        $request->validate([
            'id_client'      => 'required|exists:clients,id',
            'type_wifi'      => 'required|in:wifi_box,starlink',
            'nombre_ticket'  => 'required|integer|min:1',
            'prix_unitaire'  => 'required|numeric|min:0',
            'mon_revenu'     => 'required|numeric|min:0',
        ]);

        $ticket = Ticket::create($request->all());

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a enregistré un ticket WiFi',
            'ticket',
            null,
            $ticket->id_client
        );

        return response()->json($ticket->load('client'), 201);
    }

    // Afficher un ticket
    public function show($id)
    {
        $ticket = Ticket::with('client')->findOrFail($id);

        return response()->json($ticket, 200);
    }

    // Supprimer un ticket
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket supprimé avec succès'
        ], 200);
    }

    // Revenus par mois (courbe)
    public function revenusParMois()
    {
        $revenus = Ticket::selectRaw(
            'MONTH(created_at) as mois,
             YEAR(created_at) as annee,
             SUM(mon_revenu) as total_revenu,
             SUM(nombre_ticket) as total_tickets'
        )
        ->whereYear('created_at', date('Y'))
        ->groupBy('annee', 'mois')
        ->orderBy('mois')
        ->get();

        return response()->json($revenus, 200);
    }

    // Statistiques globales tickets
    public function statistiques()
    {
        $stats = [
            'total_tickets'   => Ticket::count(),
            'total_revenus'   => Ticket::sum('mon_revenu'),
            'wifi_box'        => Ticket::where('type_wifi', 'wifi_box')->count(),
            'starlink'        => Ticket::where('type_wifi', 'starlink')->count(),
        ];

        return response()->json($stats, 200);
    }
}
