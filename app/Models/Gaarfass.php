<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gaarfass extends Model
{
    protected $table = 'gaarfaesser';
    
    protected $fillable = [
        'betrieb_id',
        'fass_nr',
        'fass_typ',
        'volumen_l',
        'standort',
        'status',
        'erwerb_datum',
        'erstellungsjahr',
        'notizen',
    ];
    
    protected $casts = [
        'erwerb_datum' => 'date',
    ];

    /**
     * Tank belongs to a farm
     */
    public function betrieb(): BelongsTo
    {
        return $this->belongsTo(User::class, 'betrieb_id');
    }

    /**
     * Tank has many fermentation processes
     */
    public function gaarprozesse(): HasMany
    {
        return $this->hasMany(Gaarprozess::class, 'fass_id');
    }
}
