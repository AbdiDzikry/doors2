<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Schema::getColumnListing('meetings');
print_r($columns);

// Check if description is nullable
$description = DB::select("SHOW COLUMNS FROM meetings WHERE Field = 'description'");
print_r($description);
