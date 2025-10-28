<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';

    protected $fillable = [
        'place_id',
        'title',
        'description',
        'discount_type',
        'discount_value',
        'code',
        'max_uses',
        'uses_count',
        'valid_from',
        'valid_until',
        'active',
        'club_exclusive',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'active' => 'boolean',
        'club_exclusive' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class, 'voucher_id');
    }

    public function isValid()
    {
        $today = now()->toDateString();
        return $this->active 
            && $this->valid_from <= $today 
            && $this->valid_until >= $today
            && ($this->max_uses === null || $this->uses_count < $this->max_uses);
    }

    public function canBeUsedBy($user)
    {
        if ($this->club_exclusive && !$user->subscription) {
            return false;
        }
        
        return $this->isValid();
    }
}
