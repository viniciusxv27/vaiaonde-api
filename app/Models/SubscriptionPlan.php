<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'original_price',
        'period',
        'period_count',
        'stripe_price_id',
        'features',
        'roulette_spins_per_month',
        'priority_support',
        'active',
        'is_popular',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'features' => 'array',
        'active' => 'boolean',
        'is_popular' => 'boolean',
        'roulette_spins_per_month' => 'integer',
        'priority_support' => 'integer',
        'period_count' => 'integer',
        'order' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'subscription_plan_id');
    }

    public function getSavingsPercentage()
    {
        if (!$this->original_price || $this->original_price <= $this->price) {
            return 0;
        }

        return round((($this->original_price - $this->price) / $this->original_price) * 100);
    }

    public function getMonthlyEquivalent()
    {
        if ($this->period === 'month') {
            return $this->price;
        }

        $months = [
            'year' => 12,
            'quarter' => 3,
            'semester' => 6,
        ];

        return $this->price / ($months[$this->period] ?? 1);
    }
}
