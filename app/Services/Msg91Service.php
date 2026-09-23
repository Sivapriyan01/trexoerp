<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Msg91Service
{
    protected string $baseUrl = 'https://control.msg91.com/api/v5';
    protected ?string $authKey;
    protected ?string $templateId;

    public function __construct()
    {
        // Read from tenant Setting first, fall back to config / env
        $this->authKey = Setting::get('msg91_auth_key', config('services.msg91.auth_key', env('MSG91_AUTH_KEY', '')));
        $this->templateId = Setting::get('msg91_template_id', config('services.msg91.template_id', env('MSG91_TEMPLATE_ID', '')));
    }

    /**
     * Check if MSG91 is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->authKey);
    }

    /**
     * Format Indian/International phone number for MSG91
     * Strips non-digits, prepends 91 if standard 10-digit Indian mobile.
     */
    public function formatPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) === 11 && str_starts_with($clean, '0')) {
            $clean = substr($clean, 1);
        }
        if (strlen($clean) === 10) {
            return '91' . $clean;
        }
        return $clean;
    }

    /**
     * Send OTP via MSG91 API
     *
     * @param string $mobile Customer phone number
     * @param string|null $otp Optional custom OTP code (if omitted, MSG91 generates one)
     * @param int $expiryMinutes OTP expiry time in minutes (default: 10)
     * @param int $otpLength Length of OTP (default: 4)
     * @return array
     */
    public function sendOtp(string $mobile, ?string $otp = null, int $expiryMinutes = 10, int $otpLength = 4): array
    {
        if (empty($this->authKey)) {
            return [
                'success' => false,
                'message' => 'MSG91 Auth Key is missing. Please set MSG91_AUTH_KEY in your configuration.',
            ];
        }

        $phone = $this->formatPhone($mobile);

        $payload = [
            'mobile'      => $phone,
            'otp_expiry'  => $expiryMinutes,
            'otp_length'  => $otpLength,
        ];

        // Only attach template_id if it is a real DLT template, not a 24-character Widget ID
        if (!empty($this->templateId) && strlen($this->templateId) !== 24 && $this->templateId !== '36697164476b323432353839') {
            $payload['template_id'] = $this->templateId;
        }

        if ($otp !== null && $otp !== '') {
            $payload['otp'] = $otp;
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'authkey'      => $this->authKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->timeout(15)->post($this->baseUrl . '/otp?' . http_build_query($payload));

            $body = $response->json() ?? [];

            Log::info('MSG91 Send OTP response', [
                'mobile' => $phone,
                'status' => $response->status(),
                'body'   => $body,
            ]);

            $isSuccess = $response->successful() && (($body['type'] ?? '') === 'success' || strtolower($body['message'] ?? '') === 'otp sent successfully');

            return [
                'success' => $isSuccess,
                'message' => $body['message'] ?? ($isSuccess ? 'OTP sent successfully via SMS' : 'Failed to send OTP via MSG91'),
                'data'    => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('MSG91 Send OTP Exception: ' . $e->getMessage(), [
                'mobile' => $phone,
            ]);

            return [
                'success' => false,
                'message' => 'MSG91 service error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Resend OTP via Text SMS or Voice call
     *
     * @param string $mobile Customer phone number
     * @param string $retryType 'text' or 'voice'
     * @return array
     */
    public function resendOtp(string $mobile, string $retryType = 'text'): array
    {
        if (empty($this->authKey)) {
            return ['success' => false, 'message' => 'MSG91 Auth Key is missing.'];
        }

        $phone = $this->formatPhone($mobile);

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'authkey' => $this->authKey,
                'Accept'  => 'application/json',
            ])->timeout(15)->get($this->baseUrl . '/otp/retry', [
                'mobile'    => $phone,
                'retrytype' => $retryType,
            ]);

            $body = $response->json() ?? [];
            $isSuccess = $response->successful() && (($body['type'] ?? '') === 'success');

            return [
                'success' => $isSuccess,
                'message' => $body['message'] ?? ($isSuccess ? 'OTP resent successfully.' : 'Failed to resend OTP.'),
                'data'    => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('MSG91 Resend OTP Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify OTP against MSG91 (Used when MSG91 auto-generated the OTP)
     *
     * @param string $mobile Customer phone number
     * @param string $otp Code entered by user
     * @return array
     */
    public function verifyOtp(string $mobile, string $otp): array
    {
        if (empty($this->authKey)) {
            return ['success' => false, 'message' => 'MSG91 Auth Key is missing.'];
        }

        $phone = $this->formatPhone($mobile);

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'authkey' => $this->authKey,
                'Accept'  => 'application/json',
            ])->timeout(15)->get($this->baseUrl . '/otp/verify', [
                'mobile' => $phone,
                'otp'    => $otp,
            ]);

            $body = $response->json() ?? [];
            $isSuccess = $response->successful() && (($body['type'] ?? '') === 'success');

            return [
                'success' => $isSuccess,
                'message' => $body['message'] ?? ($isSuccess ? 'OTP verified successfully.' : 'Invalid or expired OTP.'),
                'data'    => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('MSG91 Verify OTP Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
