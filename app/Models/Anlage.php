<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anlage extends Model
{
    // Da wir den Tabellennamen im Singular mit Unterstrich deklariert haben
    protected $table = 'anlagen';

    protected $fillable = [
        'schlag_id',
        'name',
        'plan_status',
        'locked_by_user_id',
        'locked_until',
        'vorgewende_start_cm',
        'vorgewende_ende_cm',
        'randabstand_links_cm',
        'randabstand_rechts_cm',
        'abstand_anker_endpfahl_cm',
        'abstand_endpfahl_rebe_cm',
        'ziel_gassenbreite_cm',
        'stockabstand_cm',
        'reihenpfahl_abstand_cm'
    ];

    /**
     * Jede Anlage gehört starr zu genau einem organisatorischen Schlag.
     */
    public function schlag(): BelongsTo
    {
        return $this->belongsTo(Schlag::class, 'schlag_id');
    }

    /**
     * Eine Anlage erstreckt sich über mehrere Katasterparzellen (Pivot-Verknüpfung).
     */
    public function parzellen(): BelongsToMany
    {
        return $this->belongsToMany(Parzelle::class, 'parzelle_anlage', 'anlage_id', 'parzelle_uuid', 'id', 'parzelle_uuid')
                    ->withPivot(['anteil_prozent', 'ist_geplant', 'gerodet_am', 'pflanz_richtung'])
                    ->withTimestamps();
    }

    /**
     * Eine Anlage besitzt viele Vektor-Punkte (Reben/Pfähle) in der Pflanzmatrix.
     */
    public function pflanzmatrix(): HasMany
    {
        return $this->hasMany(Pflanzmatrix::class, 'anlage_id');
    }
}
