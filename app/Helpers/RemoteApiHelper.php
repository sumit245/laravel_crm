<?php

namespace App\Helpers;

use App\Models\DistrictCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemoteApiHelper
{
    public static function sendPoleDataToRemoteServer($pole, $streetlight, $approver)
    {
        $url = 'https://ssl.slldm.com/insertMasterData.php';
        // TODO: Read URL from env
        $districtCode = DistrictCode::where('district_name', strtoupper(trim($streetlight->district)))->value('district_code');
        $payload = [
            'poleName' => $pole->complete_pole_number,
            'ward' => $pole->ward_name,
            'panchayat' => $streetlight->panchayat,
            'block' => $streetlight->block,
            'district' => $streetlight->district,
            'districtCode' => $districtCode,
            'devId' => $pole->luminary_qr,
            'BattSno' => $pole->battery_qr,
            'PvSno' => $pole->panel_qr,
            'lat' => $pole->lat,
            'lng' => $pole->lng,
            'file' => '',
            "project" => "BREDASSL", //TODO: Get project from .env
            'remark' => $pole->remarks,
            'updated_by' => $approver,
            'MfId' => 101,
            'dateTime' => now()->format('Y-m-d H:i:s'),
        ];
        try {
            $response = Http::asForm()->post($url, $payload);
            Log::info($response->json());
            // if ($response->successful()) {
            //     Log::info("Remote API success", ['response' => $response->body()]);
            // } else {
            //     Log::error("Remote API failed", ['status' => $response->status(), 'body' => $response->body()]);
            // }
        } catch (\Exception $e) {
            Log::error("Remote API exception", ['error' => $e->getMessage()]);
        }
    }
}
