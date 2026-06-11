<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'nom',
        'telephone',
        'email',
        'adresse',
        'type_client',
        'localisation_url',
    ];

    // Un client peut avoir plusieurs interventions
    public function interventions()
    {
        return $this->hasMany(Intervention::class, 'id_client');
    }

    // Un client peut avoir plusieurs abonnements
    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'id_client');
    }

    // Un client peut avoir plusieurs tickets
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'id_client');
    }

    // Un client peut avoir plusieurs activites
    public function activites()
    {
        return $this->hasMany(Activite::class, 'id_client');
    }
}
