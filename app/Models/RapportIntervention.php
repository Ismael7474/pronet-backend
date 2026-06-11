<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RapportIntervention extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id_intervention',
        'id_user',
        'travail_effectue',
        'resultat',
        'observations',
        'duree_intervention',
    ];

    // Un rapport appartient à une intervention
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'id_intervention');
    }

    // Un rapport appartient à un user (technicien ou gérant)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
