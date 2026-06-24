<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Liste des utilisateurs
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();

        return response()->json($users, 200);
    }

    // Créer un utilisateur
    public function store(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6',
            'role'      => 'required|in:admin,technicien',
            'telephone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'nom'       => $request->nom,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'telephone' => $request->telephone,
        ]);

        Activite::enregistrer(
            'a créé un utilisateur',
            'utilisateur',
            null,
            null
        );

        return response()->json($user, 201);
    }

    // Afficher un utilisateur
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json($user, 200);
    }

    // Modifier un utilisateur
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nom'       => 'sometimes|string|max:100',
            'email'     => 'sometimes|email|unique:users,email,' . $id,
            'role'      => 'sometimes|in:admin,technicien',
            'telephone' => 'nullable|string|max:20',
            'password'  => 'nullable|min:6',
        ]);

        $data = $request->only([
            'nom',
            'email',
            'role',
            'telephone'
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make(
                $request->password
            );
        }

        $user->update($data);

        Activite::enregistrer(
            'a modifié un utilisateur',
            'utilisateur',
            null,
            null
        );

        return response()->json($user, 200);
    }

    // Supprimer un utilisateur
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Empêcher suppression du dernier admin
        if ($user->role === 'admin') {

            $nbAdmins = User::where(
                'role',
                'admin'
            )->count();

            if ($nbAdmins <= 1) {
                return response()->json([
                    'message' => 'Impossible de supprimer le dernier administrateur'
                ], 422);
            }
        }

        $user->delete();

        Activite::enregistrer(
            'a supprimé un utilisateur',
            'utilisateur',
            null,
            null
        );

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ], 200);
    }
}
