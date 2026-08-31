<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zeiterfassung extends Model
{
    protected $table = 'zeiterfassungen';
    
    protected $fillable = [
        'arbeitskraft_id',
        'arbeitstag',
        'eintrag_datetime',
        'austrag_datetime',
        'pausenminuten',
        'arbeitsminuten',
        'arbeitsstunden',
        'qr_code_eingescannt',
        'status',
        'notizen',
    ];
    
    protected $casts = [
        'arbeitstag' => 'date',
        'eintrag_datetime' => 'datetime',
        'austrag_datetime' => 'datetime',
    ];

    /**
     * Time entry belongs to a worker
     */
    public function arbeitskraft(): BelongsTo
    {
        return $this->belongsTo(Arbeitskraft::class, 'arbeitskraft_id');
    }

    /**
     * Calculate work hours (for convenience)
     */
    public function calculateHours(): void
    {
        if ($this->eintrag_datetime && $this->austrag_datetime) {
            $diff = $this->austrag_datetime->diffInMinutes($this->eintrag_datetime);
            $this->arbeitsminuten = $diff - $this->pausenminuten;
            $this->arbeitsstunden = $this->arbeitsminuten / 60;
        }
    }
}
