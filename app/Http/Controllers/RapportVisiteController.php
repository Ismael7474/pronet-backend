<?php

namespace App\Http\Controllers;

use App\Models\RapportVisite;
use App\Models\Intervention;
use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RapportVisiteController extends Controller
{
    // Liste tous les rapports de visite
    public function index()
    {
        $rapports = RapportVisite::with(['intervention', 'user'])
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        return response()->json($rapports, 200);
    }

    // Créer un rapport de visite
    public function store(Request $request)
    {
        $request->validate([
            'id_intervention'     => 'required|exists:interventions,id',
            'observations'        => 'required|string',
            'materiel_necessaire' => 'required|string',
            'estimation_cout'     => 'nullable|numeric',
            'faisable'            => 'required|in:oui,non',
        ]);

        // Vérifier qu'il n'existe pas déjà un rapport
        $existe = RapportVisite::where(
            'id_intervention',
            $request->id_intervention
        )->first();

        if ($existe) {
            return response()->json([
                'message' => 'Un rapport de visite existe déjà pour cette intervention'
            ], 422);
        }

        $rapport = RapportVisite::create([
            'id_intervention'     => $request->id_intervention,
            'id_user'             => Auth::id(),
            'observations'        => $request->observations,
            'materiel_necessaire' => $request->materiel_necessaire,
            'estimation_cout'     => $request->estimation_cout,
            'faisable'            => $request->faisable,
            'created_at'          => now(),
        ]);

        // Mettre à jour le statut de l'intervention
        $intervention = Intervention::findOrFail($request->id_intervention);
        $intervention->update(['statut' => 'visite_faite']);

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a soumis un rapport de visite',
            'intervention',
            $intervention->id,
            $intervention->id_client
        );

        // Enregistrer dans intervention_user
        $intervention->techniciens()->syncWithoutDetaching([Auth::id()]);

        return response()->json($rapport->load(['intervention', 'user']), 201);
    }

    // Afficher un rapport de visite
    public function show($id)
    {
        $rapport = RapportVisite::with(['intervention.client', 'user'])
                                ->findOrFail($id);

        return response()->json($rapport, 200);
    }

    // Rapport de visite d'une intervention
    public function parIntervention($id_intervention)
    {
        $rapport = RapportVisite::with(['user'])
                                ->where('id_intervention', $id_intervention)
                                ->first();

        return response()->json($rapport, 200);
    }
}
