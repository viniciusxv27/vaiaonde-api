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
        
        if (empty($this->apiKey)) {
            Log::error('AbacatePay API Key não configurada - verifique ABACATEPAY_API_KEY no .env');
        }
    }

    /**
     * Cria um QR Code PIX
     */
    public function createPixQrCode($amount, $description, $customer, $metadata = [])
    {
        try {
            // Verificar se a API key está configurada
            if (empty($this->apiKey)) {
                Log::error('Tentativa de criar PIX sem API Key configurada');
                return [
                    'success' => false,
                    'error' => 'Serviço de pagamento não configurado. Entre em contato com o suporte.'
                ];
            }
            
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
                'body' => $response->body(),
                'data' => $responseData
            ]);

            // Verificar se houve erro HTTP 500
            if ($response->status() >= 500) {
                Log::error('AbacatePay servidor retornou erro 500', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Serviço de pagamento temporariamente indisponível. Tente novamente em alguns instantes.'
                ];
            }

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
                
                Log::error('AbacatePay retornou erro', [
                    'error' => $error,
                    'error_type' => gettype($error),
                    'full_response' => $responseData
                ]);
                
                if (is_string($error)) {
                    if (strpos($error, 'taxId') !== false || strpos($error, 'CPF') !== false) {
                        $errorMessage = 'CPF inválido. Por favor, atualize seu CPF no perfil antes de fazer depósito via PIX.';
                    } elseif (strpos($error, 'cellphone') !== false || strpos($error, 'phone') !== false) {
                        $errorMessage = 'Telefone inválido. Por favor, atualize seu telefone no perfil.';
                    } elseif (strpos($error, 'email') !== false) {
                        $errorMessage = 'Email inválido. Por favor, atualize seu email no perfil.';
                    } elseif (strpos($error, 'name') !== false) {
                        $errorMessage = 'Nome inválido. Por favor, atualize seu nome no perfil.';
                    } else {
                        $errorMessage .= ': ' . $error;
                    }
                } elseif (is_array($error)) {
                    // Se error é um array, pegar a primeira mensagem
                    $firstError = is_array($error) ? reset($error) : $error;
                    $errorMessage .= ': ' . (is_string($firstError) ? $firstError : json_encode($firstError));
                }
            } elseif ($response->status() >= 400) {
                // Erro HTTP sem mensagem específica
                $errorMessage .= ' (HTTP ' . $response->status() . ')';
                
                if ($response->status() === 401) {
                    $errorMessage = 'Erro de autenticação com o serviço de pagamento. Entre em contato com o suporte.';
                } elseif ($response->status() === 422) {
                    $errorMessage = 'Dados inválidos para gerar PIX. Verifique seus dados cadastrais (CPF, telefone, email).';
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
