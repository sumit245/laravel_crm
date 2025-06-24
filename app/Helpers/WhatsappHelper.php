<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsappHelper
{
    public static function sendMeetLink($mobile, $userName = "Guest", $data)
    {
        $apiUrl = env('WHATSAPP_API_URL');
        $apiKey = env('WHATSAPP_API_KEY');

        $payload = [
            "apiKey"              => $apiKey,
            "campaignName"        => "create_meet_link",
            "destination"         => "91{$mobile}",
            "userName"            => $userName,
            "templateParams"      => [
                $data['firstName'] ?? 'John',
                $data['lastName'] ?? 'Doe',
                $data['type'] ?? 'Review',
                $data['title'] ?? 'Meeting',
                $data['meet_date'] ?? '03-06-2025',
                $data['meet_time'] ?? '16:23',
                $data['platform'] ?? 'Google Meet',
                $data['meet_link'] ?? 'meet.google.com',
                $data['agenda'] ?? 'Agenda of meeting'
            ],
            "source"              => "new-landing-page form",
            "media"               => [],
            "buttons"             => [[
                "type"       => "button",
                "sub_type"   => "url",
                "index"      => 0,
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => 'Agree', // Replace with dynamic or fixed value if needed
                    ],
                ],
            ]],
            "carouselCards"       => [],
            "location"            => [],
            "paramsFallbackValue" => ["FirstName" => "user"],
        ];
        Log::info($apiUrl);
        Log::info($payload);
        $response = Http::post($apiUrl, $payload);
        if ($response->successful()) {
            return $response; // Return OTP if the API call succeeds
        } else {
            throw new \Exception("Failed to send message. Error: " . $response->body());
        }
    }
}
