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
                $this->districtCounters[$district] = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
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
            'task_id'           => $taskId,
            'state'             => $row['state'],
            'district'          => $row['district'],
            'block'             => $row['block'],
            'panchayat'         => $row['panchayat'],
            'ward'              => isset($row['ward']) ? $row['ward'] : null,
            'pole'              => isset($row['pole']) ? $row['pole'] : null,
            'complete_pole_number' => isset($row['complete_pole_number']) ? $row['complete_pole_number'] : null,
            'uname'             => isset($row['uname']) ? $row['uname'] : null,
            'SID'               => isset($row['sid']), // Ensure column names match Excel header?$row['sid']:nulls
            'district_id'       => isset($row['district_id']) ? $row['district_id'] : null,
            'block_id'          => isset($row['block_id']) ? $row['block_id'] : null,
            'panchayat_id'      => isset($row['panchayat_id']) ? $row['panchayat_id'] : null,
            'ward_id'           => isset($row['ward_id']) ? $row['ward_id'] : null,
            'pole_id'           => isset($row['pole_id']) ? $row['pole_id'] : null,
            'luminary_qr'       => isset($row['luminary_qr']) ? $row['luminary_qr'] : null,
            'battery_qr'        => isset($row['battery_qr']) ? $row['battery_qr'] : null,
            'panel_qr'          => isset($row['panel_qr']) ? $row['panel_qr'] : null,
            'file'              => isset($row['file']) ? $row['file'] : null,
            'lat'               => isset($row['lat']) ? $row['lat'] : null,
            'lng'               => isset($row['lng']) ? $row['lng'] : null,
            'beneficiary'       => isset($row['beneficiary']) ? $row['beneficiary'] : null,
            'remark'            => isset($row['remark']) ? $row['remark'] : null,
            'project_id' => $this->projectId,
        ]);
    }
    public function rules(): array
    {
        return [
            'state'     => 'required',
            'district'  => 'required',
            'block'     => 'required',
            'panchayat' => 'required',
            'ward'      => 'nullable',   // ward is nullable and should be an array
        ];
    }
}
