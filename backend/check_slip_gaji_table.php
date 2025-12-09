<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

if (Schema::hasTable('slip_gaji')) {
    echo "✓ Table 'slip_gaji' exists\n";
} else {
    echo "✗ Table 'slip_gaji' does NOT exist\n";
    echo "Please run: php artisan migrate\n";
}
