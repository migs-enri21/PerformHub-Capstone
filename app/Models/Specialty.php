<?php

namespace App\Models;

use App\Models\Concerns\ManagesAdminListOptions;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use ManagesAdminListOptions;

    protected $fillable = [
        'name',
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
}
