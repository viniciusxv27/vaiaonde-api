<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements MustVerifyEmail, JWTSubject
{
    use HasApiTokens;
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'birthday',
        'phone',
        'password',
        'subscription',
        'stripe_id',
        'ticket_count',
        'promocode',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->id = self::generateUniqueRandomBigInt();
            $user->subscription = 0;
            $user->ticket_count = 0;
            $user->promocode = 1;
        });
    }

    public static function generateUniqueRandomBigInt(): string
    {
        // Máximo permitido para bigint(20)
        $maxBigInt = '9223372036854775807'; // 2^63 - 1

        do {
            // Gerar um número aleatório entre 0 e o máximo permitido para bigint(20)
            $randomBigInt = random_int(0, $maxBigInt);
            $idExists = self::where('id', $randomBigInt)->exists();
        } while ($idExists);

        // Converter o número para string
        return strval($randomBigInt);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}