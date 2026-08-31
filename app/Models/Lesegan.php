<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesegan extends Model
{
    protected $table = 'lesegaenge';
    
    protected $fillable = [
        'kampagne_id',
        'schlag_id',
        'lesedatum',
        'lesebeginn',
        'leseende',
        'mostgewicht_grad_oechsle',
        'gesamtsaeure_g_l',
        'ph_wert',
        'leseart',
        'notizen',
    ];
    
    protected $casts = [
        'lesedatum' => 'date',
        'lesebeginn' => 'time',
        'leseende' => 'time',
    ];

    /**
     * Harvest pass belongs to a campaign
     */
    public function kampagne(): BelongsTo
    {
        return $this->belongsTo(ErntKampagne::class, 'kampagne_id');
    }

    /**
     * Harvest pass has many worker assignments
     */
    public function lesetermine(): HasMany
    {
        return $this->hasMany(Lesetermin::class, 'lesegan_id');
    }

    /**
     * Harvest pass has one outcome record
     */
    public function ergebnis(): HasOne
    {
        return $this->hasOne(ErteErgebnis::class, 'lesegan_id');
    }
}
