<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gaarprozess extends Model
{
    protected $table = 'gaarprozesse';
    
    protected $fillable = [
        'fass_id',
        'lesegan_id',
        'wein_id',
        'rebsorte',
        'start_datum',
        'ende_datum',
        'status',
        'anfangsgewicht_oechsle',
        'anfangstemperatur_celsius',
        'notizen',
    ];
    
    protected $casts = [
        'start_datum' => 'date',
        'ende_datum' => 'date',
    ];

    /**
     * Fermentation process belongs to a tank
     */
    public function fass(): BelongsTo
    {
        return $this->belongsTo(Gaarfass::class, 'fass_id');
    }

    /**
     * Fermentation process belongs to a harvest pass
     */
    public function lesegan(): BelongsTo
    {
        return $this->belongsTo(Lesegan::class, 'lesegan_id');
    }

    /**
     * Process has many measurements
     */
    public function messwerte(): HasMany
    {
        return $this->hasMany(GaarMesswert::class, 'gaarprozess_id')
                    ->orderBy('messdatum', 'asc');
    }

    /**
     * Process has many cellar treatments
     */
    public function behandlungen(): HasMany
    {
        return $this->hasMany(KellerBehandlung::class, 'gaarprozess_id');
    }

    /**
     * Process has many lab analyses
     */
    public function laborwerte(): HasMany
    {
        return $this->hasMany(KellerLaborwert::class, 'gaarprozess_id');
    }
}
