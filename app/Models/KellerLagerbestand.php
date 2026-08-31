<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KellerLagerbestand extends Model
{
    protected $table = 'keller_lagerbestaende';
    
    protected $fillable = [
        'betrieb_id',
        'artikel_id',
        'artikel_name',
        'standort',
        'menge_l',
        'restmenge_l',
        'abfuell_datum',
        'abfuell_menge_flaschen',
        'kosten_euro',
    ];
    
    protected $casts = [
        'abfuell_datum' => 'date',
    ];

    /**
     * Inventory belongs to a farm
     */
    public function betrieb(): BelongsTo
    {
        return $this->belongsTo(User::class, 'betrieb_id');
    }
}
