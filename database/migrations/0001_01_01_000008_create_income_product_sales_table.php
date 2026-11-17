<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE income_product_sales (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                income_transaction_id BIGINT UNSIGNED NOT NULL,
                product_id BIGINT UNSIGNED NOT NULL,
                product_name VARCHAR(120) NOT NULL,
                quantity INT UNSIGNED NOT NULL,
                unit_price DECIMAL(15, 0) NOT NULL,
                INDEX income_product_sales_transaction_index (income_transaction_id),
                INDEX income_product_sales_product_index (product_id),
                CONSTRAINT income_product_sales_transaction_foreign
                    FOREIGN KEY (income_transaction_id) REFERENCES income_transactions (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT income_product_sales_product_foreign
                    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
                CONSTRAINT income_product_sales_price_positive CHECK (unit_price > 0),
                CONSTRAINT income_product_sales_quantity_positive CHECK (quantity > 0)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL);
    }

    public function down(): void
    {
        if (DB::table('income_product_sales')->exists()) {
            throw new RuntimeException('Rollback akan menghapus penjualan produk yang sudah tercatat.');
        }

        Schema::dropIfExists('income_product_sales');
    }
};
