<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParzelleVertrag extends Model
{
    protected $table = 'parzelle_vertrag';
    
    protected $fillable = [
        'parzelle_uuid',
        'vertragable_id',
        'vertragable_type',
        'zugeordneter_wert',
        'zugeordnete_flaeche_m2',
    ];

    /**
     * Get the parent vertragable model (VinicoreVertrag, etc.)
     */
    public function vertragable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the associated parcel
     */
    public function parzelle(): BelongsTo
    {
        return $this->belongsTo(Parzelle::class, 'parzelle_uuid', 'parzelle_uuid');
    }
}
