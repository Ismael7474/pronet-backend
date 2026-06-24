<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Liste des notifications
    public function index()
    {
            $notifications = Notification::where(
                'id_user',
                Auth::id()
            )
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    // Nombre de notifications non lues
    public function nonLues()
    {
        return response()->json([
            'count' => Notification::where('lu', false)->count()
        ]);
    }

    // Marquer comme lue
    public function marquerCommeLue($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'lu' => true
        ]);

        return response()->json([
            'message' => 'Notification marquée comme lue'
        ]);
    }

    // Marquer toutes comme lues
    public function toutMarquerCommeLu()
    {
        Notification::where('lu', false)
            ->update([
                'lu' => true
            ]);

        return response()->json([
            'message' => 'Toutes les notifications ont été marquées comme lues'
        ]);
    }
}
