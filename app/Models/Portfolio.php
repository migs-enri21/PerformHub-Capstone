<?php

namespace App\Models;

use App\Services\SupabaseStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Portfolio extends Model
{
    protected $fillable = [
        'performer_profile_id',
        'batch_key',
        'type',
        'file_path',
        'event_name',
        'caption',
        'category_ids',
    ];

    /**
     * @var Collection<int, Category>|null
     */
    protected static ?Collection $categoryLookup = null;

    protected function casts(): array
    {
        return [
            'category_ids' => 'array',
        ];
    }

    public function performerProfile(): BelongsTo
    {
        return $this->belongsTo(PerformerProfile::class);
    }

    /**
     * @return array<int, int>
     */
    public function categoryIdList(): array
    {
        return array_values(array_map(
            'intval',
            array_filter($this->category_ids ?? [], fn ($id) => is_numeric($id))
        ));
    }

    /**
     * @return array<int, string>
     */
    public function performanceTypeNames(): array
    {
        if (self::$categoryLookup === null) {
            self::$categoryLookup = Category::query()->orderBy('name')->get()->keyBy('id');
        }

        return collect($this->categoryIdList())
            ->map(fn ($id) => self::$categoryLookup->get($id)?->name)
            ->filter()
            ->values()
            ->all();
    }

    public function fileUrl(): string
    {
        return (new SupabaseStorageService)->url('performer-files', $this->file_path);
    }
}
