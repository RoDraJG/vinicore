<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KellerBehandlung extends Model
{
    protected $table = 'keller_behandlungen';
    
    protected $fillable = [
        'gaarprozess_id',
        'durchgefuehrt_am',
        'behandlungstyp',
        'stoff_oder_massnahme',
        'menge',
        'einheit',
        'durchgefuehrt_von',
        'notizen',
    ];
    
    protected $casts = [
        'durchgefuehrt_am' => 'datetime',
    ];

    /**
     * Treatment belongs to a fermentation process
     */
    public function gaarprozess(): BelongsTo
    {
        return $this->belongsTo(Gaarprozess::class, 'gaarprozess_id');
    }
}
