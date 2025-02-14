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
        Log::info('Processing row:', $row);

        // Fetch the district ID based on the exact district name
        $districtId = $this->getDistrictId($row['district']);
        $stateId    = $this->getStateId($row['state']);

        // If no matching district or state is found, log an error and skip this row
        if (!$districtId) {
            Log::error('District not found for: ' . $row['district']);
            return null;
        }
        if (!$stateId) {
            Log::error('State not found for: ' . $row['state']);
            return null;
        }

        return new Site([
            'project_id'       => $this->projectId,
            'site_name'        => $row['site_name'],
            'breda_sl_no'      => $row['breda_sl_no'],
            'state'            => $stateId,
            'district'         => $districtId,
            'location'         => $row['location'],
            'project_capacity' => $row['project_capacity'],
            'ca_number'        => $row['ca_number'],
            'contact_no'       => $row['contact_no'],
            'sanction_load'    => $row['sanction_load_in_kwp'],
            'meter_number'     => $row['meter_no'],
        ]);
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
