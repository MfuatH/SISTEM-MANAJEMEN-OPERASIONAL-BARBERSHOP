<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE products (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                sale_price DECIMAL(15, 0) NULL,
                archived_at DATETIME(6) NULL,
                created_at DATETIME(6) NOT NULL,
                updated_at DATETIME(6) NOT NULL,
                INDEX products_archived_at_name_index (archived_at, name),
                CONSTRAINT products_sale_price_positive CHECK (sale_price > 0)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
    }

    public function down(): void
    {
        if (DB::table('products')->exists()) {
            throw new RuntimeException('Rollback katalog akan menghapus produk yang sudah ada.');
        }

        Schema::dropIfExists('products');
    }
};
