<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterventionUser extends Model
{

    protected $fillable = [
        'intervention_id',
        'user_id',
    ];

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
