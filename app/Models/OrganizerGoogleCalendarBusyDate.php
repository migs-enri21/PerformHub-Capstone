<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizerGoogleCalendarBusyDate extends Model
{
    protected $fillable = ['organizer_profile_id','date','summary',];

    protected function casts(): array
    {
        return ['date' => 'date',];
    }

    public function organizerProfile(): BelongsTo
    {
        return $this->belongsTo(OrganizerProfile::class);
    }
}
