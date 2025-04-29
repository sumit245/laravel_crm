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

            foreach (['battery_qr', 'panel_qr', 'luminary_qr'] as $item) {
                $dispatch = InventoryDispatch::where('serial_number', $row[$item])
                    ->whereNull('streetlight_pole_id')
                    ->where('is_consumed', 0)
                    ->first();

                if (!$dispatch) {
                    throw new \Exception("Material '{$item}' with serial '{$row[$item]}' not yet dispatched to vendor");
                }
            }

            $pole = Pole::where('complete_pole_number', $row['complete_pole_number'])->first();

            $poleData = [
                'battery_qr' => $row['battery_qr'],
                'panel_qr' => $row['panel_qr'],
                'luminary_qr' => $row['luminary_qr'],
                'sim_number' => $row['sim_number'],
                'ward_name' => $row['ward_name'],
                'isInstallationDone' => true,
                'updated_at' =>  Carbon::createFromFormat('d/m/y', $row['date_of_installation']),
                'task_id' => $task->id,
                'site_id' => $streetlight->id,
            ];

            if ($pole) {
                $pole->update($poleData);
            } else {
                $poleData['complete_pole_number'] = $row['complete_pole_number'];
                $poleData['battery_qr'] = $row['battery_qr'];
                $poleData['panel_qr'] = $row['panel_qr'];
                $poleData['luminary_qr'] = $row['luminary_qr'];
                $poleData['sim_number'] = $row['sim_number'];
                $poleData['ward_name'] = $row['ward_name'];
                $poleData['isInstallationDone'] = true;
                $poleData['updated_at'] =  Carbon::createFromFormat('d/m/y', $row['date_of_installation']);
                $poleData['task_id'] = $task->id;
                $poleData['site_id'] = $streetlight->id;
                Pole::create($poleData);
            }

            // Set pole_id in inventory dispatch
            foreach (['battery_qr', 'panel_qr', 'luminary_qr'] as  $item) {
                InventoryDispatch::where('serial_number', (string)$row[$item])
                    ->whereNull('streetlight_pole_id')
                    ->where('is_consumed', 0)
                    ->update([
                        'streetlight_pole_id' => $pole ? $pole->id : Pole::latest()->first()->id,
                        'is_consumed' => 1,
                        'updated_at' => Carbon::now()

                    ]);
            }

            $streetlight->increment('number_of_installed_poles');
        }
    }
}
