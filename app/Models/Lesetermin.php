<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesetermin extends Model
{
    protected $table = 'lesetermine';
    
    protected $fillable = [
        'lesegan_id',
        'arbeiter_id',
        'reifegrad_prozent',
        'geplante_menge_kg',
        'status',
    ];

    /**
     * Assignment belongs to a harvest pass
     */
    public function lesegan(): BelongsTo
    {
        return $this->belongsTo(Lesegan::class, 'lesegan_id');
    }

    /**
     * Assignment belongs to a worker
     */
    public function arbeiter(): BelongsTo
    {
        return $this->belongsTo(Arbeitskraft::class, 'arbeiter_id');
    }
}
