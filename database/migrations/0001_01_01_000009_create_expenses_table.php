<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE expenses (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                incurred_on DATE NOT NULL,
                description VARCHAR(255) NOT NULL,
                amount DECIMAL(15, 0) NOT NULL,
                recorded_by_user_id BIGINT UNSIGNED NOT NULL,
                created_at DATETIME(6) NOT NULL,
                request_key CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                request_hash BINARY(32) NOT NULL,
                INDEX expenses_incurred_on_id_index (incurred_on, id),
                UNIQUE KEY expenses_owner_request_key_unique (recorded_by_user_id, request_key),
                CONSTRAINT expenses_recorder_foreign
                    FOREIGN KEY (recorded_by_user_id) REFERENCES users (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT expenses_amount_positive CHECK (amount > 0)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
    }

    public function down(): void
    {
        if (Schema::hasTable('expenses') && DB::table('expenses')->exists()) {
            throw new RuntimeException('Rollback akan menghapus pengeluaran yang sudah tercatat.');
        }

        Schema::dropIfExists('expenses');
    }
};
