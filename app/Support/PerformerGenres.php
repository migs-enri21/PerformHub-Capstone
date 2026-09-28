<?php

namespace App\Support;

use App\Models\Genre;

class PerformerGenres
{
    public static function all(): array
    {
        return Genre::activeNames();
    }
}
