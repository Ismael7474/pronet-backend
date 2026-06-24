<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $fillable = [
        'id_client',
        'type_abonnement',
        'montant',
        'date_debut',
        'date_expiration',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_expiration'   => 'date',
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
                     ->whereDate('date_expiration', '<=', now()->addDays($jours))
                     ->whereDate('date_expiration', '>=', now());
    }

    // Scope abonnements expirés
    public function scopeExpires($query)
    {
        return $query->where('statut', 'actif')
                     ->whereDate('date_expiration', '<', now());
    }
}
