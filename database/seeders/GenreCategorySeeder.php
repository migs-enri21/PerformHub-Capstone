<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreCategorySeeder extends Seeder
{
    /**
     * Default genres grouped by category slug so performers see only matching options.
     * Admin can edit, add, or reassign these later.
     *
     * @var array<string, array<int, string>>
     */
    private array $genresByCategory = [
        'singer' => [
            'Pop', 'Rock', 'Acoustic', 'Acoustic Pop', 'OPM', 'R&B', 'Soul',
            'Jazz', 'Blues', 'Classical', 'Country', 'Folk', 'Hip-Hop', 'Rap',
            'K-Pop', 'Musical Theater', 'Gospel', 'Ballad', 'Live Band',
            'Cover Band', 'Tribute Act',
        ],
        'dancers' => [
            'Contemporary Dance', 'Hip-Hop Dance', 'Ballet', 'Modern Dance',
            'Cultural / Traditional', 'Street Dance',
        ],
        'dj' => [
            'EDM', 'House', 'Techno', 'Open Format DJ', 'Club DJ', 'Wedding DJ',
        ],
        'host' => [
            'MC / Hosting',
        ],
        'magician' => [
            'Close-up Magic', 'Stage Magic', 'Illusion',
        ],
    ];

    public function run(): void
    {
        foreach ($this->genresByCategory as $slug => $names) {
            $category = Category::query()->where('slug', $slug)->first();

            if (! $category) {
                continue;
            }

            foreach ($names as $name) {
                $genre = Genre::query()->firstOrNew(['name' => $name]);

                if (! $genre->exists) {
                    $genre->slug = Genre::makeSlug($name);
                    $genre->is_active = true;
                }

                if ($genre->category_id === null) {
                    $genre->category_id = $category->id;
                }

                $genre->save();
            }
        }
    }
}
