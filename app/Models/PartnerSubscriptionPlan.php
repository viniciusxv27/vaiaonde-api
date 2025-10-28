<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerSubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
        'features',
        'can_launch_promotions',
        'appears_in_top',
        'professional_videos_per_month',
        'has_analytics',
        'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'can_launch_promotions' => 'boolean',
        'appears_in_top' => 'boolean',
        'has_analytics' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function subscriptions()
    {
        return $this->hasMany(PartnerSubscription::class, 'plan_id');
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(PartnerSubscription::class, 'plan_id')->where('status', 'active');
    }
}
