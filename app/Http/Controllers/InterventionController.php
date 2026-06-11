<?php

namespace App\Http\Controllers;

use App\Models\Intervention;
use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterventionController extends Controller
{
    // Liste toutes les interventions
    public function index()
    {
        $interventions = Intervention::with(['client', 'techniciens'])
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        return response()->json($interventions, 200);
    }

    // Créer une intervention
    public function store(Request $request)
    {
        $request->validate([
            'titre'          => 'required|string|max:200',
            'description'    => 'nullable|string',
            'type'           => 'required|in:panne,installation_wifi,installation_camera,maintenance',
            'priorite'       => 'required|in:basse,normale,urgente',
            'visite_requise' => 'required|in:oui,non',
            'id_client'      => 'required|exists:clients,id',
        ]);

        $intervention = Intervention::create($request->all());

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a créé une intervention',
            'intervention',
            $intervention->id,
            $intervention->id_client
        );

        return response()->json($intervention, 201);
    }

    // Afficher une intervention
    public function show($id)
    {
        $intervention = Intervention::with([
            'client',
            'techniciens',
            'rapportVisite',
            'rapportIntervention'
        ])->findOrFail($id);

        return response()->json($intervention, 200);
    }

    // Modifier une intervention
    public function update(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);

        $request->validate([
            'titre'          => 'sometimes|string|max:200',
            'description'    => 'nullable|string',
            'type'           => 'sometimes|in:panne,installation_wifi,installation_camera,maintenance',
            'priorite'       => 'sometimes|in:basse,normale,urgente',
            'visite_requise' => 'sometimes|in:oui,non',
            'id_client'      => 'sometimes|exists:clients,id',
        ]);

        $intervention->update($request->all());

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a modifié une intervention',
            'intervention',
            $intervention->id,
            $intervention->id_client
        );

        return response()->json($intervention, 200);
    }

    // Changer le statut
    public function changerStatut(Request $request, $id)
    {
        $intervention = Intervention::findOrFail($id);

        $request->validate([
            'statut' => 'required|in:en_attente,visite_prevue,visite_faite,valide,en_cours,termine,annule,archive',
        ]);

        $intervention->update(['statut' => $request->statut]);

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a changé le statut en ' . $request->statut,
            'intervention',
            $intervention->id,
            $intervention->id_client
        );

        return response()->json($intervention, 200);
    }

    // Interventions par type et par mois (courbe)
    public function statistiques()
    {
        $stats = Intervention::selectRaw(
            'MONTH(created_at) as mois,
             YEAR(created_at) as annee,
             SUM(CASE WHEN type = "panne"
                 THEN 1 ELSE 0 END) as pannes,
             SUM(CASE WHEN type IN (
                 "installation_wifi",
                 "installation_camera")
                 THEN 1 ELSE 0 END) as installations'
        )
        ->whereYear('created_at', date('Y'))
        ->groupBy('annee', 'mois')
        ->orderBy('mois')
        ->get();

        return response()->json($stats, 200);
    }

    // Supprimer une intervention
    public function destroy($id)
    {
        $intervention = Intervention::findOrFail($id);
        $intervention->delete();

        return response()->json([
            'message' => 'Intervention supprimée avec succès'
        ], 200);
    }

    // Interventions du technicien connecté
    public function mesTaches()
    {
        $interventions = Intervention::with(['client'])
            ->whereHas('techniciens', function ($query) {
                $query->where('id_user', Auth::id());
            })
            ->where('statut', '!=', 'archive')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($interventions, 200);
    }
}
