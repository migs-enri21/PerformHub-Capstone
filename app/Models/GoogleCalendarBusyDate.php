<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleCalendarBusyDate extends Model
{
    protected $fillable = [
        'performer_profile_id',
        'date',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function performerProfile(): BelongsTo
    {
        return $this->belongsTo(PerformerProfile::class);
    }
}
