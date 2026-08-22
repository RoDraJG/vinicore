<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class VinicoreVertrag extends Model
{
    protected $table = 'vinicore_vertraege';
    protected $guarded = [];

    /**
     * Holt alle Parzellen-Verknüpfungen, die zu diesem Vertrag gehören.
     */
    public function parzellenZuordnungen(): MorphMany
    {
        return $this->morphMany(ParzelleVertrag::class, 'vertragable');
    }
}
