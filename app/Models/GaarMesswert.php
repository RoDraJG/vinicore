<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaarMesswert extends Model
{
    protected $table = 'gaar_messwerte';
    
    protected $fillable = [
        'gaarprozess_id',
        'messdatum',
        'temperatur_celsius',
        'dichte',
        'alkohol_prozent',
        'restzucker_g_l',
        'beobachter',
        'notizen',
    ];
    
    protected $casts = [
        'messdatum' => 'datetime',
    ];

    /**
     * Measurement belongs to a fermentation process
     */
    public function gaarprozess(): BelongsTo
    {
        return $this->belongsTo(Gaarprozess::class, 'gaarprozess_id');
    }
}
