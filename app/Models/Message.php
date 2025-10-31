<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';
    
    public $timestamps = false; // A tabela só tem created_at

    protected $fillable = [
        'chat_id',
        'sender_id',
        'message',
        'type',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    // Alias para manter compatibilidade
    public function user()
    {
        return $this->sender();
    }
}
