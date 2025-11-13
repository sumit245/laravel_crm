<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ReadExcelFile extends Command
{
    protected $signature = 'excel:read {file}';
    protected $description = 'Read and display Excel file structure';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        try {
            $data = Excel::toArray([], $filePath);
            
            if (empty($data[0])) {
                $this->error("No data found in the Excel file");
                return 1;
            }

            $rows = $data[0];
            
            // Display headers
            $headers = $rows[0] ?? [];
            $this->info("=== EXCEL FILE STRUCTURE ===");
            $this->info("Total Rows: " . count($rows));
            $this->line("");
            $this->info("Headers (Column Names):");
            foreach ($headers as $index => $header) {
                $this->line("  Column " . ($index + 1) . ": " . ($header ?: "[EMPTY]"));
            }
            
            $this->line("");
            $this->info("First 5 Data Rows:");
            $this->line(str_repeat("-", 100));
            
            for ($i = 1; $i <= min(5, count($rows) - 1); $i++) {
                $row = $rows[$i];
                $this->line("Row " . ($i + 1) . ":");
                foreach ($headers as $colIndex => $header) {
                    $value = $row[$colIndex] ?? '';
                    $this->line("  " . $header . ": " . ($value ?: "[EMPTY]"));
                }
                $this->line("");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error reading file: " . $e->getMessage());
            return 1;
        }
    }
}
