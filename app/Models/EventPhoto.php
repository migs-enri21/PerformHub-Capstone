<?php

namespace App\Models;

use App\Services\SupabaseStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPhoto extends Model
{
    protected $fillable = [
        'event_id',
        'file_path',
        'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function fileUrl(): string
    {
        if (str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }

        return (new SupabaseStorageService)->url('organizer-files', $this->file_path);
    }
}
