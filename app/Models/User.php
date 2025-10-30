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
        'username',
        'email',
        'birthday',
        'phone',
        'cpf',
        'password',
        'subscription',
        'payment_id',
        'score',
        'economy',
        'is_admin',
        'stripe_id',
        'promocode',
        'ticket_count',
        'role',
        'wallet_balance',
        'pix_key',
        'abacatepay_customer_id',
        'bio',
        'avatar',
        'instagram_url',
        'youtube_url',
        'tiktok_url',
        'twitter_url',
        'balance',
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
            $user->birthday = 0;
            $user->promocode = 1;
            $user->economy = 0;
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

    public function vouchers()
    {
        return $this->hasMany(UserVoucher::class, 'user_id');
    }

    public function usedVouchers()
    {
        return $this->hasMany(UserVoucher::class, 'user_id')->where('used', true);
    }

    public function availableVouchers()
    {
        return $this->hasMany(UserVoucher::class, 'user_id')->where('used', false);
    }

    public function videos()
    {
        return $this->hasMany(Video::class, 'user_id');
    }

    public function ownedPlaces()
    {
        return $this->hasMany(Place::class, 'owner_id');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'influencer_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function partnerSubscription()
    {
        return $this->hasOne(PartnerSubscription::class)->where('status', 'active')->latest();
    }

    public function chatsAsInfluencer()
    {
        return $this->hasMany(Chat::class, 'influencer_id');
    }

    public function isComum()
    {
        return $this->role === 'comum';
    }

    public function isAssinante()
    {
        return $this->role === 'assinante' || $this->subscription;
    }

    public function isProprietario()
    {
        return $this->role === 'proprietario';
    }

    public function isInfluenciador()
    {
        return $this->role === 'influenciador';
    }

    public function hasWalletAccess()
    {
        return $this->isProprietario() || $this->isInfluenciador();
    }

    public function roulettePlays()
    {
        return $this->hasMany(RoulettePlay::class, 'user_id');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function canSpinRoulette()
    {
        return $this->roulette_spins_available > 0;
    }

    public function canGetDailySpin()
    {
        if (!$this->last_daily_spin) {
            return true;
        }

        $lastSpin = \Carbon\Carbon::parse($this->last_daily_spin);
        return $lastSpin->diffInDays(now()) >= 1;
    }

}