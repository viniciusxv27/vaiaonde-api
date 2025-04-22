<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Place extends Model
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $table = 'place';

    protected $fillable = [
        'name',
        'card_image',
        'review',
        'categories_ids',
        'city_id',
        'logo',
        'instagram_url',
        'phone',
        'location_url',
        'location',
        'uber_url',
        'hidden',
        'tipe_id',
        'top',
    ];

}