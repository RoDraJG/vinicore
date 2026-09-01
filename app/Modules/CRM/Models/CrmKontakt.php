<?php

namespace App\Modules\CRM\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CrmKontakt extends Model
{
    use SoftDeletes;

    protected $table = 'crm_kontakte';

    protected $fillable = [
        'kontakt_uuid', 'betrieb_id', 'firma', 'nachname', 'vorname', 'geburtsdatum',
        'kundennummer', 'lieferantennummer', 'debitorennummer', 'kreditorennummer',
        'ist_kunde', 'ist_lieferant', 'kunden_kategorie', 'email', 'telefon', 
        'strasse_nr', 'adresszusatz', 'plz', 'ort', 'land',
        'liefer_strasse_nr', 'liefer_adresszusatz', 'liefer_plz', 'liefer_ort', 'liefer_land',
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
}
