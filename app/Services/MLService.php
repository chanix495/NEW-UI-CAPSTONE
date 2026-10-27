<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Runs the Python models in /ml (SARIMAX sales forecast, XGBoost spoilage).
 * The scripts read from and write to the same MySQL database.
 */
class MLService
{
    /**
     * Applies the saved per-fruit SARIMAX models to the latest sales (fast). $refit
     * re-estimates every fruit's parameters first, which takes a few minutes.
     */
    public function runSalesForecast(int $horizon = 7, bool $refit = false): array
    {
        return $this->run('sales_forecast.py', array_merge(['--horizon', (string) $horizon], $refit ? ['--refit'] : []));
    }

    public function runSpoilagePrediction(int $horizon = 7): array
    {
        return $this->run('spoilage_model.py', ['--horizon', (string) $horizon]);
    }

    private function run(string $script, array $args = []): array
    {
        $db = config('database.connections.' . config('database.default'));

        $result = Process::path(base_path())
            ->timeout((int) config('services.ml.timeout'))
            ->env([
                'DB_HOST' => $db['host'],
                'DB_PORT' => (string) $db['port'],
                'DB_DATABASE' => $db['database'],
                'DB_USERNAME' => $db['username'],
                'DB_PASSWORD' => (string) $db['password'],
                'APP_TIMEZONE' => config('app.timezone'),
                'OPENWEATHER_TIMEZONE' => config('services.openweather.timezone'),
                'PYTHONIOENCODING' => 'utf-8',
            ])
            ->run(array_merge([config('services.ml.python'), base_path('ml/' . $script)], $args));

        // The script prints a single JSON line as its last output
        $lines = array_filter(explode("\n", trim($result->output())));
        $payload = json_decode((string) end($lines), true);

        if (!is_array($payload)) {
            throw new RuntimeException("{$script} failed: " . trim($result->errorOutput() ?: $result->output()));
        }

        return $payload;
    }
}
