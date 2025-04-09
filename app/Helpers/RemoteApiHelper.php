<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemoteApiHelper
{
    public static function sendPoleDataToRemoteServer($pole, $streetlight, $approver)
    {
        $url = 'https://ssl.slldm.com/insertMasterData.php';
        // TODO: Read URL from env
        $payload = [
            'poleName' => $pole->complete_pole_number,
            'ward' => $pole->ward_name,
            'panchayat' => $streetlight->panchayat,
            'block' => $streetlight->block,
            'district' => $streetlight->district,
            'devId' => $pole->luminary_qr,
            'BattSno' => $pole->battery_qr,
            'PvSno' => $pole->panel_qr,
            'lat' => $pole->lat,
            'lng' => $pole->lng,
            'file' => '',
            "project" => "test", //TODO: Get project from .env
            'remark' => $pole->remarks,
            'updated_by' => $approver,
            'MfId' => 101,
            'dateTime' => now()->format('Y-m-d H:i:s'),
        ];
        Log::info($payload);
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
