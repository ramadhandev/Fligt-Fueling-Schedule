<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        // Gunakan service WhatsApp API seperti WhatsApp Business API, Twilio, atau service lain
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->apiKey = config('services.whatsapp.api_key');
    }

    public function sendMessage($phoneNumber, $message)
    {
        try {
            // Format nomor telepon (hapus karakter selain angka)
            $formattedPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // Jika menggunakan WhatsApp Business API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'phone' => $formattedPhone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'to' => $formattedPhone,
                    'message' => $message
                ]);
                return true;
            } else {
                Log::error('WhatsApp API error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp service error: ' . $e->getMessage());
            return false;
        }
    }

    // Alternative: Using Twilio WhatsApp
    public function sendViaTwilio($phoneNumber, $message)
    {
        // Implementasi Twilio WhatsApp jika menggunakan Twilio
        // $twilio = new Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        
        // try {
        //     $twilio->messages->create(
        //         "whatsapp:+" . $phoneNumber,
        //         [
        //             'from' => "whatsapp:" . env('TWILIO_WHATSAPP_FROM'),
        //             'body' => $message
        //         ]
        //     );
        //     return true;
        // } catch (\Exception $e) {
        //     Log::error('Twilio WhatsApp error: ' . $e->getMessage());
        //     return false;
        // }
    }
}