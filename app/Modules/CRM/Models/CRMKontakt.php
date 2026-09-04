<?php

namespace App\Modules\CRM\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CRMKontakt extends Model
{
    use SoftDeletes;

    protected $table = 'crm_kontakte';

        protected $fillable = [
        'kontakt_uuid', 'partner_typ', 'betrieb_id', 'firma', 'ansprechpartner_name', 'nachname', 'vorname', 'geburtsdatum',
        'kundennummer', 'lieferantennummer', 'debitorennummer', 'kreditorennummer', 'buchhaltung_gruppe',
        'ist_kunde', 'ist_lieferant', 'ist_gesperrt', 'kunden_kategorie', 'email', 'telefon', 
        'strasse', 'hausnummer', 'adresszusatz', 'plz', 'ort', 'land',
        'liefer_strasse', 'liefer_hausnummer', 'liefer_adresszusatz', 'liefer_plz', 'liefer_ort', 'liefer_land',
        'ust_id', 'steuernummer', 'ist_steuerbefreit', 'steuerbefreiung_grund',
        'iban', 'bic', 'standard_zahlungsziel_tage', 'individueller_rabatt_prozent', 
        'skonto_prozent', 'skonto_tage', 'lieferbedingungen', 'versanddienstleister', 
        'speditions_hinweis', 'bevorzugte_weinstilistik', 'herkunft_kontakt', 'notizen'
    ];

    /**
     * 🚀 AUTOMATISCHE UUID-GENERIERUNG
     * Erzeugt beim Anlegen vollautomatisch eine sichere, eindeutige UUID!
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->kontakt_uuid)) {
                $model->kontakt_uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * 🛰️ SCOPE: Filtert reine Kunden heraus (für Weinverkauf & Rechnungen)
     */
    public function scopeKunden($query)
    {
        return $query->where('ist_kunde', true);
    }

    /**
     * 🛰️ SCOPE: Filtert reine Lieferanten heraus (für Flaschen- & Hefebezug)
     */
    public function scopeLieferanten($query)
    {
        return $query->where('ist_lieferant', true);
    }
        /**
     * 🎯 MULTI-KANAL: Holt alle verknüpften Ansprechpartner, Abteilungen und spezifischen E-Mails
     */
    public function ansprechpartner()
    {
        return $this->hasMany(\App\Modules\CRM\Models\CRMKontaktDetail::class, 'crm_kontakt_id');
    }

    /**
     * Hilfsmethode, um den Anzeigenamen reaktiv im System zu ermitteln
     */
    public function getAnzeigeNameAttribute(): string
    {
        if ($this->firma) {
            return $this->firma;
        }
        return trim(($this->vorname ?? '') . ' ' . $this->nachname);
    }

}
