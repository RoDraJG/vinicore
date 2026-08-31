<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KellerLaborwert extends Model
{
    protected $table = 'keller_laborwerte';
    
    protected $fillable = [
        'gaarprozess_id',
        'analysedatum',
        'labor_name',
        'alkohol_vol_prozent',
        'gesamtsaeure_g_l',
        'fluechtbige_saeure_g_l',
        'freier_so2_mg_l',
        'gesamt_so2_mg_l',
        'restzucker_g_l',
        'ph_wert',
        'extrakt_g_l',
        'zusatzliche_parameter',
    ];
    
    protected $casts = [
        'analysedatum' => 'date',
    ];

    /**
     * Lab result belongs to a fermentation process
     */
    public function gaarprozess(): BelongsTo
    {
        return $this->belongsTo(Gaarprozess::class, 'gaarprozess_id');
    }
}
