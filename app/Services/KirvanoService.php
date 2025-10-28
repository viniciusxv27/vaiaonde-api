<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KirvanoService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = env('KIRVANO_API_URL', 'https://api.kirvano.com');
        $this->apiKey = env('KIRVANO_API_KEY');
    }

    /**
     * Valida um voucher com a API Kirvano
     */
    public function validateVoucher($voucherCode, $userId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/vouchers/validate', [
                'code' => $voucherCode,
                'user_id' => $userId,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Erro ao validar voucher',
            ];
        } catch (\Exception $e) {
            Log::error('Kirvano API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro de comunicação com o serviço de vouchers',
            ];
        }
    }

    /**
     * Registra o uso de um voucher na API Kirvano
     */
    public function redeemVoucher($voucherCode, $userId, $placeId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/vouchers/redeem', [
                'code' => $voucherCode,
                'user_id' => $userId,
                'place_id' => $placeId,
                'redeemed_at' => now()->toIso8601String(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Erro ao resgatar voucher',
            ];
        } catch (\Exception $e) {
            Log::error('Kirvano Redeem Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro de comunicação com o serviço de vouchers',
            ];
        }
    }

    /**
     * Sincroniza vouchers com a API Kirvano
     */
    public function syncVouchers()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->apiUrl . '/vouchers');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'vouchers' => $response->json()['data'] ?? [],
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao sincronizar vouchers',
            ];
        } catch (\Exception $e) {
            Log::error('Kirvano Sync Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro de comunicação com o serviço de vouchers',
            ];
        }
    }
}
