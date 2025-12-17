<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsappHelper
{
    /**
     * Send a WhatsApp message using the "create_meet_link" template.
     *
     * NOTE: This is used by the Meetings module and kept unchanged.
     */
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
                $data['title'] ?? 'Meeting',
                $data['meet_date'] ?? '03-06-2025',
                $data['meet_time'] ?? '16:23',
                $data['platform'] ?? 'Google Meet',
                $data['meet_link'] ?? 'meet.google.com',
                $data['agenda'] ?? 'Agenda of meeting',
                $data['type'] ?? 'Review'
            ],
            "source"              => "new-landing-page form",
            "media"               => [],
            "buttons"             => [],
            "carouselCards"       => [],
            "location"            => [],
            "attributes"          => [],
            "paramsFallbackValue" => ["FirstName" => "user"],
        ];
        Log::info($apiUrl);
        Log::info($payload);
        $response = Http::post($apiUrl, $payload);
        if (!$response->successful()) {
            throw new \Exception("Failed to send meeting message. Error: " . $response->body());
        }

        return $response;
    }

    /**
     * Send a WhatsApp OTP for mobile number verification.
     *
     * This follows the same API pattern as sendMeetLink but uses a different
     * campaign/template that should be configured on the WhatsApp provider side.
     *
     * Expected template:
     *  - One template param: the numeric OTP
     */
    public static function sendMobileChangeOtp(string $mobile, string $otp, ?string $userName = null)
    {
        $apiUrl = env('WHATSAPP_API_URL');
        $apiKey = env('WHATSAPP_API_KEY');
        $campaignName = env('WHATSAPP_MOBILE_OTP_CAMPAIGN', 'mobile_change_otp');

        if (!$apiUrl || !$apiKey) {
            throw new \Exception('WhatsApp API configuration is missing (WHATSAPP_API_URL / WHATSAPP_API_KEY).');
        }

        // Default name fallback
        $userName = $userName ?: 'User';

        $payload = [
            "apiKey"              => $apiKey,
            "campaignName"        => $campaignName,
            "destination"         => "91{$mobile}",
            "userName"            => $userName,
            "templateParams"      => [
                $otp,
            ],
            "source"              => "laravel-crm profile mobile change",
            "media"               => [],
            "buttons"             => [],
            "carouselCards"       => [],
            "location"            => [],
            "attributes"          => [],
            "paramsFallbackValue" => ["OTP" => $otp],
        ];

        Log::info('Sending WhatsApp mobile change OTP', [
            'url'      => $apiUrl,
            'mobile'   => $mobile,
            'campaign' => $campaignName,
        ]);

        $response = Http::post($apiUrl, $payload);

        if (!$response->successful()) {
            throw new \Exception("Failed to send OTP message. Error: " . $response->body());
        }

        return $response;
    }
}
