<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['name', 'username', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    public const USERNAME_FORMAT = 'regex:/\A[a-z0-9._-]+\z/';

    public static function normalizeUsername(string $username): string
    {
        return strtolower(trim($username));
    }

    protected function casts(): array
    {
        return [
            'disabled_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
