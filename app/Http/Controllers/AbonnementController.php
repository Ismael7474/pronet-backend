<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Activite;
use Illuminate\Http\Request;

class AbonnementController extends Controller
{
    // Liste tous les abonnements
    public function index()
    {
        $abonnements = Abonnement::with('client')
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        return response()->json($abonnements, 200);
    }

    // Créer un abonnement
    public function store(Request $request)
    {
        $request->validate([
            'id_client'       => 'required|exists:clients,id',
            'type_abonnement' => 'required|in:wifi_starlink,camera,mixte',
            'prix_mensuel'    => 'required|numeric|min:0',
            'date_debut'      => 'required|date',
            'date_fin'        => 'required|date|after:date_debut',
        ]);

        $abonnement = Abonnement::create($request->all());

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a créé un abonnement',
            'abonnement',
            null,
            $abonnement->id_client
        );

        return response()->json($abonnement->load('client'), 201);
    }

    // Afficher un abonnement
    public function show($id)
    {
        $abonnement = Abonnement::with('client')->findOrFail($id);

        return response()->json($abonnement, 200);
    }

    // Modifier un abonnement
    public function update(Request $request, $id)
    {
        $abonnement = Abonnement::findOrFail($id);

        $request->validate([
            'type_abonnement' => 'sometimes|in:wifi_starlink,camera,mixte',
            'prix_mensuel'    => 'sometimes|numeric|min:0',
            'date_debut'      => 'sometimes|date',
            'date_fin'        => 'sometimes|date|after:date_debut',
            'statut'          => 'sometimes|in:actif,expirant,expire,historique',
        ]);

        $abonnement->update($request->all());

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a modifié un abonnement',
            'abonnement',
            null,
            $abonnement->id_client
        );

        return response()->json($abonnement, 200);
    }

    // Renouveler un abonnement
    public function renouveler(Request $request, $id)
    {
        $ancien = Abonnement::findOrFail($id);

        $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after:date_debut',
            'prix_mensuel' => 'sometimes|numeric|min:0',
        ]);

        // Archiver l'ancien
        $ancien->update(['statut' => 'historique']);

        // Créer le nouveau
        $nouveau = Abonnement::create([
            'id_client'       => $ancien->id_client,
            'type_abonnement' => $ancien->type_abonnement,
            'prix_mensuel'    => $request->prix_mensuel ?? $ancien->prix_mensuel,
            'date_debut'      => $request->date_debut,
            'date_fin'        => $request->date_fin,
            'statut'          => 'actif',
        ]);

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a renouvelé un abonnement',
            'abonnement',
            null,
            $nouveau->id_client
        );

        return response()->json($nouveau->load('client'), 201);
    }

    // Abonnements expirants (pour le tableau de bord)
    public function expirants()
    {
        $expirants = Abonnement::with('client')
                               ->expirantDans(15)
                               ->orderBy('date_fin')
                               ->get();

        return response()->json($expirants, 200);
    }

    // Mettre à jour les statuts automatiquement
    public function mettreAJourStatuts()
    {
        // Expirés
        Abonnement::where('statut', 'actif')
                  ->whereDate('date_fin', '<', now())
                  ->update(['statut' => 'expire']);

        // Expirants dans 15 jours
        Abonnement::where('statut', 'actif')
                  ->whereDate('date_fin', '<=', now()->addDays(15))
                  ->whereDate('date_fin', '>=', now())
                  ->update(['statut' => 'expirant']);

        return response()->json([
            'message' => 'Statuts mis à jour avec succès'
        ], 200);
    }

    // Supprimer un abonnement
    public function destroy($id)
    {
        $abonnement = Abonnement::findOrFail($id);
        $abonnement->delete();

        return response()->json([
            'message' => 'Abonnement supprimé avec succès'
        ], 200);
    }
}
