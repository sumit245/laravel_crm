<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\Pole;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\InventroyStreetLightModel;
use App\Models\InventoryDispatch;

class StreetlightPoleImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $missingItems = [];
        foreach ($rows as $row) {
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

            $pole = Pole::where('complete_pole_number', $row['complete_pole_number'])->first();

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
                'updated_at' =>  Carbon::parse($row['date_of_installation']),
            ];
            $creatingNewPole = false; // Track if we're creating a new pole

            if ($pole) {
                $pole->update($poleData);
            } else {
                foreach (['battery_qr', 'panel_qr', 'luminary_qr'] as $item) {
                    $dispatch = InventoryDispatch::where('serial_number', (string)$row[$item])
                        ->whereNull('streetlight_pole_id')
                        ->where('is_consumed', 0)
                        ->first();

                    if (!$dispatch) {
                        $missingItems[] = "Material '{$item}' with serial '{$row[$item]}' not yet dispatched to vendor";
                    }
                }

                $creatingNewPole = true;
                $poleData['complete_pole_number'] = $row['complete_pole_number'];
                Pole::create($poleData);
            }

            // Update inventory dispatch **only if new pole created**
            if ($creatingNewPole) {
                $latestPoleId = Pole::where('complete_pole_number', $row['complete_pole_number'])->value('id');
                foreach (['battery_qr', 'panel_qr', 'luminary_qr'] as $item) {
                    InventoryDispatch::where('serial_number', (string)$row[$item])
                        ->whereNull('streetlight_pole_id')
                        ->where('is_consumed', 0)
                        ->update([
                            'streetlight_pole_id' => $latestPoleId,
                            'is_consumed' => 1,
                            'total_quantity' => 0,
                            'updated_at' => Carbon::now()
                        ]);
                }
                $streetlight->increment('number_of_surveyed_poles');
                $streetlight->increment('number_of_installed_poles');
            }
        }
        if (!empty($missingItems)) {
            throw new \Exception("The following items are missing: " . implode(", ", $missingItems));
        }
    }
}
