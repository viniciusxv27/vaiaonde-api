<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceHighlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'place_id',
        'user_id',
        'amount',
        'start_date',
        'end_date',
        'is_active',
        'payment_method',
        'stripe_charge_id',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
    ];

    /**
     * Relacionamento com Place
     */
    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Relacionamento com User (proprietário)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se está ativo
     */
    public function isActive()
    {
        return $this->is_active 
            && $this->status === 'active'
            && now()->between($this->start_date, $this->end_date);
    }

    /**
     * Ativar destaque
     */
    public function activate()
    {
        $this->update([
            'status' => 'active',
            'is_active' => true,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ]);
    }

    /**
     * Desativar destaque
     */
    public function deactivate()
    {
        $this->update([
            'status' => 'expired',
            'is_active' => false,
        ]);
    }

    /**
     * Scope para destaques ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }
}
