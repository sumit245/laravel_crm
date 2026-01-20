<?php

namespace App\Helpers;

use App\Models\DistrictCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemoteApiHelper
{
    public static function sendPoleDataToRemoteServer($pole, $streetlight, $approver)
    {
        $url = env('RMS_API_URL', 'https://ssl.slldm.com/insertMasterData.php');
        $districtCode = DistrictCode::where('district_name', strtoupper(trim($streetlight->district)))->value('district_code');
        $wardType = ($pole->ward_name ==="GP") ? 'GP' : 'W';
        $poleName = $wardType === 'GP' 
            ? (($digits = preg_replace('/\D/', '', $pole->complete_pole_number)) && strlen($digits) >= 2 
                ? substr($digits, -2) 
                : substr($pole->complete_pole_number, strrpos($pole->complete_pole_number, '/') + 1))
            : substr($pole->complete_pole_number, strrpos($pole->complete_pole_number, '/') + 1);
        $payload = [
            'devId' => $pole->luminary_qr,
            'MfId' => "4",
            'poleName' => $poleName,
            'project'=>"SUGS",
            'district' => $streetlight->district,
            'districtCode' => (string) $districtCode,
            'block' => $streetlight->block,
            'blockCode' => $streetlight->block_code,
            'panchayat' => $streetlight->panchayat,
            'panchayatCode' => $streetlight->panchayat_code,
            'ward_type' => $wardType,
            'ward_number' => $wardType==='GP'?null:$pole->ward_name,
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
            $response = Http::asForm()->post($url, $payload);
            Log::info($response->json());
            if ($response->successful()) {
               Log::info("Remote API success", ['response' => $response->body()]);
            } else {
               Log::error("Remote API failed", ['status' => $response->status(), 'body' => $response->body()]);
            }
            return $response;
        } catch (\Exception $e) {
            Log::error("Remote API exception", ['error' => $e->getMessage()]);
        }
    }
}
