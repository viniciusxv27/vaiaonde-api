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
        'owner_id',
        'name',
        'type',
        'subscription_id',
        'is_active',
        'deactivation_reason',
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
        'image',
        'coords_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    
    public function getFirstCategoryAttribute()
    {
        if (!$this->categories_ids) {
            return null;
        }
        
        $categoryIds = explode(',', $this->categories_ids);
        $firstCategoryId = trim($categoryIds[0]);
        
        return Categorie::find($firstCategoryId);
    }

    public function subscription()
    {
        return $this->belongsTo(PartnerSubscription::class, 'subscription_id');
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'place_id');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'place_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'place_id');
    }

    public function images()
    {
        return $this->hasMany(PlaceImage::class)->orderBy('order');
    }

    public function primaryImage()
    {
        return $this->hasOne(PlaceImage::class)->where('is_primary', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function hasActiveSubscription()
    {
        return $this->subscription && $this->subscription->isActive();
    }

    // Accessor para calcular rating dinamicamente
    public function getRatingAttribute()
    {
        $ratings = Rating::where('place_id', $this->id)->pluck('rate')->toArray();
        return count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : 0;
    }

    // Relação com avaliações
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'place_id');
    }

}