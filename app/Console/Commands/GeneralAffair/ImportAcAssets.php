<?php

namespace App\Console\Commands\GeneralAffair;

use Illuminate\Console\Command;
use App\Models\GeneralAffair\GaAcAsset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImportAcAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ga:import-assets {file : Path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import AC Assets from legacy CSV (Jadwal Maintenance)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $this->info("Starting import from $file...");

        $handle = fopen($file, 'r');
        if ($handle === false) {
            $this->error("Cannot open file.");
            return 1;
        }

        // Configuration
        $delimiter = ';';
        $headerLinesToSkip = 7; // Rows 1-7 are header garbage
        $currentRow = 0;
        $importedCount = 0;
        $updatedCount = 0;

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== false) {
                $currentRow++;

                // Skip header lines
                if ($currentRow <= $headerLinesToSkip) {
                    continue;
                }

                // Skip empty rows (check if SKU column is empty)
                // Index 2 is No AC (SKU)
                // Array: 0=NO, 1=RUANGAN, 2=NO AC, 3=JENIS, 4=PK
                if (empty($data[2])) {
                    $this->warn("Row $currentRow: SKIPPING - Empty SKU.");
                    continue;
                }

                // Check for secondary headers (repeating headers in file)
                if (str_contains(strtoupper($data[1]), 'RUANGAN') || str_contains(strtoupper($data[1]), 'NO AC')) {
                    $this->warn("Row $currentRow: SKIPPING - Detected Header Row.");
                    continue;
                }

                // Extract Data
                $location = trim($data[1] ?? '');
                $sku = trim($data[2] ?? '');
                $type = trim($data[3] ?? ''); // Model/Type (Split/Cassette)
                $pk = trim($data[4] ?? '');

                // Generate Name
                $name = "AC " . ucwords(strtolower($location)) . " ($sku)";

                // Sanitize SKU (remove spaces? No, exact match is better)

                $this->line("Processing: $sku - $location");

                $asset = GaAcAsset::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'name' => $name,
                        'location' => $location,
                        'model' => $type, // Mapping 'JENIS' to 'model'
                        'brand' => null,  // CSV doesn't have brand
                        'pk' => $pk,
                        'status' => 'good', // Default assumption
                    ]
                );

                if ($asset->wasRecentlyCreated) {
                    $importedCount++;
                } else {
                    $updatedCount++;
                }
            }

            DB::commit();
            fclose($handle);

            $this->info("--------------------------------------");
            $this->info("Import Complete!");
            $this->info("Total Imported: $importedCount");
            $this->info("Total Updated:  $updatedCount");
            $this->info("--------------------------------------");

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            $this->error("Error importing: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
