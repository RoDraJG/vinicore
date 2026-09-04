<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class CRMEinstellung extends Model
{
    protected $table = 'crm_einstellungen';

    // 🎯 REPARATUR: Schaltet die neuen Historisierungs- und Musterfelder für Eloquent frei
    protected $fillable = [
        'typ', 
        'code', 
        'wert', 
        'sortierung',
        'modul_key', 
        'kreis_key', 
        'muster', 
        'zaehlerstand', 
        'fuehrende_nullen',
        'gueltig_von', 
        'gueltig_bis'
    ];

    // Automatische Datums-Konvertierung für die Zeitfenster aktivieren
    protected $casts = [
        'gueltig_von' => 'datetime',
        'gueltig_bis' => 'datetime',
    ];
}
