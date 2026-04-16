<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory;
    use Notifiable;

    public const ROLE_PLATFORM_ADMIN = 'platform_admin';
    public const ROLE_BUSINESS_ADMIN = 'business_admin';
    public const GLOBAL_ADMIN_EMAIL = 'adrian88gm@gmail.com';
    public const GLOBAL_ADMIN_DOMAIN = 'clockia.net';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isPlatformAdmin(): bool
    {
        return $this->role === self::ROLE_PLATFORM_ADMIN;
    }

    public function isBusinessAdmin(): bool
    {
        return $this->role === self::ROLE_BUSINESS_ADMIN;
    }

    public function hasFullAdminAccess(): bool
    {
        return $this->isPlatformAdmin() || $this->hasGlobalVisibilityByEmail();
    }

    public function negocios(): BelongsToMany
    {
        return $this->belongsToMany(Negocio::class, 'business_user')
            ->withTimestamps();
    }

    public function hasGlobalVisibilityByEmail(): bool
    {
        $email = strtolower(trim((string) $this->email));

        if ($email === '') {
            return false;
        }

        return $email === self::GLOBAL_ADMIN_EMAIL
            || str_ends_with($email, '@'.self::GLOBAL_ADMIN_DOMAIN);
    }
}
