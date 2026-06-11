<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    protected $fillable = [
        'titre',
        'description',
        'type',
        'priorite',
        'statut',
        'visite_requise',
        'id_client',
    ];

    // Une intervention appartient à un client
    public function client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    // Une intervention peut avoir plusieurs techniciens
    public function techniciens()
    {
        return $this->belongsToMany(
            User::class,
            'intervention_user',
            'intervention_id',
            'user_id'
        );
    }

    // Une intervention peut avoir un rapport de visite
    public function rapportVisite()
    {
        return $this->hasOne(RapportVisite::class, 'id_intervention');
    }

    // Une intervention peut avoir un rapport d'intervention
    public function rapportIntervention()
    {
        return $this->hasOne(RapportIntervention::class, 'id_intervention');
    }

    // Une intervention peut avoir plusieurs activites
    public function activites()
    {
        return $this->hasMany(Activite::class, 'id_intervention');
    }
}
