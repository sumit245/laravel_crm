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
       Log::info( basename($pole->complete_pole_number));
        $payload = [
            'devId' => $pole->luminary_qr,
            'MfId' => "4",
            'poleName' => basename($pole->complete_pole_number),
            "project" => "SUGS", //TODO: Get project from .env
            'district' => $streetlight->district,
            'districtCode' => (string) $districtCode,
            'block' => $streetlight->block,
            'blockCode' => $streetlight->block_code,
            'panchayat' => $streetlight->panchayat,
            'panchayatCode' => $streetlight->panchayat_code,
            'ward_type' => $streetlight->ward_type ?? 'W',
            'ward_number' => preg_replace('/\D/' , '' ,$pole->ward_name),
            'BattSno' => $pole->battery_qr,
            'PvSno' => $pole->panel_qr,
            'simNo' => $pole->sim_number,
            'lat' => $pole->lat,
            'lng' => $pole->lng,
            'remark' => $pole->remarks,
            'updated_by' => $approver,
            'dateTime' => now()->format('Y-m-d H:i:s'),
        ];
        Log::info($payload);
        try {
            // $response = Http::asForm()->post($url, $payload);
            // Log::info($response->json());
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
