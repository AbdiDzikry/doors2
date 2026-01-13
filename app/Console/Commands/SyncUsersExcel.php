<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Rap2hpoutre\FastExcel\FastExcel;

class SyncUsersExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-users-excel {file : Path to the excel file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user phone and email from Excel file based on NPK';

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

        $this->info("Reading file: $file");

        $users = (new FastExcel)->import($file);
        
        $count = $users->count();
        $this->info("Found {$count} rows.");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $updated = 0;
        $notFound = 0;
        $skipped = 0;

        foreach ($users as $row) {
            // Flexible Header Mapping
            // NPK
            $npk = $row['NPK'] ?? $row['npk'] ?? $row['NIP'] ?? $row['Pegawai ID'] ?? null;
            
            if (!$npk) {
                // Try guessing if keys are numeric indices? No, FastExcel uses headers.
                $skipped++;
                $bar->advance();
                continue;
            }

            // Phone
            $phone = $row['Phone'] ?? $row['phone'] ?? $row['No HP'] ?? $row['Mobile'] ?? $row['Handphone'] ?? null;
            
            // Email
            $email = $row['Email'] ?? $row['email'] ?? $row['Mail'] ?? $row['Alamat Email'] ?? null;

            // Find User
            $user = User::where('npk', $npk)->first();

            if ($user) {
                $updates = [];
                if ($phone && $user->phone !== (string)$phone) {
                    $updates['phone'] = (string)$phone;
                }
                if ($email && $user->email !== $email) {
                    $updates['email'] = $email;
                }

                if (!empty($updates)) {
                    $user->update($updates);
                    $updated++;
                }
            } else {
                $notFound++;
                 // Option: Create user? The spec said "Sync/Update".
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Rows', $count],
                ['Updated', $updated],
                ['Not Found (NPK)', $notFound],
                ['Skipped (No NPK)', $skipped],
            ]
        );

        return 0;
    }
}
