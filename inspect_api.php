<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(App\Services\EmployeeApiService::class);
    $data = $service->fetchEmployees();

    $output = "";
    $output .= "API Fetch Successful. Count: " . count($data) . "\n";
    if (count($data) > 0) {
        $output .= "First Record Keys:\n";
        $output .= print_r(array_keys($data[0]), true);
        $output .= "First Record Data:\n";
        $output .= print_r($data[0], true);
    } else {
        $output .= "No data returned.\n";
    }
    file_put_contents('api_dump_safe.txt', $output);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
