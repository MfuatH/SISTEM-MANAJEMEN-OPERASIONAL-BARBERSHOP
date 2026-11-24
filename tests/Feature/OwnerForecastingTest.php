<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Forecasting\MonthlyForecast;
use App\Services\Forecasting\MonthlyRevenueHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Process\Exceptions\ProcessTimedOutException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessTimedOutException as SymfonyTimeout;
use Symfony\Component\Process\Process as SymfonyProcess;
use Tests\TestCase;

class OwnerForecastingTest extends TestCase
{
    use DatabaseTransactions;

    private string $snapshotPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->snapshotPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'barbershop-forecast-'.Str::uuid().'.json';
        config(['forecasting.enabled' => true, 'forecasting.snapshot_path' => $this->snapshotPath]);
        $this->travelTo(Carbon::parse('2026-11-24 08:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        File::delete($this->snapshotPath);

        parent::tearDown();
    }

    public function test_routes_require_an_active_owner_even_when_the_feature_is_disabled(): void
    {
        config(['forecasting.enabled' => false]);
        $this->get(route('owner.forecasting.index'))->assertRedirect(route('login'));
        $this->getJson(route('owner.forecasting.data'))->assertUnauthorized();
        $this->actingAs(User::factory()->create(['disabled_at' => now()]));
        $this->getJson(route('owner.forecasting.data'))->assertUnauthorized();

        $this->actingAs(User::factory()->create());
        $this->getJson(route('owner.forecasting.data'))
            ->assertOk()->assertJsonPath('status', 'disabled')->assertJsonPath('predictions', [])
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->get(route('owner.forecasting.index'))->assertOk()->assertSee('Forecasting belum diaktifkan.');
    }

    public function test_refresh_uses_three_months_with_a_separate_holdout_and_is_idempotent(): void
    {
        $this->history();
        $this->successfulProcess();
        $this->artisan('forecast:refresh')->expectsOutput('Forecasting: ready')->assertSuccessful();
        $snapshot = File::get($this->snapshotPath);

        Process::assertRan(function ($process, $result): bool {
            $input = json_decode($process->input, true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame([config('forecasting.python'), base_path('forecasting/linear_regression.py')], $process->command);
            $this->assertSame('2026-11', $input['target_month']);
            $this->assertSame(['2026-08', '2026-09', '2026-10'], array_column($input['actual'], 'month'));
            $this->assertSame('2026-10', $input['holdout']['target_month']);
            $this->assertSame(['2026-07', '2026-08', '2026-09'], array_column($input['holdout']['actual'], 'month'));

            return true;
        });
        $this->artisan('forecast:refresh')->expectsOutput('Forecasting: up_to_date')->assertSuccessful();
        Process::assertRanTimes(fn (): bool => true, 1);
        $this->assertSame($snapshot, File::get($this->snapshotPath));

        $this->actingAs(User::factory()->create());
        $this->getJson(route('owner.forecasting.data'))->assertOk()
            ->assertJsonPath('schema_version', 1)->assertJsonPath('status', 'ready')->assertJsonCount(3, 'actual')
            ->assertJsonPath('predictions.0.month', '2026-11')->assertJsonPath('predictions.0.amount', '8573667')
            ->assertJsonPath('evaluation.mae_idr', '290000')->assertJsonPath('evaluation.baseline_mae_idr', '219000')
            ->assertJsonPath('evaluation.test_months', 1)->assertJsonPath('evaluation.field_accuracy_verified', false);
        $page = $this->get(route('owner.forecasting.index'))->assertOk()
            ->assertSee('Rp8.573.667')->assertSee('Data sintetis')->assertSee('Rp290.000')->assertSee('Rp219.000')
            ->assertSee('Histori dan prediksi pemasukan')->assertSee('name="forecast-chart-type" value="bar" checked', false)
            ->assertSee('name="forecast-chart-type" value="line"', false)->assertSee('data-projection', false)
            ->assertSee('js/owner-dashboard.js')
            ->assertSeeInOrder(['data-month="2026-08"', 'data-month="2026-09"', 'data-month="2026-10"', 'data-month="2026-11"'], false);
        $url = preg_quote(route('owner.forecasting.index'), '/');
        $this->assertMatchesRegularExpression('/href="'.$url.'"\s+aria-current="page"/', $page->getContent());
    }

    public function test_status_distinguishes_missing_history_processing_and_stale_results(): void
    {
        $this->history();
        Cache::put('forecasting:refreshing', true, 60);
        $this->assertSame('processing', app(MonthlyForecast::class)->status()['status']);
        Cache::forget('forecasting:refreshing');
        $this->successfulProcess();
        $this->artisan('forecast:refresh')->assertSuccessful();

        $this->travelTo(Carbon::parse('2026-12-01 00:00:00', 'Asia/Jakarta'));
        // Replace the original binding so the later request sees the next calendar month.
        $this->mock(MonthlyRevenueHistory::class, fn ($mock) => $mock->shouldReceive('window')->withNoArgs()->andReturn([
            'status' => 'insufficient_data', 'target_month' => '2026-12',
            'data_kind' => 'synthetic', 'actual' => [], 'missing_months' => ['2026-11'],
        ]));

        $status = app(MonthlyForecast::class)->status();
        $this->assertSame('stale', $status['status']);
        $this->assertSame('2026-11', $status['predictions'][0]['month']);
        File::delete($this->snapshotPath);
        $this->assertSame('insufficient_data', app(MonthlyForecast::class)->status()['status']);
    }

    public function test_concurrent_refresh_does_not_invoke_the_model_twice(): void
    {
        Process::fake();
        $lock = Cache::lock('forecasting:refresh', 180);
        $this->assertTrue($lock->get());
        try {
            $this->artisan('forecast:refresh')->expectsOutput('Forecasting: processing')->assertSuccessful();
            Process::assertNothingRan();
        } finally {
            $lock->release();
        }
    }

    public function test_model_errors_and_negative_predictions_expose_no_prediction(): void
    {
        $this->history();
        Process::fake(fn () => Process::result(output: 'bad JSON'));
        $this->artisan('forecast:refresh')->expectsOutput('Forecasting: unavailable')->assertFailed();
        $response = app(MonthlyForecast::class)->status();
        $this->assertSame('generation_failed', $response['reason_code']);
        $this->assertSame([], $response['predictions']);

        $this->successfulProcess('-100', true);
        $this->artisan('forecast:refresh')->expectsOutput('Forecasting: unavailable')->assertFailed();
        $response = app(MonthlyForecast::class)->status();
        $this->assertSame('negative_prediction', $response['reason_code']);
        $this->assertSame([], $response['predictions']);
        $this->assertSame('-100.0', File::json($this->snapshotPath)['model_output']['prediction']['raw']);
    }

    public function test_process_timeout_preserves_the_page_and_reports_an_unavailable_result(): void
    {
        $this->history();
        Process::fake(function (): never {
            $process = new SymfonyProcess(['python']);
            throw new ProcessTimedOutException(new SymfonyTimeout($process, SymfonyTimeout::TYPE_GENERAL), Process::result(exitCode: 1));
        });
        $this->artisan('forecast:refresh')->assertFailed();
        $this->actingAs(User::factory()->create());
        $this->getJson(route('owner.forecasting.data'))->assertOk()
            ->assertJsonPath('status', 'unavailable')->assertJsonPath('reason_code', 'generation_failed');
    }

    private function history(): void
    {
        $months = ['2026-07' => '8898000', '2026-08' => '9010000', '2026-09' => '8573000', '2026-10' => '8792000'];
        $points = [];
        foreach ($months as $month => $amount) {
            $points[] = ['month' => $month, 'amount' => $amount, 'data_kind' => 'synthetic'];
        }

        $window = ['status' => 'available', 'data_kind' => 'synthetic', 'missing_months' => []];
        $mock = $this->mock(MonthlyRevenueHistory::class);
        $mock->shouldReceive('window')->withNoArgs()->andReturn($window + ['target_month' => '2026-11', 'actual' => array_slice($points, 1)]);
        $mock->shouldReceive('window')->with('2026-10')->andReturn($window + ['target_month' => '2026-10', 'actual' => array_slice($points, 0, 3)]);
    }

    private function successfulProcess(string $amount = '8573667', bool $negative = false): void
    {
        Process::fake(fn () => Process::result(output: json_encode([
            'model' => 'LinearRegression',
            'target_month' => '2026-11',
            'prediction' => ['amount' => $amount, 'raw' => $amount.'.0', 'negative' => $negative],
            'holdout' => ['month' => '2026-10', 'amount' => '8502000', 'raw' => '8502000.0', 'negative' => false],
        ], JSON_THROW_ON_ERROR)));
    }
}
