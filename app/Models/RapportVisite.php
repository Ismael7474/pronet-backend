<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RapportVisite extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id_intervention',
        'id_user',
        'observations',
        'materiel_necessaire',
        'estimation_cout',
        'faisable',
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
