<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Activite;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Liste tous les clients
    public function index()
    {
        $clients = Client::withCount(['interventions', 'abonnements'])
                         ->orderBy('created_at', 'desc')
                         ->get();
        return response()->json($clients, 200);

    }

    // Créer un client
    public function store(Request $request)
    {
        $request->validate([
            'nom'        => 'required|string|max:100',
            'telephone'  => 'required|string|unique:clients|max:20',
            'email'      => 'nullable|email|max:150',
            'adresse'    => 'required|string|max:255',
            'type_client'=> 'required|in:particulier,entreprise',
            'localisation_url' => 'nullable|string|max:255',
        ]);

        $client = Client::create($request->all());

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a enregistré un nouveau client',
            'client',
            null,
            $client->id
        );

        return response()->json($client, 201);
    }

    // Afficher un client
    public function show($id)
    {
        $client = Client::with([
            'interventions',
            'abonnements',
            'tickets'
        ])->findOrFail($id);

        return response()->json($client, 200);
    }

    // Modifier un client
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $request->validate([
            'nom'        => 'sometimes|string|max:100',
            'telephone'  => 'sometimes|string|max:20|unique:clients,telephone,'.$id,
            'email'      => 'nullable|email|max:150',
            'adresse'    => 'sometimes|string|max:255',
            'type_client'=> 'sometimes|in:particulier,entreprise',
            'localisation_url' => 'nullable|string|max:255',
        ]);

        $client->update($request->all());

        // Enregistrer dans les activites
        Activite::enregistrer(
            'a modifié le client',
            'client',
            null,
            $client->id
        );

        return response()->json($client, 200);
    }

    // Supprimer un client
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->json([
            'message' => 'Client supprimé avec succès'
        ], 200);
    }

    // Rechercher un client
    public function search(Request $request)
    {
        $query = $request->get('q');

        $clients = Client::where('nom', 'like', "%{$query}%")
                         ->orWhere('telephone', 'like', "%{$query}%")
                         ->get();

        return response()->json($clients, 200);
    }
}
