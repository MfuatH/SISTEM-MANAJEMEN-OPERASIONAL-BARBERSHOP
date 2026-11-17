<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE income_entries (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                barber_name VARCHAR(120) NOT NULL,
                customer_name VARCHAR(120) NULL,
                service_id BIGINT UNSIGNED NOT NULL,
                service_name VARCHAR(120) NOT NULL,
                amount DECIMAL(15, 0) NOT NULL,
                recorded_at DATETIME(6) NOT NULL,
                session_scope BINARY(32) NOT NULL,
                request_key CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                request_hash BINARY(32) NOT NULL,
                INDEX income_entries_recorded_at_id_index (recorded_at, id),
                INDEX income_entries_session_scope_recorded_at_id_index (session_scope, recorded_at, id),
                INDEX income_entries_service_id_index (service_id),
                UNIQUE KEY income_entries_request_key_unique (request_key),
                CONSTRAINT income_entries_service_id_foreign
                    FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT income_entries_amount_positive CHECK (amount > 0)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('income_entries');
    }
};
