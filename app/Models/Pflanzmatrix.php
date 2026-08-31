<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pflanzmatrix extends Model
{
    protected $table = 'pflanzmatrizen';

    // Wir deklarieren die parzelle_uuid als primären Erkennungsschlüssel für Relationen
    protected $primaryKey = 'id';

    protected $fillable = [
        'parzelle_uuid',
        'version',
        'polygon_vektoren',
        'gemeinde',
        'gemarkung',
        'flur',
        'flurstueck_zaehler',
        'flurstueck_nenner',
        'flurname_lage',
        'amtliche_flaeche_m2',
        'aenderungsgrund',
        'gueltig_von',
        'gueltig_bis'
    ];

    protected $casts = [
        'polygon_vektoren' => 'array' // Konvertiert JSON beim Laden automatisch in ein PHP-Array
    ];

    /**
     * Eine Parzelle kann im Laufe der Zeit von verschiedenen biologischen Anlagen genutzt werden.
     */
    public function anlagen(): BelongsToMany
    {
        return $this->belongsToMany(Anlage::class, 'parzelle_anlage', 'parzelle_uuid', 'anlage_id', 'parzelle_uuid', 'id')
                    ->withPivot(['anteil_prozent', 'ist_geplant', 'gerodet_am', 'pflanz_richtung'])
                    ->withTimestamps();
    }
}
