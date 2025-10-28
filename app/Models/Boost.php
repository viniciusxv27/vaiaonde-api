<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boost extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'user_id',
        'amount',
        'days',
        'daily_budget',
        'clicks',
        'impressions',
        'cpc',
        'ctr',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'daily_budget' => 'decimal:2',
        'cpc' => 'decimal:2',
        'ctr' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Calcula CPC (Cost Per Click)
    public function calculateCPC()
    {
        if ($this->clicks > 0) {
            $this->cpc = $this->amount / $this->clicks;
        } else {
            $this->cpc = 0;
        }
        $this->save();
    }

    // Calcula CTR (Click Through Rate)
    public function calculateCTR()
    {
        if ($this->impressions > 0) {
            $this->ctr = ($this->clicks / $this->impressions) * 100;
        } else {
            $this->ctr = 0;
        }
        $this->save();
    }

    // Atualiza métricas
    public function updateMetrics()
    {
        $this->calculateCPC();
        $this->calculateCTR();
    }

    // Incrementa impressões
    public function incrementImpressions()
    {
        $this->increment('impressions');
        $this->calculateCTR();
    }

    // Incrementa cliques
    public function incrementClicks()
    {
        $this->increment('clicks');
        $this->updateMetrics();
    }

    // Verifica se está ativo
    public function isActive()
    {
        return $this->status === 'active' && 
               now()->between($this->start_date, $this->end_date);
    }

    // Budget restante
    public function remainingBudget()
    {
        $daysRemaining = now()->diffInDays($this->end_date, false);
        if ($daysRemaining < 0) return 0;
        return $daysRemaining * $this->daily_budget;
    }
}
