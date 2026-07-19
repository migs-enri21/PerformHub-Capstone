<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use  App\Models\Category;

class Event extends Model
{
    protected $fillable = [
        'organizer_id',
        'event_type_id',
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'venue',
        'budget',
        'performers_needed',
        'status',
        'cover_photo',
        'preferred_category_id',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function eventType()
    {
         return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function preferredCategory()
    {
        return $this->belongsTo(Category::class, 'preferred_category_id');
    }
}