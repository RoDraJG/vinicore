<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Akkordleistung extends Model
{
    protected $table = 'akkordleistungen';
    
    protected $fillable = [
        'arbeitskraft_id',
        'lesetermin_id',
        'leistung_datum',
        'menge_kisten_oder_kg',
        'einheit',
        'betrag_pro_einheit',
        'gesamtbetrag',
        'notizen',
    ];
    
    protected $casts = [
        'leistung_datum' => 'date',
    ];

    /**
     * Piece-rate record belongs to a worker
     */
    public function arbeitskraft(): BelongsTo
    {
        return $this->belongsTo(Arbeitskraft::class, 'arbeitskraft_id');
    }

    /**
     * Piece-rate record may be linked to a harvest assignment
     */
    public function lesetermin(): BelongsTo
    {
        return $this->belongsTo(Lesetermin::class, 'lesetermin_id');
    }
}
