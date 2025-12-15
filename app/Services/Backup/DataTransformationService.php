<?php

namespace App\Services\Backup;

use App\Enums\ProjectType;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Enums\InstallationPhase;

class DataTransformationService
{
    /**
     * Transform user data to human-readable format
     */
    public function transformUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name ?? ($user->firstName . ' ' . $user->lastName),
            'firstName' => $user->firstName ?? '',
            'lastName' => $user->lastName ?? '',
            'email' => $user->email ?? '',
            'username' => $user->username ?? '',
            'contactNo' => $user->contactNo ?? '',
            'address' => $user->address ?? '',
            'role' => $this->convertUserRole($user->role),
            'category' => $user->category ?? '',
            'department' => $user->department ?? '',
            'status' => $this->convertStatus($user->status ?? 'active'),
            'disableLogin' => $user->disableLogin ? 'Disabled' : 'Enabled',
            'accountName' => $user->accountName ?? '',
            'accountNumber' => $user->accountNumber ?? '',
            'ifsc' => $user->ifsc ?? '',
            'bankName' => $user->bankName ?? '',
            'branch' => $user->branch ?? '',
            'gstNumber' => $user->gstNumber ?? '',
            'pan' => $user->pan ?? '',
            'aadharNumber' => $user->aadharNumber ?? '',
            'created_at' => $this->formatDateField($user->created_at ?? null, 'Y-m-d H:i:s'),
            'updated_at' => $this->formatDateField($user->updated_at ?? null, 'Y-m-d H:i:s'),
        ];
    }

    /**
     * Transform pole data to human-readable format
     */
    public function transformPole($pole): array
    {
        return [
            'id' => $pole->id,
            'complete_pole_number' => $pole->complete_pole_number ?? 'N/A',
            'beneficiary' => $pole->beneficiary ?? 'N/A',
            'beneficiary_contact' => $pole->beneficiary_contact ?? 'N/A',
            'ward_name' => $pole->ward_name ?? 'N/A',
            'luminary_qr' => $pole->luminary_qr ?? 'N/A',
            'battery_qr' => $pole->battery_qr ?? 'N/A',
            'panel_qr' => $pole->panel_qr ?? 'N/A',
            'sim_number' => $pole->sim_number ?? 'N/A',
            'rms_status' => $pole->rms_status ?? 'N/A',
            'survey_status' => $pole->isSurveyDone ? 'Done' : 'Not Done',
            'installation_status' => $pole->isInstallationDone ? 'Done' : 'Not Done',
            'network_available' => $pole->isNetworkAvailable ? 'Available' : 'Not Available',
            'lat' => $pole->lat ?? '',
            'lng' => $pole->lng ?? '',
            'remarks' => $pole->remarks ?? '',
            'created_at' => $this->formatDateField($pole->created_at ?? null, 'Y-m-d H:i:s'),
            'updated_at' => $this->formatDateField($pole->updated_at ?? null, 'Y-m-d H:i:s'),
        ];
    }

    /**
     * Transform task data to human-readable format
     */
    public function transformTask($task, $projectType): array
    {
        $data = [
            'id' => $task->id,
            'status' => $this->convertTaskStatus($task->status ?? 'Pending'),
            'start_date' => $this->formatDateField($task->start_date ?? null),
            'end_date' => $this->formatDateField($task->end_date ?? null),
            'description' => $task->description ?? '',
            'materials_consumed' => $task->materials_consumed ?? '',
            'created_at' => $this->formatDateField($task->created_at ?? null, 'Y-m-d H:i:s'),
            'updated_at' => $this->formatDateField($task->updated_at ?? null, 'Y-m-d H:i:s'),
        ];

        // Add project-specific fields
        if ($projectType == ProjectType::STREETLIGHT->value) {
            $data['billed'] = isset($task->billed) ? ($task->billed ? 'Yes' : 'No') : 'N/A';
            $data['panchayat'] = ($task->site && isset($task->site->panchayat)) ? $task->site->panchayat : 'N/A';
            $data['ward'] = ($task->site && isset($task->site->ward)) ? $task->site->ward : 'N/A';
            $data['engineer_name'] = $task->engineer ? (($task->engineer->firstName ?? '') . ' ' . ($task->engineer->lastName ?? '')) : 'N/A';
            $data['vendor_name'] = $task->vendor ? ($task->vendor->name ?? (($task->vendor->firstName ?? '') . ' ' . ($task->vendor->lastName ?? ''))) : 'N/A';
            $data['manager_name'] = $task->manager ? (($task->manager->firstName ?? '') . ' ' . ($task->manager->lastName ?? '')) : 'N/A';
        } else {
            $data['task_name'] = $task->task_name ?? 'N/A';
            $data['activity'] = $task->activity ?? 'N/A';
            $data['site_name'] = ($task->site && isset($task->site->site_name)) ? $task->site->site_name : 'N/A';
            $data['engineer_name'] = $task->engineer ? (($task->engineer->firstName ?? '') . ' ' . ($task->engineer->lastName ?? '')) : 'N/A';
            $data['vendor_name'] = $task->vendor ? ($task->vendor->name ?? (($task->vendor->firstName ?? '') . ' ' . ($task->vendor->lastName ?? ''))) : 'N/A';
            $data['manager_name'] = $task->manager ? (($task->manager->firstName ?? '') . ' ' . ($task->manager->lastName ?? '')) : 'N/A';
        }

        return $data;
    }

    /**
     * Transform site data to human-readable format (rooftop)
     */
    public function transformSite($site): array
    {
        return [
            'id' => $site->id,
            'breda_sl_no' => $site->breda_sl_no ?? 'N/A',
            'site_name' => $site->site_name ?? 'N/A',
            'state' => $site->state ?? 'N/A',
            'district' => $site->district ?? 'N/A',
            'division' => $site->division ?? 'N/A',
            'block' => $site->block ?? 'N/A',
            'location' => $site->location ?? 'N/A',
            'bts_department_name' => $site->bts_department_name ?? 'N/A',
            'department_name' => $site->department_name ?? 'N/A',
            'project_capacity' => $site->project_capacity ?? 0,
            'ca_number' => $site->ca_number ?? 'N/A',
            'contact_no' => $site->contact_no ?? 'N/A',
            'ic_vendor_name' => $site->ic_vendor_name ?? 'N/A',
            'meter_number' => $site->meter_number ?? 'N/A',
            'net_meter_sr_no' => $site->net_meter_sr_no ?? 'N/A',
            'solar_meter_sr_no' => $site->solar_meter_sr_no ?? 'N/A',
            'site_survey_status' => $this->convertSiteSurveyStatus($site->site_survey_status ?? 'Pending'),
            'load_enhancement_status' => $this->convertLoadEnhancementStatus($site->load_enhancement_status ?? 'No'),
            'installation_status' => $this->convertInstallationStatus($site->installation_status ?? 'Pending'),
            'handover_status' => $this->convertHandoverStatus($site->handover_status ?? 'Pending'),
            'drawing_approval' => $this->convertYesNo($site->drawing_approval ?? 'No'),
            'inspection' => $this->convertYesNo($site->inspection ?? 'No'),
            'material_supplied' => $this->convertYesNo($site->material_supplied ?? 'No'),
            'structure_installation' => $this->convertYesNo($site->structure_installation ?? 'No'),
            'structure_foundation' => $this->convertYesNo($site->structure_foundation ?? 'No'),
            'pv_module_installation' => $this->convertYesNo($site->pv_module_installation ?? 'No'),
            'inverter_installation' => $this->convertYesNo($site->inverter_installation ?? 'No'),
            'dcdb_acdb_installaation' => $this->convertYesNo($site->dcdb_acdb_installaation ?? 'No'),
            'dc_cabelling' => $this->convertYesNo($site->dc_cabelling ?? 'No'),
            'ac_cabelling' => $this->convertYesNo($site->ac_cabelling ?? 'No'),
            'ac_cable_termination' => $this->convertYesNo($site->ac_cable_termination ?? 'No'),
            'dc_earthing' => $this->convertYesNo($site->dc_earthing ?? 'No'),
            'ac_earthing' => $this->convertYesNo($site->ac_earthing ?? 'No'),
            'lighntning_arrestor' => $this->convertYesNo($site->lighntning_arrestor ?? 'No'),
            'remote_monitoring_unit' => $this->convertYesNo($site->remote_monitoring_unit ?? 'No'),
            'fire_safety' => $this->convertYesNo($site->fire_safety ?? 'No'),
            'net_meter_registration' => $this->convertYesNo($site->net_meter_registration ?? 'No'),
            'meter_installaton_commission' => $this->convertYesNo($site->meter_installaton_commission ?? 'No'),
            'performance_guarantee_test' => $this->convertYesNo($site->performance_guarantee_test ?? 'No'),
            'material_inspection_date' => $this->formatDateField($site->material_inspection_date ?? null),
            'spp_installation_date' => $this->formatDateField($site->spp_installation_date ?? null),
            'commissioning_date' => $this->formatDateField($site->commissioning_date ?? null),
            'created_at' => $this->formatDateField($site->created_at ?? null, 'Y-m-d H:i:s'),
            'updated_at' => $this->formatDateField($site->updated_at ?? null, 'Y-m-d H:i:s'),
        ];
    }

    /**
     * Transform streetlight site data to human-readable format
     */
    public function transformStreetlightSite($streetlight): array
    {
        return [
            'id' => $streetlight->id,
            'state' => $streetlight->state ?? 'N/A',
            'district' => $streetlight->district ?? 'N/A',
            'block' => $streetlight->block ?? 'N/A',
            'panchayat' => $streetlight->panchayat ?? 'N/A',
            'ward' => $streetlight->ward ?? 'N/A',
            'mukhiya_contact' => $streetlight->mukhiya_contact ?? 'N/A',
            'total_poles' => $streetlight->total_poles ?? 0,
            'number_of_surveyed_poles' => $streetlight->number_of_surveyed_poles ?? 0,
            'number_of_installed_poles' => $streetlight->number_of_installed_poles ?? 0,
            'district_code' => $streetlight->district_code ?? 'N/A',
            'block_code' => $streetlight->block_code ?? 'N/A',
            'panchayat_code' => $streetlight->panchayat_code ?? 'N/A',
            'ward_type' => $streetlight->ward_type ?? 'N/A',
            'created_at' => $this->formatDateField($streetlight->created_at ?? null, 'Y-m-d H:i:s'),
            'updated_at' => $this->formatDateField($streetlight->updated_at ?? null, 'Y-m-d H:i:s'),
        ];
    }

    /**
     * Transform project data to human-readable format
     */
    public function transformProject($project): array
    {
        return [
            'id' => $project->id,
            'project_name' => $project->project_name ?? 'N/A',
            'project_type' => $this->convertProjectType($project->project_type ?? 0),
            'project_in_state' => $project->project_in_state ?? 'N/A',
            'agreement_number' => $project->agreement_number ?? 'N/A',
            'agreement_date' => $this->formatDateField($project->agreement_date ?? null),
            'work_order_number' => $project->work_order_number ?? 'N/A',
            'rate' => $project->rate ?? 0,
            'project_capacity' => $project->project_capacity ?? 0,
            'start_date' => $this->formatDateField($project->start_date ?? null),
            'end_date' => $this->formatDateField($project->end_date ?? null),
            'description' => $project->description ?? '',
            'total' => $project->total ?? 0,
            'created_at' => $this->formatDateField($project->created_at ?? null, 'Y-m-d H:i:s'),
            'updated_at' => $this->formatDateField($project->updated_at ?? null, 'Y-m-d H:i:s'),
        ];
    }

    /**
     * Convert user role to human-readable format
     */
    private function convertUserRole($role): string
    {
        $userRole = UserRole::tryFrom((int) $role);
        return $userRole ? $userRole->label() : (string) $role;
    }

    /**
     * Convert task status to human-readable format
     */
    private function convertTaskStatus($status): string
    {
        try {
            $taskStatus = TaskStatus::from($status);
            return $taskStatus->label();
        } catch (\ValueError $e) {
            return $status;
        }
    }

    /**
     * Convert project type to human-readable format
     */
    private function convertProjectType($projectType): string
    {
        $type = ProjectType::tryFrom((int) $projectType);
        return $type ? $type->label() : 'Unknown';
    }

    /**
     * Convert status field
     */
    private function convertStatus($status): string
    {
        return ucfirst(strtolower($status ?? 'active'));
    }

    /**
     * Convert site survey status
     */
    private function convertSiteSurveyStatus($status): string
    {
        return ucfirst(strtolower($status ?? 'Pending'));
    }

    /**
     * Convert load enhancement status
     */
    private function convertLoadEnhancementStatus($status): string
    {
        return ucfirst(strtolower($status ?? 'No'));
    }

    /**
     * Convert installation status
     */
    private function convertInstallationStatus($status): string
    {
        return ucfirst(strtolower($status ?? 'Pending'));
    }

    /**
     * Convert handover status
     */
    private function convertHandoverStatus($status): string
    {
        return ucfirst(strtolower($status ?? 'Pending'));
    }

    /**
     * Convert Yes/No enum values
     */
    private function convertYesNo($value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        $lower = strtolower($value ?? 'no');
        return in_array($lower, ['yes', 'y', '1', 'true']) ? 'Yes' : 'No';
    }

    /**
     * Format date field (handles both Carbon instances and string dates)
     */
    public function formatDateField($date, $format = 'Y-m-d'): string
    {
        if (empty($date)) {
            return '';
        }

        if (is_string($date)) {
            try {
                return \Carbon\Carbon::parse($date)->format($format);
            } catch (\Exception $e) {
                return $date; // Return as-is if parsing fails
            }
        }

        if ($date instanceof \Carbon\Carbon) {
            return $date->format($format);
        }

        return '';
    }
}
