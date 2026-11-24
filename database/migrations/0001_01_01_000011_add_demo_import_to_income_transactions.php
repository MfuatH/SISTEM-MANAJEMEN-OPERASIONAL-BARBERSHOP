<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('income_transactions', function (Blueprint $table): void {
            $table->foreignId('demo_import_id')->nullable()
                ->constrained('historical_imports')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('income_transactions')->whereNotNull('demo_import_id')->exists()) {
            throw new RuntimeException('Rollback akan menghilangkan asal transaksi demo yang sudah tercatat.');
        }

        Schema::table('income_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('demo_import_id');
        });
    }
};
