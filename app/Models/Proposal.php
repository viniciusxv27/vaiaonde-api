<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proposal extends Model
{
    use HasFactory;

    protected $table = 'proposals';

    protected $fillable = [
        'influencer_id',
        'place_id',
        'title',
        'description',
        'amount',
        'deadline_days',
        'status',
        'accepted_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deadline_days' => 'integer',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function influencer()
    {
        return $this->belongsTo(User::class, 'influencer_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function chat()
    {
        return $this->hasOne(Chat::class, 'proposal_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'proposal_id');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function canBeAccepted()
    {
        return $this->isPending();
    }

    public function canBeCompleted()
    {
        return $this->isAccepted();
    }
}
