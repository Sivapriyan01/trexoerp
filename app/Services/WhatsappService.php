<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected $baseUrl = 'https://whatsapp.zerodeveloper.net/api/create-message';
    protected $appKey;
    protected $authKey;

    public function __construct()
    {
        // Read from tenant Setup settings first (wa_access_token / wa_auth_key),
        // fall back to .env config values if not set.
        $this->appKey  = Setting::get('wa_access_token', config('services.whatsapp.app_key', ''));
        $this->authKey = Setting::get('wa_auth_key',     config('services.whatsapp.auth_key', ''));
    }

    public function sendMessage($to, $message)
    {
        $phone = preg_replace('/[^0-9]/', '', $to);
        if (strlen($phone) == 10) $phone = "91" . $phone;

        try {
            $response = Http::asForm()->withoutVerifying()->timeout(60)->post($this->baseUrl, [
                'appkey' => $this->appKey,
                'authkey' => $this->authKey,
                'to' => $phone,
                'message' => $message,
                'sandbox' => 'false'
            ]);

            Log::info('WhatsApp API Request:', [
                'to' => $phone,
                'appkey' => $this->appKey,
                'status' => $response->status()
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('WhatsApp API Error: ' . $response->body());
            return [
                'success' => false,
                'message' => 'API Error: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp Connection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Could not connect to WhatsApp API'
            ];
        }
    }
}
