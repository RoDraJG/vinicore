<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AufgabenZuordnung extends Model
{
    protected $table = 'aufgaben_zuordnungen';
    
    protected $fillable = [
        'aufgabe_id',
        'arbeitskraft_id',
        'zugewiesen_am',
        'angenommen_am',
        'abgeschlossen_am',
        'prioritaet',
        'status',
        'notizen',
    ];
    
    protected $casts = [
        'zugewiesen_am' => 'datetime',
        'angenommen_am' => 'datetime',
        'abgeschlossen_am' => 'datetime',
    ];

    /**
     * Assignment belongs to a task
     */
    public function aufgabe(): BelongsTo
    {
        return $this->belongsTo(Aufgabe::class, 'aufgabe_id');
    }

    /**
     * Assignment belongs to a worker
     */
    public function arbeitskraft(): BelongsTo
    {
        return $this->belongsTo(Arbeitskraft::class, 'arbeitskraft_id');
    }
}
