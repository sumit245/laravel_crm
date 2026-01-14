<?php

namespace App\Imports;

use App\Models\Pole;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\PoleImportJob;
use App\Services\Import\PoleImportService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StreetlightPoleImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected ?string $jobId;
    protected ?PoleImportJob $job;
    protected PoleImportService $importService;
    protected array $errors = [];
    protected int $successCount = 0;
    protected int $errorCount = 0;

    public function __construct(?string $jobId = null, ?PoleImportJob $job = null)
    {
        $this->jobId = $jobId;
        $this->job = $job;
        $this->importService = app(PoleImportService::class);
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because Excel rows are 1-indexed and we have header row
            $rowArray = $row instanceof Collection ? $row->toArray() : (array) $row;

            try {
                $this->processRow($rowArray, $rowNumber);
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'complete_pole_number' => $rowArray['complete_pole_number'] ?? 'Unknown',
                    'reason' => 'Unexpected error: ' . $e->getMessage(),
                    'details' => $e->getTraceAsString()
                ];
                Log::error('Error processing pole import row', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'row_data' => $rowArray
                ]);
            }
        }
    }

    protected function processRow(array $row, int $rowNumber): void
    {
        // Skip if pole already exists
        $completePoleNumber = trim($row['complete_pole_number'] ?? '');
        if (empty($completePoleNumber)) {
            $this->errorCount++;
            $this->errors[] = [
                'row' => $rowNumber,
                'complete_pole_number' => '',
                'reason' => 'Complete pole number is required'
            ];
            return;
        }

        $existingPole = Pole::where('complete_pole_number', $completePoleNumber)->first();
        if ($existingPole) {
            $this->errorCount++;
            $this->errors[] = [
                'row' => $rowNumber,
                'complete_pole_number' => $completePoleNumber,
                'reason' => "Pole with complete_pole_number '{$completePoleNumber}' already exists"
            ];
            return;
        }

        // Find streetlight site
        $district = trim($row['district'] ?? '');
        $block = trim($row['block'] ?? '');
        $panchayat = trim($row['panchayat'] ?? '');

        if (empty($district) || empty($block) || empty($panchayat)) {
            $this->errorCount++;
            $this->errors[] = [
                'row' => $rowNumber,
                'complete_pole_number' => $completePoleNumber,
                'reason' => 'District, block, and panchayat are required'
            ];
            return;
        }

        $streetlight = Streetlight::where([
            ['district', $district],
            ['block', $block],
            ['panchayat', $panchayat]
        ])->first();

        if (!$streetlight) {
            $this->errorCount++;
            $this->errors[] = [
                'row' => $rowNumber,
                'complete_pole_number' => $completePoleNumber,
                'reason' => "Streetlight site not found for: {$district}, {$block}, {$panchayat}"
            ];
            return;
        }

        // Find task
        $task = StreetlightTask::where('site_id', $streetlight->id)->first();
        if (!$task) {
            $this->errorCount++;
            $this->errors[] = [
                'row' => $rowNumber,
                'complete_pole_number' => $completePoleNumber,
                'reason' => "Target not allotted for site {$streetlight->panchayat}"
            ];
            return;
        }

        // Validate inventory items
        $batteryQr = trim($row['battery_qr'] ?? '');
        $luminaryQr = trim($row['luminary_qr'] ?? '');
        $panelQr = trim($row['panel_qr'] ?? '');

        $itemsToValidate = [];
        if (!empty($batteryQr)) {
            $itemsToValidate['battery_qr'] = $batteryQr;
        }
        if (!empty($luminaryQr)) {
            $itemsToValidate['luminary_qr'] = $luminaryQr;
        }
        if (!empty($panelQr)) {
            $itemsToValidate['panel_qr'] = $panelQr;
        }

        // Validate each inventory item
        $validationErrors = [];
        $validDispatches = [];

        foreach ($itemsToValidate as $field => $serialNumber) {
            $validation = $this->importService->validateAndDispatchInventory($serialNumber, $task);
            
            if ($validation['status'] === 'error') {
                $validationErrors[] = $validation['error'];
            } else {
                $validDispatches[$field] = $validation['dispatch'];
            }
        }

        // If any validation errors, skip this row
        if (!empty($validationErrors)) {
            $this->errorCount++;
            $this->errors[] = [
                'row' => $rowNumber,
                'complete_pole_number' => $completePoleNumber,
                'reason' => implode('; ', $validationErrors)
            ];
            return;
        }

        // All validations passed, create pole
        DB::beginTransaction();
        try {
            $poleData = [
                'task_id' => $task->id,
                'complete_pole_number' => $completePoleNumber,
                'isSurveyDone' => true,
                'beneficiary' => !empty($row['beneficiary']) ? trim($row['beneficiary']) : null,
                'beneficiary_contact' => !empty($row['beneficiary_contact']) ? trim($row['beneficiary_contact']) : null,
                'ward_name' => !empty($row['ward_name']) ? trim($row['ward_name']) : null,
                'isNetworkAvailable' => true,
                'isInstallationDone' => true,
                'luminary_qr' => !empty($luminaryQr) ? $luminaryQr : null,
                'sim_number' => !empty($row['sim_number']) ? trim($row['sim_number']) : null,
                'battery_qr' => !empty($batteryQr) ? $batteryQr : null,
                'panel_qr' => !empty($panelQr) ? $panelQr : null,
                'lat' => !empty($row['lat']) ? $row['lat'] : null,
                'lng' => !empty($row['long']) ? $row['long'] : null,
            ];

            if (!empty($row['date_of_installation'])) {
                try {
                    $poleData['updated_at'] = Carbon::parse($row['date_of_installation']);
                } catch (\Exception $e) {
                    // Invalid date, use current time
                    $poleData['updated_at'] = Carbon::now();
                }
            }

            $newPole = Pole::create($poleData);

            // Consume inventory for this pole
            $serialNumbers = array_filter(array_values($itemsToValidate));
            if (!empty($serialNumbers)) {
                $this->importService->consumeInventoryForPole($newPole, $serialNumbers);
            }

            // Update streetlight counters
            $streetlight->update([
                'number_of_surveyed_poles' => DB::raw('number_of_surveyed_poles + 1'),
                'number_of_installed_poles' => DB::raw('number_of_installed_poles + 1'),
            ]);

            DB::commit();
            $this->successCount++;

            Log::info('Pole created successfully', [
                'pole_id' => $newPole->id,
                'complete_pole_number' => $completePoleNumber
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorCount++;
            $this->errors[] = [
                'row' => $rowNumber,
                'complete_pole_number' => $completePoleNumber,
                'reason' => 'Error creating pole: ' . $e->getMessage()
            ];
            Log::error('Error creating pole', [
                'complete_pole_number' => $completePoleNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Set the chunk size for processing
     */
    public function chunkSize(): int
    {
        return 100; // Process 100 rows per chunk
    }

    /**
     * Get errors array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get success count
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }
}
