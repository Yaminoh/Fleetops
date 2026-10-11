<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string|null $two_factor_code
 * @property \Illuminate\Support\Carbon|null $two_factor_expires_at
 */
#[Fillable([
    'name',
    'email',
    'password',
    'password_hash',
    'role',
    'status',
    'preferences'
])]
#[Hidden([
    'password',
    'password_hash',
    'remember_token'
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
            'password_hash' => 'hashed',
            'two_factor_expires_at' => 'datetime',
            'preferences' => 'array',
        ];
    }

    public static function passwordColumnName(): string
    {
        return Schema::hasColumn('users', 'password_hash')
            ? 'password_hash'
            : 'password';
    }

    public function setPassword(string $password): void
    {
        $this->setAttribute(
            static::passwordColumnName(),
            Hash::make($password)
        );
    }

    public function getAuthPasswordName(): string
    {
        return static::passwordColumnName();
    }

    public function getAuthPassword(): string
    {
        return (string) $this->getAttribute(
            $this->getAuthPasswordName()
        );
    }

    public function getPreferencesWithDefaultsAttribute(): array
    {
        return array_merge([
            'theme' => 'light',
            'date_format' => 'M d, Y',
            'locale' => 'en',
        ], $this->preferences ?? []);
    }
}