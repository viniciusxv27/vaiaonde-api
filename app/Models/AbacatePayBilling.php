<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbacatePayBilling extends Model
{
    protected $table = 'abacatepay_billings';
    
    protected $fillable = [
        'user_id',
        'billing_id',
        'type',
        'amount',
        'status',
        'pix_qr_code',
        'pix_qr_code_url',
        'description',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
