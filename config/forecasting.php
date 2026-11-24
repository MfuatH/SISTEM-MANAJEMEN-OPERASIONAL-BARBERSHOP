<?php

return [
    'enabled' => env('FORECAST_ENABLED', true),
    'python' => env('FORECAST_PYTHON', 'python'),
    'snapshot_path' => storage_path('app/private/forecasting/latest.json'),
];
