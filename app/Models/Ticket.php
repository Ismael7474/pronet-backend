<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'client_id',
        'type_wifi',
        'nombre_ticket',
        'prix_unitaire',
        'mon_revenu',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }


    // Scope pour les revenus par mois
    public function scopeRevenusParMois($query, $annee)
    {
        return $query->selectRaw(
            'MONTH(created_at) as mois,
             SUM(mon_revenu) as total_revenu'
        )
        ->whereYear('created_at', $annee)
        ->groupBy('mois')
        ->orderBy('mois');
    }
}
