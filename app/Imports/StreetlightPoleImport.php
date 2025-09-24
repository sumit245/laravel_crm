<?php

namespace App\Imports;

use Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
// Import the necessary concerns
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue; // Optional: For background processing
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB; // Import DB facade for updates
use Carbon\Carbon;
use App\Models\Pole;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\InventoryDispatch;

// Implement WithChunkReading to process the file in batches
class StreetlightPoleImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function collection(Collection $rows)
    {
        $missingItems = [];
        foreach ($rows as $row) {
            // Skip if pole already exists
            Log::info("Processing pole: " . $row['complete_pole_number']);
            $pole = Pole::where('complete_pole_number', $row['complete_pole_number'])->first();
            if ($pole) {
                Log::info('Pole Already imported' . $pole->complete_pole_number);
                continue;
            }


            // Find related models
            $streetlight = Streetlight::where([
                ['district', $row['district']],
                ['block', $row['block']],
                ['panchayat', $row['panchayat']]
            ])->first();

            if (!$streetlight) {
                throw new \Exception("Streetlight not found for: {$row['district']}, {$row['block']}, {$row['panchayat']}");
            }

            $task = StreetlightTask::where('site_id', $streetlight->id)->first();

            if (!$task) {
                throw new \Exception("Target not allotted for site ID: {$streetlight->id}");
            }

            // Prepare pole data
            $poleData = [
                'task_id' => $task->id,
                'isSurveyDone' => true,
                'beneficiary' => $row['beneficiary'] ?? null,
                'beneficiary_contact' => $row['beneficiary_contact'] ?? null,
                'ward_name' => $row['ward_name'],
                'isNetworkAvailable' => true,
                'isInstallationDone' => true,
                'luminary_qr' => $row['luminary_qr'],
                'sim_number' => $row['sim_number'],
                'battery_qr' => $row['battery_qr'],
                'panel_qr' => $row['panel_qr'],
                'lat' => $row['lat'],
                'lng' => $row['long'],
                'updated_at' => Carbon::parse($row['date_of_installation']),
            ];

            // If pole is new, perform checks and create it.
            // $itemsToDispatch = [
            //     (string) $row['battery_qr'],
            //     (string) $row['panel_qr'],
            //     (string) $row['luminary_qr']
            // ];

            // Check for missing items (this logic remains the same)
            // foreach ($itemsToDispatch as $serialNumber) {
            //     $dispatch = InventoryDispatch::where('serial_number', $serialNumber)
            //         ->whereNull('streetlight_pole_id')
            //         ->where('is_consumed', 0)
            //         ->first();
            //     if (!$dispatch) {
            //         $missingItems[] = "Material with serial '{$serialNumber}' not yet dispatched to vendor";
            //     }
            // }

            // if (!empty($missingItems)) {
            //     // If items are missing for this row, skip to next row after collecting errors.
            //     // This prevents a partial creation if one item is missing.
            //     continue;
            // }

            $poleData['complete_pole_number'] = $row['complete_pole_number'];
            $newPole = Pole::create($poleData);
            Log::info('Pole Created: ' . $newPole->complete_pole_number);

            // **OPTIMIZATION: Update inventory in a single query**
            // InventoryDispatch::whereIn('serial_number', $itemsToDispatch)
            //     ->whereNull('streetlight_pole_id')
            //     ->where('is_consumed', 0)
            //     ->update([
            //         'streetlight_pole_id' => $newPole->id,
            //         'is_consumed' => 1,
            //         'total_quantity' => 0, // Assuming this logic is correct
            //         'updated_at' => Carbon::now()
            //     ]);

            // **OPTIMIZATION: Increment counters in a single query**
            $streetlight->update([
                'number_of_surveyed_poles' => DB::raw('number_of_surveyed_poles + 1'),
                'number_of_installed_poles' => DB::raw('number_of_installed_poles + 1'),
            ]);
        }

        // After the loop, if any missing items were found, throw one exception with all of them.
        // if (!empty($missingItems)) {
        //     throw new \Exception("The following items are missing or already consumed: " . implode(", ", array_unique($missingItems)));
        // }
    }

    // Set the chunk size
    public function chunkSize(): int
    {
        return 40000; // You can adjust this number based on your server's performance
    }
}
