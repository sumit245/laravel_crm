<?php

namespace App\Imports;

use App\Models\Streetlight;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;

class StreetlightImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $projectId;
    protected $districtCounters = [];

    // Constructor to accept project ID
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function model(array $row)
    {
        $district = strtoupper(substr($row['district'], 0, 3)); // Extract first 3 letters of district

        // If the district is not already counted, find the last task_id for it
        if (!isset($this->districtCounters[$district])) {
            $lastTask = Streetlight::where('task_id', 'LIKE', "{$district}%")
                ->orderBy('task_id', 'desc')
                ->first();

            if ($lastTask) {
                // Extract numeric part and increment
                preg_match('/(\d+)$/', $lastTask->task_id, $matches);
                $this->districtCounters[$district] = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
            } else {
                $this->districtCounters[$district] = 1;
            }
        }

        // Generate task_id
        $taskId = sprintf('%s%03d', $district, $this->districtCounters[$district]);
        $this->districtCounters[$district]++; // Increment counter for next row
        // Convert the ward field to an array if it's not already
        // Convert 'ward' to an array of integers if it's not empty
        $ward = isset($row['ward']) && !empty($row['ward']) ? array_map('intval', explode(',', $row['ward'])) : [];

        return new Streetlight([
            'task_id' => $taskId,
            'state' => $row['state'],
            'district' => $row['district'],
            'block' => $row['block'],
            'panchayat' => $row['panchayat'],
            'ward' => isset($row['ward']) ? $row['ward'] : null,
            // 'total_poles' => isset($row['total_scope']) ? $row['total_poles'] : (isset($row['pole']) ? $row['pole'] : null),
            'mukhiya_contact' => isset($row['mukhiya_contact']) ? $row['mukhiya_contact'] : null,
            'project_id' => $this->projectId,
            'district_code' => isset($row['district_code']) ? $row['district_code'] : null,
            'block_code' => isset($row['block_code']) ? $row['block_code'] : null,
            'panchayat_code' => isset($row['panchayat_code']) ? $row['panchayat_code'] : null,
            'ward_type' => isset($row['ward_type']) ? $row['ward_type'] : null,
            // Note: The following columns were dropped in migration 2025_08_06_204122:
            // complete_pole_number, uname, SID, district_id, block_id, panchayat_id, ward_id,
            // luminary_qr, battery_qr, panel_qr, file, lat, lng, remark
            // These are now handled in the poles table or other related tables
        ]);
    }
    public function rules(): array
    {
        return [
            'state' => 'required',
            'district' => 'required',
            'block' => 'required',
            'panchayat' => 'required',
            'ward' => 'nullable',   // ward is nullable and should be an array
        ];
    }
}
