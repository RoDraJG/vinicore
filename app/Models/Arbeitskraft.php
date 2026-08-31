<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arbeitskraft extends Model
{
    protected $table = 'arbeitskraefte';
    
    protected $fillable = [
        'betrieb_id',
        'benutzer_id',
        'vorname',
        'nachname',
        'rolle_im_betrieb',
        'qr_code',
        'personalnummer',
        'einstellungsdatum',
        'austritt_datum',
        'status',
        'kontakt_telefon',
        'kontakt_email',
        'notizen',
    ];
    
    protected $casts = [
        'einstellungsdatum' => 'date',
        'austritt_datum' => 'date',
    ];

    /**
     * Worker belongs to a farm
     */
    public function betrieb(): BelongsTo
    {
        return $this->belongsTo(User::class, 'betrieb_id');
    }

    /**
     * Worker may be linked to a user account
     */
    public function benutzer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'benutzer_id');
    }

    /**
     * Worker has many time clock entries
     */
    public function zeiterfassungen(): HasMany
    {
        return $this->hasMany(Zeiterfassung::class, 'arbeitskraft_id');
    }

    /**
     * Worker has many task assignments
     */
    public function aufgabenZuordnungen(): HasMany
    {
        return $this->hasMany(AufgabenZuordnung::class, 'arbeitskraft_id');
    }

    /**
     * Worker has many harvest assignments
     */
    public function lesetermine(): HasMany
    {
        return $this->hasMany(Lesetermin::class, 'arbeiter_id');
    }

    /**
     * Worker has many piece-rate records
     */
    public function akkordleistungen(): HasMany
    {
        return $this->hasMany(Akkordleistung::class, 'arbeitskraft_id');
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->vorname} {$this->nachname}";
    }
}
