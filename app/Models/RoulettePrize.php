<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoulettePrize extends Model
{
    use HasFactory;

    protected $table = 'roulette_prizes';

    protected $fillable = [
        'name',
        'description',
        'type',
        'prize_value',
        'voucher_id',
        'points_value',
        'discount_value',
        'image_url',
        'color',
        'probability',
        'quantity',
        'quantity_used',
        'active',
        'club_exclusive',
    ];

    protected $casts = [
        'active' => 'boolean',
        'club_exclusive' => 'boolean',
        'discount_value' => 'decimal:2',
        'probability' => 'integer',
        'quantity' => 'integer',
        'quantity_used' => 'integer',
        'points_value' => 'integer',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function plays()
    {
        return $this->hasMany(RoulettePlay::class, 'prize_id');
    }

    public function isAvailable()
    {
        return $this->active 
            && ($this->quantity === null || $this->quantity_used < $this->quantity);
    }

    public function canBeWonBy($user)
    {
        if ($this->club_exclusive && !$user->subscription) {
            return false;
        }
        
        return $this->isAvailable();
    }
}
