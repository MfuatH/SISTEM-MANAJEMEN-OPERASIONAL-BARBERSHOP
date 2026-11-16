<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE users
                ADD COLUMN username VARCHAR(80) CHARACTER SET ascii COLLATE ascii_general_ci NULL,
                ADD COLUMN disabled_at DATETIME(6) NULL,
                ADD UNIQUE INDEX users_username_unique (username),
                DROP COLUMN email,
                DROP COLUMN email_verified_at
            SQL);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
        });

        // Provide unique values for the original schema, including accounts created after the upgrade.
        DB::statement("UPDATE users SET email = CONCAT('owner-', id, '@example.invalid')");

        DB::statement(<<<'SQL'
            ALTER TABLE users
                MODIFY COLUMN email VARCHAR(255) NOT NULL,
                ADD UNIQUE INDEX users_email_unique (email),
                DROP COLUMN username,
                DROP COLUMN disabled_at
            SQL);
    }
};
