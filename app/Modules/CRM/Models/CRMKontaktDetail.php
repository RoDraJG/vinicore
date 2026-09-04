<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class CRMKontaktDetail extends Model
{
    protected $table = 'crm_kontakte_details';
    protected $fillable = ['crm_kontakt_id', 'abteilung', 'ansprechpartner_name', 'email', 'telefon', 'ist_hauptkontakt', 'notiz'];

    public function hauptPartner()
    {
        return $this->belongsTo(CRMKontakt::class, 'crm_kontakt_id');
    }
}
