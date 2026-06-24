<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Activite;
use Illuminate\Http\Request;
use app\Models\Notification;

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
            'type_abonnement' => 'required|in:wifi_starlink,wifi_box',
            'montant'    => 'required|numeric|min:0',
            'date_debut'      => 'required|date',
            'date_expiration'        => 'required|date|after:date_debut',
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
            'type_abonnement' => 'sometimes|in:wifi_starlink,wifi_box',
            'montant'    => 'sometimes|numeric|min:0',
            'date_debut'      => 'sometimes|date',
            'date_expiration'        => 'sometimes|date|after:date_debut',
            'statut'          => 'sometimes|in:actif,expirant,expire,suspendu',
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
            'date_expiration'   => 'required|date|after:date_debut',
            'montant' => 'sometimes|numeric|min:0',
        ]);

        // Archiver l'ancien
        $ancien->update(['statut' => 'suspendu']);

        // Créer le nouveau
        $nouveau = Abonnement::create([
            'id_client'       => $ancien->id_client,
            'type_abonnement' => $ancien->type_abonnement,
            'montant'    => $request->montant ?? $ancien->montant,
            'date_debut'      => $request->date_debut,
            'date_expiration'        => $request->date_expiration,
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
                               ->orderBy('date_expiration')
                               ->get();

        return response()->json($expirants, 200);
    }

    // Mettre à jour les statuts automatiquement
    public function mettreAJourStatuts()
    {
        // Abonnements expirés
        Abonnement::whereIn('statut', ['actif', 'expirant'])
            ->whereDate('date_expiration', '<', now())
            ->update([
                'statut' => 'expire'
            ]);

        // Ceux qui arrivent à 7 jours
        $abonnements = Abonnement::with('client')
            ->where('statut', 'actif')
            ->whereDate('date_expiration', '<=', now()->addDays(7))
            ->whereDate('date_expiration', '>=', now())
            ->get();

        foreach ($abonnements as $abonnement) {

            // Passage à expirant
            $abonnement->update([
                'statut' => 'expirant'
            ]);

            // Notification interne
            Notification::create([
                'titre' => 'Abonnement expirant',
                'message' => 'L\'abonnement de '
                    . $abonnement->client->nom .
                    ' expire dans 7 jours.',
                'type' => 'abonnement',
                'lu' => false
            ]);

            // WhatsApp
            $numero = $abonnement->client->telephone;

            $message =
                "Bonjour {$abonnement->client->nom}, "
                ."votre abonnement internet expire le "
                .$abonnement->date_expiration
                .". Merci de penser à son renouvellement.";

            $lienWhatsapp =
                "https://wa.me/226{$numero}?text="
                . urlencode($message);

            // Ici plus tard on utilisera Twilio
            // ou WhatsApp Business API
        }

        return response()->json([
            'message' => 'Statuts mis à jour'
        ]);
    }
    public function verifierExpirations()
    {
            $abonnements = Abonnement::with('client')
                ->where('statut', 'actif')
                ->whereDate('date_expiration', now()->addDays(7))
                ->get();

            foreach ($abonnements as $abonnement) {

                Notification::create([
                    'titre' => 'Abonnement expirant',
                    'message' =>
                        $abonnement->client->nom .
                        ' expire dans 7 jours'
                ]);

                // futur envoi WhatsApp ici
            }

            return response()->json([
                'message' => 'Vérification terminée'
            ]);
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
