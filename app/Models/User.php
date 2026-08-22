<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * Das zentrale Benutzer-Model des vinicore ERP.
 * 🚀 ARCHITEKTUR: Erweitert um getrennte Namensstrukturen und reaktive Betriebs-Beziehungen.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Die Attribute, die für das Mass-Assignment freigegeben sind.
     * 🛡️ SICHERUNG: Vorname und Nachname sind jetzt voll einsatzbereit!
     */
    protected $fillable = [
        'username',
        'vorname',
        'nachname',
        'email',
        'password',
        'rolle',
    ];

    /**
     * Die Attribute, die bei der Serialisierung (z. B. JSON-Ausgaben) versteckt werden.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Die Attribute, die automatisch gecastet werden sollen.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * 🔮 DYNAMISCHER GETTER: Kombiniert Vor- und Nachname reaktiv für dein App-Layout!
     * Dadurch bleibt {{ auth()->user()->name }} in der app.blade.php vollkompatibel,
     * ohne dass dort Code angepasst werden muss.
     */
    public function getNameAttribute()
    {
        return trim($this->vorname . ' ' . $this->nachname);
    }

    /**
     * 🏢 ERFP-BETRIEBSANBINDUNG: Holt die globalen Einstellungen des Weinbaubetriebs.
     * 🚀 REVISIONS-FIX: Wirft die gelöschten Pivot-Tabellen raus und greift direkt auf 'betriebseinstellungen' zu!
     */
    public function getAktuellerBetriebAttribute()
    {
        // Greift ohne Umwege direkt über die betrieb_id des Users auf die neuen Einstellungen zu
        return DB::table('betriebseinstellungen')
            ->where('betrieb_id', $this->betrieb_id)
            ->first();
    }

}
