<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Banner extends Model
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $table = 'banner';

    protected $fillable = [
        'image',
    ];

}