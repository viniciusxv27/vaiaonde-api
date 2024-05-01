<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class City extends Model
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $table = 'city';

    protected $fillable = [
        'name',
    ];

}