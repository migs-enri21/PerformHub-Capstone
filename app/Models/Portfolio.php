<?php

namespace App\Models;

use App\Services\SupabaseStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Portfolio extends Model
{
    protected $fillable = [
        'performer_profile_id',
        'batch_key',
        'type',
        'file_path',
        'caption',
    ];

    public function performerProfile(): BelongsTo
    {
        return $this->belongsTo(PerformerProfile::class);
    }

    public function fileUrl(): string
    {
        return (new SupabaseStorageService)->url('performer-files', $this->file_path);
    }
}
