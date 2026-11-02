<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'news';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'excerpt',
        'content',
        'image',
        'category',
        'published',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'published' => 'boolean',
    ];

    /**
     * Scope for published news only.
     */
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
}
