<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_imports', function (Blueprint $table): void {
            $table->id();
            $table->string('dataset_version', 80);
            $table->string('data_kind', 24);
            $table->char('manifest_sha256', 64);
            $table->string('mapping_version', 40);
            $table->dateTime('imported_at');
            $table->foreignId('imported_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->unique(['manifest_sha256', 'mapping_version'], 'historical_import_identity');
        });

        Schema::create('historical_monthly_revenues', function (Blueprint $table): void {
            $table->foreignId('import_id')->constrained('historical_imports')->restrictOnDelete();
            $table->date('month_start');
            $table->decimal('revenue', 30, 0);
            $table->unsignedInteger('transaction_count');
            $table->boolean('is_complete');
            $table->primary(['import_id', 'month_start']);
        });

        Schema::create('forecast_month_sources', function (Blueprint $table): void {
            $table->date('month_start')->primary();
            $table->string('source', 16);
            $table->unsignedBigInteger('import_id')->nullable();
            $table->foreign(['import_id', 'month_start'], 'forecast_source_month_fk')
                ->references(['import_id', 'month_start'])->on('historical_monthly_revenues')->restrictOnDelete();
        });

        DB::statement("ALTER TABLE historical_imports ADD CONSTRAINT historical_import_kind
            CHECK (data_kind IN ('synthetic', 'actual'))");
        DB::statement('ALTER TABLE historical_monthly_revenues ADD CONSTRAINT historical_month_values
            CHECK (DAYOFMONTH(month_start) = 1 AND revenue >= 0 AND is_complete IN (0, 1))');
        DB::statement("ALTER TABLE forecast_month_sources ADD CONSTRAINT forecast_month_source_values
            CHECK (DAYOFMONTH(month_start) = 1 AND (
                (source = 'application' AND import_id IS NULL) OR (source = 'import' AND import_id IS NOT NULL)
            ))");
    }

    public function down(): void
    {
        // Preserve imported and explicitly selected history on a deployed database.
        if (DB::table('historical_imports')->exists() || DB::table('forecast_month_sources')->exists()) {
            throw new RuntimeException('Rollback akan menghapus histori dan sumber Forecasting yang sudah tercatat.');
        }

        Schema::dropIfExists('forecast_month_sources');
        Schema::dropIfExists('historical_monthly_revenues');
        Schema::dropIfExists('historical_imports');
    }
};
