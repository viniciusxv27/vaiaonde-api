<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    use HasFactory;

    protected $table = 'videos';

    protected $fillable = [
        'user_id',
        'place_id',
        'title',
        'description',
        'video_url',
        'thumbnail_url',
        'duration',
        'views_count',
        'likes_count',
        'shares_count',
        'is_sponsored',
        'active',
    ];

    protected $casts = [
        'is_sponsored' => 'boolean',
        'active' => 'boolean',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'shares_count' => 'integer',
        'duration' => 'integer',
    ];

    protected $appends = ['share_url'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function interactions()
    {
        return $this->hasMany(VideoInteraction::class, 'video_id');
    }

    public function likes()
    {
        return $this->hasMany(VideoInteraction::class, 'video_id')->where('type', 'like');
    }

    public function views()
    {
        return $this->hasMany(VideoInteraction::class, 'video_id')->where('type', 'view');
    }

    public function isLikedBy($userId)
    {
        return $this->interactions()
            ->where('user_id', $userId)
            ->where('type', 'like')
            ->exists();
    }

    public function getShareUrlAttribute()
    {
        return env('APP_URL') . '/videos/' . $this->id;
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementLikes()
    {
        $this->increment('likes_count');
    }

    public function decrementLikes()
    {
        $this->decrement('likes_count');
    }

    public function incrementShares()
    {
        $this->increment('shares_count');
    }
}
