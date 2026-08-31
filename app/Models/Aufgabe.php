<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aufgabe extends Model
{
    protected $table = 'aufgaben';
    
    protected $fillable = [
        'betrieb_id',
        'titel',
        'beschreibung',
        'modul_kontext',
        'related_entity_id',
        'erstellt_am',
        'faellig_am',
        'prioritaet',
        'zustaendig_rolle',
        'status',
    ];
    
    protected $casts = [
        'erstellt_am' => 'datetime',
        'faellig_am' => 'datetime',
    ];

    /**
     * Task belongs to a farm
     */
    public function betrieb(): BelongsTo
    {
        return $this->belongsTo(User::class, 'betrieb_id');
    }

    /**
     * Task has many worker assignments
     */
    public function zuordnungen(): HasMany
    {
        return $this->hasMany(AufgabenZuordnung::class, 'aufgabe_id');
    }
}
