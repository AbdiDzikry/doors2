<?php
require 'vendor/autoload.php';

use Rap2hpoutre\FastExcel\FastExcel;

try {
    $rows = (new FastExcel)->import('kontak update dharma.xlsx');
    $firstRow = $rows->first();
    
    echo "First Row Keys:\n";
    foreach ($firstRow as $key => $value) {
        echo "Key: [" . $key . "] Value: " . $value . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
