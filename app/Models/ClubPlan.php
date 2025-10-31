<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClubPlan extends Model
{
    use HasFactory;

    protected $table = 'club_plans';

    protected $fillable = [
        'title',
        'description',
        'benefits',
        'price',
        'duration_days',
        'is_active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(ClubSubscription::class, 'plan_id');
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(ClubSubscription::class, 'plan_id')
                    ->where('status', 'active')
                    ->where('expires_at', '>', now());
    }
}
