<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserVoucher extends Model
{
    use HasFactory;

    protected $table = 'user_vouchers';

    protected $fillable = [
        'user_id',
        'voucher_id',
        'used',
        'used_at',
        'kirvano_transaction_id',
    ];

    protected $casts = [
        'used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
