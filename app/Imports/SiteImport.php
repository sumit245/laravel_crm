<?php

namespace App\Imports;

use App\Models\City;
use App\Models\Site;
use App\Models\State;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiteImport implements ToModel, WithHeadingRow
{
    protected $projectId;

    // Constructor to accept project ID
    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // // Fetch the district ID based on the exact district name
        // $districtId = $this->getDistrictId($row['district']);
        // $stateId    = $this->getStateId($row['state']);
        // Initialize districtId and stateId as null
        // $districtId = null;
        // $stateId = null;

        // // If 'district' key exists, fetch its ID
        // if (isset($row['district'])) {
        //     $districtId = $this->getDistrictId($row['district']);
        //     if (!$districtId) {
        //         Log::warning('Invalid district in import row.', $row);
        //         return null;
        //     }
        // }

        // If no matching district or state is found, log an error and skip this row
        // if (!$districtId || !$stateId) {
        //     return null;
        // }

        $data = [
            'project_id'          => $this->projectId,
            'state'               => $stateId ?? null,
            'district'            => $districtId ?? null,
            'breda_sl_no'         => $row['breda_sl_no'] ?? null,
            'division'            => $row['division'] ?? null,
            'site_name'           => $row['site_name'] ?? null,
            'location'            => $row['location'] ?? null,
            'sanction_load'       => $row['sanction_load_in_kwp'] ?? null,
            'ca_number'           => $row['ca_number'] ?? null,
            'meter_number'        => $row['meter_no'] ?? null,
            'bts_department_name' => $row['bts_department_name'] ?? null,
            'contact_no'          => $row['contact_no'] ?? null,
            'installation_status' => isset($row['installation_status'])
                ? (strtolower(trim($row['installation_status'])) === 'yes' ? 1 : 0)
                : null,
        ];

        // Newly added enum columns with yes/no status
        $enumColumns = [
            'drawing_approval',
            'inspection',
            'material_supplied',
            'structure_installation',
            'structure_foundation',
            'pv_module_installation',
            'inverter_installation',
            'dcdb_acdb_installaation',
            'dc_cabelling',
            'ac_cabelling',
            'ac_cable_termination',
            'dc_earthing',
            'ac_earthing',
            'lighntning_arrestor',
            'remote_monitoring_unit',
            'fire_safety',
            'net_meter_registration',
            'meter_installaton_commission',
            'performance_guarantee_test',
            'handover_status',
        ];

        foreach ($enumColumns as $column) {
            if (isset($row[$column])) {
                // Clean value and enforce lowercase yes/no for consistency
                $value = strtolower(trim($row[$column]));
                $data[$column] = in_array($value, ['yes', 'no']) ? $value : 'no';
            }
        }

        return new Site($data);
    }

    /**
     * Fetch the exact district ID based on the district name.
     *
     * @param string $districtName
     * @return int|null
     */
    private function getDistrictId($districtName)
    {
        return City::whereRaw('LOWER(name) = ?', [strtolower($districtName)])->value('id');
    }

    /**
     * Fetch the exact state ID based on the state name.
     *
     * @param string $stateName
     * @return int|null
     */
    private function getStateId($stateName)
    {
        return State::whereRaw('LOWER(name) = ?', [strtolower($stateName)])->value('id');
    }
}
