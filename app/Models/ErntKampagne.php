<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErntKampagne extends Model
{
    protected $table = 'ernte_kampagnen';
    
    protected $fillable = [
        'betrieb_id',
        'jahr',
        'status',
        'geplanter_start',
        'geplantes_ende',
        'tatsachlicher_start',
        'tatsachliches_ende',
        'notizen',
    ];
    
    protected $casts = [
        'geplanter_start' => 'date',
        'geplantes_ende' => 'date',
        'tatsachlicher_start' => 'date',
        'tatsachliches_ende' => 'date',
    ];

    /**
     * Harvest campaign belongs to a business/farm
     */
    public function betrieb(): BelongsTo
    {
        return $this->belongsTo(User::class, 'betrieb_id');
    }

    /**
     * Campaign has many harvest passes
     */
    public function lesegaenge(): HasMany
    {
        return $this->hasMany(Lesegan::class, 'kampagne_id');
    }
}
