<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $fillable = [
        'id_client',
        'type_abonnement',
        'prix_mensuel',
        'date_debut',
        'date_fin',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    // Un abonnement appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    // Scope abonnements expirants dans X jours
    public function scopeExpirantDans($query, $jours)
    {
        return $query->where('statut', 'actif')
                     ->whereDate('date_fin', '<=', now()->addDays($jours))
                     ->whereDate('date_fin', '>=', now());
    }

    // Scope abonnements expirés
    public function scopeExpires($query)
    {
        return $query->where('statut', 'actif')
                     ->whereDate('date_fin', '<', now());
    }
}
