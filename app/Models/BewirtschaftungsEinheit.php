<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BewirtschaftungsEinheit extends Model
{
    protected $table = 'bewirtschaftungs_einheiten';
    protected $fillable = ['parzelle_id', 'name'];

    public function parzelle(): BelongsTo
    {
        return $this->belongsTo(Parzelle::class);
    }

    public function schlaege(): HasMany
    {
        return $this->hasMany(Schlag::class);
    }
}
