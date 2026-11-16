<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateOwner extends Command
{
    protected $signature = 'owner:create {username : Username Owner} {--name=Owner : Nama tampilan Owner}';

    protected $description = 'Membuat akun Owner melalui terminal';

    public function handle(): int
    {
        $account = Validator::make([
            'username' => User::normalizeUsername($this->argument('username')),
            'name' => trim($this->option('name')),
        ], [
            'username' => ['required', 'string', 'max:80', User::USERNAME_FORMAT, Rule::unique('users', 'username')],
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($account->fails()) {
            $this->error($account->errors()->first());

            return self::FAILURE;
        }

        $password = $this->secret('Kata sandi Owner');
        if (! is_string($password) || mb_strlen($password) < 12 || strlen($password) > 72) {
            $this->error('Gunakan minimal 12 karakter dengan panjang maksimal 72 byte.');

            return self::FAILURE;
        }

        if ($this->secret('Ulangi kata sandi Owner') !== $password) {
            $this->error('Konfirmasi kata sandi berbeda.');

            return self::FAILURE;
        }

        try {
            User::create($account->validated() + ['password' => $password]);
        } catch (UniqueConstraintViolationException) {
            $this->error('Username sudah digunakan.');

            return self::FAILURE;
        }

        $this->info('Akun Owner berhasil dibuat.');

        return self::SUCCESS;
    }
}
