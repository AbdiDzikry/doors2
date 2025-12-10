<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = app(\App\Services\EmployeeApiService::class);
    $data = $service->fetchEmployees();

    $count = count($data);
    
    // Check if the keys look like integers (0, 1, 2...) meaning valid array list, 
    // or string keys (meaning single object wrapper)
    $keys = array_keys($data);
    $isIndexed = array_filter($keys, 'is_int') === $keys;

    $output = "Total Records Received: " . $count . "\n";
    $output .= "Data Type: " . ($isIndexed ? "List of Employees (Indexed Array)" : "Assoc Array (Single Object?)") . "\n";
    
    // Check for potential pagination keys that might be hidden outside the 'data' wrapper if the service unwraps it
    // Note: The service currently does: return $data['data'] ?? $data;
    // So we are inspecting the *result* of that unwrapping.
    
    $output .= "First 5 items sample (Name only):\n";
    if ($isIndexed) {
        for($i=0; $i < min(5, $count); $i++) {
            $output .= "[$i] " . ($data[$i]['EMPLOYEE_NAME'] ?? 'No Name') . " (NPK: " . ($data[$i]['EMPLOYEE_NO'] ?? '-') . ")\n";
        }
    }

    file_put_contents('api_count_check.txt', $output);

} catch (\Exception $e) {
    file_put_contents('api_count_check.txt', "Error: " . $e->getMessage());
}
