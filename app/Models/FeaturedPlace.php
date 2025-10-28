<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedPlace extends Model
{
    use HasFactory;

    protected $table = 'featured_places';

    protected $fillable = [
        'place_id',
        'starts_at',
        'ends_at',
        'is_active'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('ends_at', '>', now());
    }
}
