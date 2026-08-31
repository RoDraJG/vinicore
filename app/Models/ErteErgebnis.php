<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErteErgebnis extends Model
{
    protected $table = 'ernte_ergebnisse';
    
    protected $fillable = [
        'lesegan_id',
        'menge_kg',
        'lesegut_zustand',
        'qualitaetsnotiz',
        'durchschnittliche_restzucker_g_l',
    ];

    /**
     * Outcome belongs to a harvest pass
     */
    public function lesegan(): BelongsTo
    {
        return $this->belongsTo(Lesegan::class, 'lesegan_id');
    }
}
