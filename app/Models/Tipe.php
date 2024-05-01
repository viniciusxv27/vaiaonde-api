<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Tipe extends Model
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $table = 'tipe';

    protected $fillable = [
        'name',
    ];

}