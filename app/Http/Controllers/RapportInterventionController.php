<?php

namespace App\Http\Controllers;

use App\Models\RapportIntervention;
use App\Models\Intervention;
use App\Models\Activite;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RapportInterventionController extends Controller
{
    // Liste tous les rapports d'intervention
    public function index()
    {
        $rapports = RapportIntervention::with([
            'intervention.client',
            'user'
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($rapports, 200);
    }

    // Créer un rapport d'intervention
    public function store(Request $request)
    {
        $request->validate([
            'id_intervention'    => 'required|exists:interventions,id',
            'travail_effectue'   => 'required|string',
            'resultat'           => 'required|in:resolu,partiel,non_resolu',
            'observations'       => 'nullable|string',
            'duree_intervention' => 'nullable|integer',
        ]);

        // Vérifier qu'il n'existe pas déjà un rapport
        $existe = RapportIntervention::where(
            'id_intervention',
            $request->id_intervention
        )->first();

        if ($existe) {
            return response()->json([
                'message' => 'Un rapport existe déjà pour cette intervention'
            ], 422);
        }

        $rapport = RapportIntervention::create([
            'id_intervention'    => $request->id_intervention,
            'id_user'            => Auth::id(),
            'travail_effectue'   => $request->travail_effectue,
            'resultat'           => $request->resultat,
            'observations'       => $request->observations,
            'duree_intervention' => $request->duree_intervention,
        ]);

        // Mettre à jour le statut de l'intervention
        $intervention = Intervention::findOrFail($request->id_intervention);
        $intervention->update(['statut' => 'termine']);

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a soumis un rapport d\'intervention',
            'intervention',
            $intervention->id,
            $intervention->id_client
        );
        Notification::create([
            'titre' => 'Rapport intervention',
            'message' =>
                Auth::user()->nom .
                ' a terminé l’intervention ' .
                $intervention->titre
        ]);
        // Enregistrer dans intervention_user
        $intervention->techniciens()->syncWithoutDetaching([Auth::id()]);

        return response()->json($rapport->load(['intervention', 'user']), 201);
    }

    // Afficher un rapport d'intervention
    public function show($id)
    {
        $rapport = RapportIntervention::with(['intervention.client', 'user'])
                                      ->findOrFail($id);

        return response()->json($rapport, 200);
    }

    // Rapport d'une intervention
    public function parIntervention($id_intervention)
    {
        $rapport = RapportIntervention::with(['user'])
                                      ->where('id_intervention', $id_intervention)
                                      ->first();

        return response()->json($rapport, 200);
    }
}
