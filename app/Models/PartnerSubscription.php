<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PartnerSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'last_payment_date',
        'next_payment_date',
        'status',
        'auto_renew'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'last_payment_date' => 'datetime',
        'next_payment_date' => 'datetime',
        'auto_renew' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(PartnerSubscriptionPlan::class, 'plan_id');
    }

    public function places()
    {
        return $this->hasMany(Place::class, 'subscription_id');
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->ends_at > now();
    }

    public function isOverdue()
    {
        return $this->status === 'overdue' || ($this->ends_at < now() && $this->status === 'active');
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'last_payment_date' => now(),
            'next_payment_date' => now()->addMonth()
        ]);

        // Ativa todos os lugares vinculados
        $this->places()->update(['is_active' => true, 'deactivation_reason' => null]);
    }

    public function suspend()
    {
        $this->update(['status' => 'overdue']);

        // Desativa todos os lugares vinculados
        $this->places()->update([
            'is_active' => false,
            'deactivation_reason' => 'Assinatura em atraso. Regularize seu pagamento para reativar.'
        ]);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled', 'auto_renew' => false]);
    }

    public function renew()
    {
        $this->update([
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'last_payment_date' => now(),
            'next_payment_date' => now()->addMonth(),
            'status' => 'active'
        ]);

        $this->places()->update(['is_active' => true, 'deactivation_reason' => null]);
    }
}
