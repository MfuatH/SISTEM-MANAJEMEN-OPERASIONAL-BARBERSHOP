<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS income_transactions (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                barber_name VARCHAR(120) NOT NULL,
                customer_name VARCHAR(120) NULL,
                recorded_at DATETIME(6) NOT NULL,
                session_scope BINARY(32) NOT NULL,
                request_key CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                request_hash BINARY(32) NOT NULL,
                INDEX income_transactions_recorded_at_id_index (recorded_at, id),
                INDEX income_transactions_session_scope_recorded_at_id_index (session_scope, recorded_at, id),
                UNIQUE KEY income_transactions_request_key_unique (request_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);

        if (! Schema::hasColumn('income_entries', 'request_key')) {
            return;
        }

        if (! Schema::hasColumn('income_entries', 'income_transaction_id')) {
            DB::statement('ALTER TABLE income_entries ADD income_transaction_id BIGINT UNSIGNED NULL');
        }

        DB::transaction(function (): void {
            DB::statement(<<<'SQL'
                INSERT INTO income_transactions (barber_name, customer_name, recorded_at, session_scope, request_key, request_hash)
                SELECT e.barber_name, e.customer_name, e.recorded_at, e.session_scope, e.request_key, e.request_hash
                FROM income_entries AS e
                LEFT JOIN income_transactions AS t ON t.request_key = e.request_key
                WHERE t.id IS NULL
                SQL);
            DB::statement(<<<'SQL'
                UPDATE income_entries AS e
                JOIN income_transactions AS t ON t.request_key = e.request_key
                SET e.income_transaction_id = t.id
                SQL);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE income_entries
                MODIFY income_transaction_id BIGINT UNSIGNED NOT NULL,
                ADD used_product_id BIGINT UNSIGNED NULL,
                ADD used_product_name VARCHAR(120) NULL,
                ADD INDEX income_entries_transaction_index (income_transaction_id),
                ADD INDEX income_entries_used_product_index (used_product_id),
                ADD CONSTRAINT income_entries_transaction_foreign
                    FOREIGN KEY (income_transaction_id) REFERENCES income_transactions (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                ADD CONSTRAINT income_entries_used_product_foreign
                    FOREIGN KEY (used_product_id) REFERENCES products (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                DROP INDEX income_entries_recorded_at_id_index,
                DROP INDEX income_entries_session_scope_recorded_at_id_index,
                DROP INDEX income_entries_request_key_unique,
                DROP COLUMN barber_name,
                DROP COLUMN customer_name,
                DROP COLUMN recorded_at,
                DROP COLUMN session_scope,
                DROP COLUMN request_key,
                DROP COLUMN request_hash
            SQL);
    }

    public function down(): void
    {
        if (Schema::hasTable('income_product_sales')) {
            throw new RuntimeException('Rollback migrasi penjualan produk sebelum membalikkan migrasi transaksi.');
        }

        if (! Schema::hasColumn('income_entries', 'income_transaction_id')) {
            Schema::dropIfExists('income_transactions');

            return;
        }

        $hasUnsupportedTransactions = DB::table('income_transactions AS t')
            ->leftJoin('income_entries AS e', 'e.income_transaction_id', '=', 't.id')
            ->select('t.id')
            ->groupBy('t.id')
            ->havingRaw('COUNT(e.id) <> 1 OR COUNT(e.used_product_id) > 0')
            ->exists();

        if ($hasUnsupportedTransactions) {
            throw new RuntimeException('Skema lama tidak dapat menyimpan transaksi multi-layanan, pemakaian produk, atau penjualan produk.');
        }

        if (! Schema::hasColumn('income_entries', 'request_key')) {
            DB::statement(<<<'SQL'
                ALTER TABLE income_entries
                    ADD barber_name VARCHAR(120) NULL,
                    ADD customer_name VARCHAR(120) NULL,
                    ADD recorded_at DATETIME(6) NULL,
                    ADD session_scope BINARY(32) NULL,
                    ADD request_key CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NULL,
                    ADD request_hash BINARY(32) NULL
                SQL);
        }

        DB::statement(<<<'SQL'
            UPDATE income_entries AS e
            JOIN income_transactions AS t ON t.id = e.income_transaction_id
            SET e.barber_name = t.barber_name, e.customer_name = t.customer_name,
                e.recorded_at = t.recorded_at, e.session_scope = t.session_scope,
                e.request_key = t.request_key, e.request_hash = t.request_hash
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE income_entries
                DROP FOREIGN KEY income_entries_transaction_foreign,
                DROP FOREIGN KEY income_entries_used_product_foreign,
                DROP COLUMN income_transaction_id,
                DROP COLUMN used_product_id,
                DROP COLUMN used_product_name,
                MODIFY barber_name VARCHAR(120) NOT NULL,
                MODIFY recorded_at DATETIME(6) NOT NULL,
                MODIFY session_scope BINARY(32) NOT NULL,
                MODIFY request_key CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                MODIFY request_hash BINARY(32) NOT NULL,
                ADD INDEX income_entries_recorded_at_id_index (recorded_at, id),
                ADD INDEX income_entries_session_scope_recorded_at_id_index (session_scope, recorded_at, id),
                ADD UNIQUE KEY income_entries_request_key_unique (request_key)
            SQL);

        Schema::dropIfExists('income_transactions');
    }
};
