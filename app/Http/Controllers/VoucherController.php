<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Models\User;
use App\Services\KirvanoService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class VoucherController extends Controller
{
    protected $kirvanoService;

    public function __construct(KirvanoService $kirvanoService)
    {
        $this->kirvanoService = $kirvanoService;
    }

    /**
     * Lista vouchers disponíveis para um estabelecimento
     */
    public function list(Request $request, $placeId)
    {
        $token = $request->bearerToken();
        $user = null;

        if ($token) {
            try {
                $payload = JWTAuth::setToken($token)->getPayload();
                if ($payload && $payload->get('sub')) {
                    $user = User::find($payload->get('sub'));
                }
            } catch (\Exception $e) {
                // Token inválido, continua como anônimo
            }
        }

        // Busca vouchers ativos e válidos para o estabelecimento
        $vouchers = Voucher::where('place_id', $placeId)
            ->where('active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->where(function ($query) {
                $query->whereNull('max_uses')
                    ->orWhereRaw('uses_count < max_uses');
            })
            ->get();

        // Filtra vouchers exclusivos do clube se usuário não for membro
        if (!$user || !$user->subscription) {
            $vouchers = $vouchers->filter(function ($voucher) {
                return !$voucher->club_exclusive;
            });
        }

        // Verifica quais vouchers o usuário já usou
        if ($user) {
            $usedVoucherIds = UserVoucher::where('user_id', $user->id)
                ->where('used', true)
                ->pluck('voucher_id')
                ->toArray();

            $vouchers = $vouchers->map(function ($voucher) use ($usedVoucherIds) {
                $voucher->already_used = in_array($voucher->id, $usedVoucherIds);
                return $voucher;
            });
        }

        return response()->json([
            'success' => true,
            'vouchers' => $vouchers->values(),
        ]);
    }
    
    /**
     * Usa/resgata um voucher
     */
    public function use(Request $request, $voucherId)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token de autorização ausente'], 401);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            if (!$payload || !$payload->get('sub')) {
                return response()->json(['error' => 'Token de autorização inválido'], 401);
            }

            $userId = $payload->get('sub');
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado'], 404);
            }

            $voucher = Voucher::with('place')->find($voucherId);

            if (!$voucher) {
                return response()->json(['error' => 'Voucher não encontrado'], 404);
            }

            // Verifica se o voucher pode ser usado
            if (!$voucher->canBeUsedBy($user)) {
                if ($voucher->club_exclusive && !$user->subscription) {
                    return response()->json([
                        'error' => 'Este voucher é exclusivo para membros do clube'
                    ], 403);
                }
                return response()->json(['error' => 'Voucher não disponível'], 400);
            }

            // Verifica se o usuário já usou este voucher
            $alreadyUsed = UserVoucher::where('user_id', $user->id)
                ->where('voucher_id', $voucher->id)
                ->where('used', true)
                ->exists();

            if ($alreadyUsed) {
                return response()->json(['error' => 'Você já utilizou este voucher'], 400);
            }

            // Integração com Kirvano (se configurado)
            $kirvanoTransactionId = null;
            if (env('KIRVANO_ENABLED', false)) {
                $kirvanoResult = $this->kirvanoService->redeemVoucher(
                    $voucher->code,
                    $user->id,
                    $voucher->place_id
                );

                if (!$kirvanoResult['success']) {
                    return response()->json([
                        'error' => $kirvanoResult['message']
                    ], 400);
                }

                $kirvanoTransactionId = $kirvanoResult['transaction_id'];
            }

            // Registra o uso do voucher
            $userVoucher = UserVoucher::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'voucher_id' => $voucher->id,
                ],
                [
                    'used' => true,
                    'used_at' => now(),
                    'kirvano_transaction_id' => $kirvanoTransactionId,
                ]
            );

            // Incrementa contador de usos
            $voucher->increment('uses_count');

            // Atualiza economia do usuário
            if ($voucher->discount_type === 'fixed' && $voucher->discount_value) {
                $user->increment('economy', $voucher->discount_value);
            }

            // Adiciona pontos ao usuário
            $user->increment('score', 10);

            return response()->json([
                'success' => true,
                'message' => 'Voucher resgatado com sucesso!',
                'voucher' => $voucher,
                'place' => $voucher->place,
                'transaction_id' => $kirvanoTransactionId,
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao processar voucher',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
