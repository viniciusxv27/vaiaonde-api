<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoulettePlay extends Model
{
    use HasFactory;

    protected $table = 'roulette_plays';

    protected $fillable = [
        'user_id',
        'prize_id',
        'claimed',
        'claimed_at',
    ];

    protected $casts = [
        'claimed' => 'boolean',
        'claimed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prize()
    {
        return $this->belongsTo(RoulettePrize::class, 'prize_id');
    }
}
