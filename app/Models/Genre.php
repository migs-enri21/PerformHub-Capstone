<?php

namespace App\Models;

use App\Models\Concerns\ManagesAdminListOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Genre extends Model
{
    use ManagesAdminListOptions;

    protected $fillable = [
        'name',
        'category_id',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
