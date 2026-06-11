<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiviteController extends Controller
{
    // Liste toutes les activites
    public function index(Request $request)
    {
        $query = Activite::with(['user', 'intervention', 'client'])
                         ->orderBy('created_at', 'desc');

        // Filtre par module
        if ($request->has('module')) {
            $query->where('module', $request->module);
        }

        // Filtre par user
        if ($request->has('id_user')) {
            $query->where('id_user', $request->id_user);
        }

        // Filtre par date
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filtre par période
        if ($request->has('debut') && $request->has('fin')) {
            $query->whereBetween('created_at', [
                $request->debut,
                $request->fin
            ]);
        }

        $activites = $query->paginate(20);

        return response()->json($activites, 200);
    }

    // Activites récentes (30 derniers jours)
    public function recentes()
    {
        $activites = Activite::with(['user', 'client'])
                             ->where('created_at', '>=', now()->subDays(30))
                             ->orderBy('created_at', 'desc')
                             ->take(50)
                             ->get();

        return response()->json($activites, 200);
    }

    // Activites d'un technicien
    public function parTechnicien($id)
    {
        $activites = Activite::with(['intervention', 'client'])
                             ->where('id_user', $id)
                             ->orderBy('created_at', 'desc')
                             ->get();

        return response()->json($activites, 200);
    }

    // Activites d'un client
    public function parClient($id)
    {
        $activites = Activite::with(['user', 'intervention'])
                             ->where('id_client', $id)
                             ->orderBy('created_at', 'desc')
                             ->get();

        return response()->json($activites, 200);
    }
}
