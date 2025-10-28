<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoInteraction extends Model
{
    use HasFactory;

    protected $table = 'video_interactions';

    public $timestamps = false;

    protected $fillable = [
        'video_id',
        'user_id',
        'type',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
