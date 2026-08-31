<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lohnabrechnung extends Model
{
    protected $table = 'lohnabrechnungen';
    
    protected $fillable = [
        'arbeitskraft_id',
        'betrieb_id',
        'abrechnungsmonat',
        'abrechnungsjahr',
        'gesamtstunden',
        'stundenlohn_euro',
        'grundlohn_euro',
        'akkordlohn_euro',
        'bonuszulage_euro',
        'abzuege_euro',
        'nettoauszahlung_euro',
        'status',
        'bezahlungsdatum',
        'zahlungsart',
        'notizen',
    ];
    
    protected $casts = [
        'bezahlungsdatum' => 'datetime',
    ];

    /**
     * Payroll belongs to a worker
     */
    public function arbeitskraft(): BelongsTo
    {
        return $this->belongsTo(Arbeitskraft::class, 'arbeitskraft_id');
    }

    /**
     * Payroll belongs to a farm
     */
    public function betrieb(): BelongsTo
    {
        return $this->belongsTo(User::class, 'betrieb_id');
    }
}
