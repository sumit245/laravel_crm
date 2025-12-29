<?php

namespace App\Imports;

use Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Pole;
use App\Models\Streetlight;

class SitePoleImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected $siteId;
    protected $taskId;
    protected $errors = [];
    protected $importedCount = 0;

    public function __construct($siteId, $taskId)
    {
        $this->siteId = $siteId;
        $this->taskId = $taskId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $rowIndex => $row) {
            try {
                // Skip if pole number is missing
                if (empty($row['complete_pole_number'])) {
                    $this->errors[] = [
                        'row' => $rowIndex + 2, // +2 for header row and 1-based index
                        'pole_number' => '',
                        'reason' => 'Pole number is required'
                    ];
                    continue;
                }

                $poleNumber = $row['complete_pole_number'];

                // Check if pole already exists
                $existingPole = Pole::where('complete_pole_number', $poleNumber)->first();
                if ($existingPole) {
                    // Update existing pole if it belongs to this site's task
                    if ($existingPole->task_id == $this->taskId) {
                        $existingPole->update([
                            'beneficiary' => $row['beneficiary'] ?? $existingPole->beneficiary,
                            'beneficiary_contact' => $row['beneficiary_contact'] ?? $existingPole->beneficiary_contact,
                            'ward_name' => $row['ward_name'] ?? $existingPole->ward_name,
                            'luminary_qr' => $row['luminary_qr'] ?? $existingPole->luminary_qr,
                            'battery_qr' => $row['battery_qr'] ?? $existingPole->battery_qr,
                            'panel_qr' => $row['panel_qr'] ?? $existingPole->panel_qr,
                            'sim_number' => $row['sim_number'] ?? $existingPole->sim_number,
                            'lat' => $row['lat'] ?? $existingPole->lat,
                            'lng' => $row['long'] ?? $existingPole->lng,
                        ]);
                        $this->importedCount++;
                        Log::info('Pole updated: ' . $poleNumber);
                    } else {
                        $this->errors[] = [
                            'row' => $rowIndex + 2,
                            'pole_number' => $poleNumber,
                            'reason' => 'Pole belongs to a different site/task'
                        ];
                    }
                    continue;
                }

                // Validate ward name matches site's wards
                $site = Streetlight::find($this->siteId);
                if ($site && !empty($row['ward_name'])) {
                    $siteWards = collect(explode(",", $site->ward))
                        ->map(fn($w) => "Ward " . trim($w))
                        ->toArray();
                    if (!in_array($row['ward_name'], $siteWards)) {
                        $this->errors[] = [
                            'row' => $rowIndex + 2,
                            'pole_number' => $poleNumber,
                            'reason' => 'Ward ' . $row['ward_name'] . ' does not belong to this site'
                        ];
                        continue;
                    }
                }

                // Create new pole
                $poleData = [
                    'task_id' => $this->taskId,
                    'complete_pole_number' => $poleNumber,
                    'beneficiary' => $row['beneficiary'] ?? null,
                    'beneficiary_contact' => $row['beneficiary_contact'] ?? null,
                    'ward_name' => $row['ward_name'] ?? null,
                    'luminary_qr' => $row['luminary_qr'] ?? null,
                    'battery_qr' => $row['battery_qr'] ?? null,
                    'panel_qr' => $row['panel_qr'] ?? null,
                    'sim_number' => $row['sim_number'] ?? null,
                    'lat' => $row['lat'] ?? null,
                    'lng' => $row['long'] ?? null,
                    'isSurveyDone' => isset($row['is_survey_done']) ? (bool)$row['is_survey_done'] : false,
                    'isInstallationDone' => isset($row['is_installation_done']) ? (bool)$row['is_installation_done'] : false,
                ];

                if (isset($row['date_of_installation']) && !empty($row['date_of_installation'])) {
                    try {
                        $poleData['updated_at'] = Carbon::parse($row['date_of_installation']);
                    } catch (\Exception $e) {
                        // Ignore date parsing errors
                    }
                }

                Pole::create($poleData);
                $this->importedCount++;
                Log::info('Pole created: ' . $poleNumber);

                // Update site counters
                $site = Streetlight::find($this->siteId);
                if ($site) {
                    $updateFields = [];
                    if (isset($poleData['isSurveyDone']) && $poleData['isSurveyDone']) {
                        $updateFields['number_of_surveyed_poles'] = DB::raw('COALESCE(number_of_surveyed_poles, 0) + 1');
                    }
                    if (isset($poleData['isInstallationDone']) && $poleData['isInstallationDone']) {
                        $updateFields['number_of_installed_poles'] = DB::raw('COALESCE(number_of_installed_poles, 0) + 1');
                    }
                    if (!empty($updateFields)) {
                        $site->update($updateFields);
                    }
                }
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $rowIndex + 2,
                    'pole_number' => $row['complete_pole_number'] ?? 'Unknown',
                    'reason' => $e->getMessage()
                ];
                Log::error('Error importing pole: ' . $e->getMessage());
            }
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}














