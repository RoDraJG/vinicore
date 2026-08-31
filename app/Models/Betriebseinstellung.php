<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Betriebseinstellung extends Model
{
    protected $table = 'betriebseinstellungen';
    
    protected $fillable = [
        'betrieb_id',
        'vier_augen_kataster',
        'standard_allokation',
        'farb_schema_eigentum',
        'farb_schema_gepachtet',
        'farb_schema_verpachtet',
        'zeitzone',
        'sprache',
    ];
    
    protected $casts = [
        'vier_augen_kataster' => 'boolean',
        'standard_allokation' => 'decimal:2',
    ];

    /**
     * Settings belong to a business/farm
     */
    public function betrieb(): BelongsTo
    {
        return $this->belongsTo(User::class, 'betrieb_id');
    }
}
