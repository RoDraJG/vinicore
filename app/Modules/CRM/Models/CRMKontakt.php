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
        'kontakt_uuid', 'partner_typ', 'anrede', 'betrieb_id', 'firma', 
        'rechtsform', // 🎯 B2B Neu
        'ansprechpartner_name', 'nachname', 'vorname', 'geburtsdatum',
        'strasse', 'hausnummer', 'adresszusatz', 'plz', 'ort',  
        // 🎯 Abweichende Rechnungsdaten für Zentralregulierungen (Filial-System)
        'weicht_rechnungsanschrift_ab', 'rechnung_firma', 'rechnung_strasse', 
        'rechnung_hausnummer', 'rechnung_adresszusatz', 'rechnung_plz', 'rechnung_ort',
        
        'liefer_strasse', 'liefer_hausnummer', 'liefer_adresszusatz', 'liefer_plz', 'liefer_ort',
        'kunden_kategorie', 'buchhaltung_gruppe', 'debitorennummer', 'kreditorennummer',
        'standard_zahlungsziel_tage', 'individueller_rabatt_prozent', 'skonto_prozent', 'skonto_tage',
        'lieferbedingungen', 'versanddienstleister', 'speditions_hinweis', 
        'ust_id', 'steuernummer', 'leitweg_id', // 🎯 B2B Neu
        'iban', 'bic', 'bevorzugte_weinstilistik', 'herkunft_kontakt', 'email', 'telefon', 'notizen'
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
