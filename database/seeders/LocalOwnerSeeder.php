<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class LocalOwnerSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new RuntimeException('LocalOwnerSeeder hanya dapat dijalankan pada lingkungan local atau testing.');
        }

        $password = config('auth.local_owner.password');
        if (! is_string($password) || mb_strlen($password) < 12 || strlen($password) > 72) {
            throw new RuntimeException('Isi LOCAL_OWNER_PASSWORD pada .env lokal dengan minimal 12 karakter dan maksimal 72 byte.');
        }

        $username = config('auth.local_owner.username');
        $name = config('auth.local_owner.name');
        $account = Validator::make([
            'username' => is_string($username) ? User::normalizeUsername($username) : $username,
            'name' => is_string($name) ? trim($name) : $name,
        ], [
            'username' => ['required', 'string', 'max:80', User::USERNAME_FORMAT],
            'name' => ['required', 'string', 'max:255'],
        ])->validate();

        $owner = User::query()->firstOrCreate(
            ['username' => $account['username']],
            ['name' => $account['name'], 'password' => $password],
        );

        if ($owner->wasRecentlyCreated) {
            $this->command?->info('Akun Owner lokal dibuat untuk username '.$owner->username.'.');
        } else {
            $this->command?->info('Username '.$owner->username.' sudah terdaftar. Gunakan kredensial akun yang ada.');
        }
    }
}
