<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RapportIntervention extends Model
{
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
