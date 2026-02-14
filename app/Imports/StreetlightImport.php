<?php

namespace App\Imports;

use App\Models\Streetlight;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StreetlightImport implements ToCollection, WithHeadingRow
{
    protected $projectId;
    protected $districtCounters = [];
    protected array $errors = [];
    protected int $importedCount = 0;
    protected int $updatedCount = 0;
    protected int $skippedCount = 0;

    // Constructor to accept project ID
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if (empty($row['district']) && empty($row['block']) && empty($row['panchayat'])) {
                    continue;
                }

                $state = trim($row['state'] ?? '');
                $district = trim($row['district'] ?? '');
                $block = trim($row['block'] ?? '');
                $panchayat = trim($row['panchayat'] ?? '');
                $ward = trim($row['ward'] ?? '');
                $totalPoles = isset($row['total_poles']) ? (int) $row['total_poles'] : null;
                $mukhiyaContact = trim($row['mukhiya_contact'] ?? '');

                // Validate required fields
                if (empty($state) || empty($district) || empty($block) || empty($panchayat)) {
                    $this->skippedCount++;
                    $this->errors[] = [
                        'row' => $index + 2,
                        'district' => $district,
                        'block' => $block,
                        'panchayat' => $panchayat,
                        'reason' => 'Missing required fields (State, District, Block, or Panchayat)'
                    ];
                    continue;
                }

                // Check for existing site with same District->Block->Panchayat
                $existingSite = Streetlight::where('project_id', $this->projectId)
                    ->where('district', $district)
                    ->where('block', $block)
                    ->where('panchayat', $panchayat)
                    ->first();

                if ($existingSite) {
                    // Parse wards
                    $existingWards = !empty($existingSite->ward)
                        ? array_map('intval', explode(',', $existingSite->ward))
                        : [];
                    $newWards = !empty($ward)
                        ? array_map('intval', explode(',', $ward))
                        : [];

                    // Check if wards are the same
                    sort($existingWards);
                    sort($newWards);
                    $sameWards = $existingWards === $newWards;

                    if ($sameWards) {
                        // Same District->Block->Panchayat->Wards -> Skip with error
                        $this->skippedCount++;
                        $this->errors[] = [
                            'row' => $index + 2,
                            'district' => $district,
                            'block' => $block,
                            'panchayat' => $panchayat,
                            'ward' => $ward,
                            'reason' => 'Site already exists'
                        ];
                        continue;
                    } else {
                        // Different wards -> Update existing site by merging wards
                        $mergedWards = array_unique(array_merge($existingWards, $newWards));
                        sort($mergedWards);
                        $mergedWardsString = implode(',', $mergedWards);

                        // Calculate new total_poles (assuming 10 poles per ward)
                        $newTotalPoles = count($mergedWards) * 10;

                        $existingSite->update([
                            'ward' => $mergedWardsString,
                            'total_poles' => $newTotalPoles,
                            'mukhiya_contact' => !empty($mukhiyaContact) ? $mukhiyaContact : $existingSite->mukhiya_contact,
                            'district_code' => isset($row['district_code']) ? trim($row['district_code']) : $existingSite->district_code,
                            'block_code' => isset($row['block_code']) ? trim($row['block_code']) : $existingSite->block_code,
                            'panchayat_code' => isset($row['panchayat_code']) ? trim($row['panchayat_code']) : $existingSite->panchayat_code,
                        ]);

                        $this->updatedCount++;
                        continue;
                    }
                }

                // No duplicate found -> Create new site
                $districtPrefix = strtoupper(substr($district, 0, 3));

                // Get or initialize district counter
                if (!isset($this->districtCounters[$districtPrefix])) {
                    $lastTask = Streetlight::where('task_id', 'LIKE', "{$districtPrefix}%")
                        ->orderBy('task_id', 'desc')
                        ->first();

                    if ($lastTask) {
                        preg_match('/(\d+)$/', $lastTask->task_id, $matches);
                        $this->districtCounters[$districtPrefix] = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
                    } else {
                        $this->districtCounters[$districtPrefix] = 1;
                    }
                }

                $taskId = sprintf('%s%03d', $districtPrefix, $this->districtCounters[$districtPrefix]);
                $this->districtCounters[$districtPrefix]++;

                // Calculate total_poles if not provided (10 per ward)
                if ($totalPoles === null && !empty($ward)) {
                    $wardCount = count(array_map('intval', explode(',', $ward)));
                    $totalPoles = $wardCount * 10;
                }

                Streetlight::create([
                    'task_id' => $taskId,
                    'state' => $state,
                    'district' => $district,
                    'block' => $block,
                    'panchayat' => $panchayat,
                    'ward' => !empty($ward) ? $ward : null,
                    'total_poles' => $totalPoles,
                    'mukhiya_contact' => !empty($mukhiyaContact) ? $mukhiyaContact : null,
                    'project_id' => $this->projectId,
                    'district_code' => isset($row['district_code']) ? trim($row['district_code']) : null,
                    'block_code' => isset($row['block_code']) ? trim($row['block_code']) : null,
                    'panchayat_code' => isset($row['panchayat_code']) ? trim($row['panchayat_code']) : null,
                    'ward_type' => isset($row['ward_type']) ? trim($row['ward_type']) : null,
                ]);

                $this->importedCount++;
            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->errors[] = [
                    'row' => $index + 2,
                    'district' => $row['district'] ?? '',
                    'block' => $row['block'] ?? '',
                    'panchayat' => $row['panchayat'] ?? '',
                    'reason' => 'Error: ' . $e->getMessage()
                ];
                Log::error("StreetlightImport row " . ($index + 2) . " error: " . $e->getMessage(), ['row' => $row ?? []]);
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}
