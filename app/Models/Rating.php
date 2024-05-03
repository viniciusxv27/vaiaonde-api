<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Rating extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'rating';

    protected $fillable = [
        'place_id',
        'user_id',
        'rate',
    ];

}