<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schlag extends Model
{
    protected $table = 'schlaege';

    protected $fillable = [
        'name',
        'flaeche_ha',
        'bodenart',
        'letzte_bodenprobe'
    ];

    /**
     * Ein Schlag kapselt eine oder viele homogene biologische Anlagen.
     */
    public function anlagen(): HasMany
    {
        return $this->hasMany(Anlage::class, 'schlag_id');
    }
}
