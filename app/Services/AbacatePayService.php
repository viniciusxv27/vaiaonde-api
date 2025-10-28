<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AbacatePayService
{
    private $apiKey;
    private $baseUrl = 'https://api.abacatepay.com/v1';

    public function __construct()
    {
        $this->apiKey = env('ABACATEPAY_API_KEY');
    }

    /**
     * Cria um QR Code PIX
     */
    public function createPixQrCode($amount, $description, $customer, $metadata = [])
    {
        try {
            // Preparar dados do customer (remover campos vazios ou inválidos)
            $customerData = [];
            
            if (!empty($customer['name'])) {
                $customerData['name'] = $customer['name'];
            }
            
            if (!empty($customer['email'])) {
                $customerData['email'] = $customer['email'];
            }
            
            if (!empty($customer['cellphone'])) {
                // Limpar telefone (remover caracteres especiais)
                $phone = preg_replace('/[^0-9]/', '', $customer['cellphone']);
                if (strlen($phone) >= 10) {
                    $customerData['cellphone'] = $customer['cellphone'];
                }
            }
            
            // Validar e adicionar CPF apenas se válido
            if (!empty($customer['taxId'])) {
                $cpf = preg_replace('/[^0-9]/', '', $customer['taxId']);
                // CPF deve ter 11 dígitos
                if (strlen($cpf) === 11) {
                    $customerData['taxId'] = $cpf;
                }
            }

            $payload = [
                'amount' => (int) ($amount * 100), // Converter para centavos
                'expiresIn' => 3600, // 1 hora em segundos
                'description' => $description,
                'customer' => $customerData,
                'metadata' => $metadata,
            ];

            Log::info('AbacatePay Request', ['payload' => $payload]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/pixQrCode/create', $payload);

            $responseData = $response->json();
            
            Log::info('AbacatePay Response', [
                'status' => $response->status(),
                'data' => $responseData
            ]);

            if ($response->successful() && isset($responseData['data']) && empty($responseData['error'])) {
                $data = $responseData['data'];
                
                return [
                    'success' => true,
                    'id' => $data['id'],
                    'qr_code' => $data['brCode'] ?? null, // Código copia e cola
                    'qr_code_url' => $data['brCodeBase64'] ?? null, // Imagem base64
                    'amount' => $data['amount'] / 100, // Converter de centavos
                    'status' => $data['status'] ?? 'PENDING',
                    'expires_at' => $data['expiresAt'] ?? null,
                ];
            }

            // Tratamento de erro
            $errorMessage = 'Erro ao gerar QR Code PIX';
            
            if (isset($responseData['error'])) {
                $error = $responseData['error'];
                
                if (is_string($error)) {
                    if (strpos($error, 'taxId') !== false) {
                        $errorMessage = 'CPF inválido. Por favor, atualize seu CPF no perfil.';
                    } elseif (strpos($error, 'cellphone') !== false) {
                        $errorMessage = 'Telefone inválido. Por favor, atualize seu telefone no perfil.';
                    } elseif (strpos($error, 'email') !== false) {
                        $errorMessage = 'Email inválido. Por favor, atualize seu email no perfil.';
                    } else {
                        $errorMessage .= ': ' . $error;
                    }
                }
            }

            Log::error('AbacatePay erro ao criar QR Code', [
                'status' => $response->status(),
                'response' => $responseData,
                'customer_data' => $customerData
            ]);

            return [
                'success' => false,
                'error' => $errorMessage
            ];

        } catch (\Exception $e) {
            Log::error('AbacatePay exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Erro ao processar pagamento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verifica o status de um PIX
     */
    public function checkPixQrCode($pixId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/pixQrCode/check', [
                'id' => $pixId
            ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['data']) && empty($responseData['error'])) {
                $data = $responseData['data'];
                
                return [
                    'success' => true,
                    'status' => $data['status'],
                    'paid' => $data['status'] === 'PAID',
                    'expires_at' => $data['expiresAt'] ?? null,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['error'] ?? 'Erro ao verificar status'
            ];

        } catch (\Exception $e) {
            Log::error('AbacatePay check exception', [
                'error' => $e->getMessage(),
                'pix_id' => $pixId
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
