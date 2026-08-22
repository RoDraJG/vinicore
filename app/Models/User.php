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
     * 🍇 DYNAMISCHER BETRIEBS-GETTER: Holt das aktuell aktive Weingut des Winzers.
     * 🚀 CORE-FIX: Verknüpft das User-Model unzerbrechlich mit deiner 'betriebsdaten'-Tabelle!
     */
    public function getAktuellerBetriebAttribute()
    {
        // Holt über die Pivot-Tabelle den ersten verknüpften Betrieb aus 'betriebsdaten'
        return DB::table('betrieb_user')
            ->where('user_id', $this->id)
            ->join('betriebsdaten', 'betriebsdaten.id', '=', 'betrieb_user.betrieb_id')
            ->select('betriebsdaten.*')
            ->first();
    }
}
