<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activite extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'id_intervention',
        'id_client',
        'action',
        'module',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Une activite appartient à un user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Une activite appartient à une intervention
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'id_intervention');
    }

    // Une activite appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    // Méthode statique pour enregistrer une activite
    public static function enregistrer(
        $action,
        $module,
        $id_intervention = null,
        $id_client = null
    ) {
        self::create([
            'id_user'         => auth()->id(),
            'id_intervention' => $id_intervention,
            'id_client'       => $id_client,
            'action'          => $action,
            'module'          => $module,
            'created_at'      => now()
        ]);
    }
}
