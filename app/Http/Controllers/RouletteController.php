<?php

namespace App\Http\Controllers;

use App\Models\RoulettePrize;
use App\Models\RoulettePlay;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RouletteController extends Controller
{
    /**
     * Lista prêmios disponíveis na roleta
     */
    public function prizes(Request $request)
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

        // Busca prêmios ativos e disponíveis
        $prizes = RoulettePrize::where('active', true)
            ->where(function ($query) {
                $query->whereNull('quantity')
                    ->orWhereRaw('quantity_used < quantity');
            })
            ->get();

        // Filtra prêmios exclusivos do clube se usuário não for membro
        if (!$user || !$user->subscription) {
            $prizes = $prizes->filter(function ($prize) {
                return !$prize->club_exclusive;
            });
        }

        // Remove dados sensíveis
        $prizes = $prizes->map(function ($prize) {
            return [
                'id' => $prize->id,
                'name' => $prize->name,
                'description' => $prize->description,
                'type' => $prize->type,
                'image_url' => $prize->image_url,
                'color' => $prize->color,
                'club_exclusive' => $prize->club_exclusive,
            ];
        });

        return response()->json([
            'success' => true,
            'prizes' => $prizes->values(),
            'user_spins' => $user ? $user->roulette_spins_available : 0,
            'can_get_daily_spin' => $user ? $user->canGetDailySpin() : false,
        ]);
    }

    /**
     * Gira a roleta e sorteia um prêmio
     */
    public function spin(Request $request)
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

            // Verifica se usuário tem giros disponíveis
            if (!$user->canSpinRoulette()) {
                return response()->json([
                    'error' => 'Você não possui giros disponíveis',
                    'can_get_daily_spin' => $user->canGetDailySpin(),
                ], 400);
            }

            // Busca prêmios disponíveis
            $prizes = RoulettePrize::where('active', true)
                ->where(function ($query) {
                    $query->whereNull('quantity')
                        ->orWhereRaw('quantity_used < quantity');
                })
                ->get()
                ->filter(function ($prize) use ($user) {
                    return $prize->canBeWonBy($user);
                });

            if ($prizes->isEmpty()) {
                return response()->json(['error' => 'Nenhum prêmio disponível no momento'], 400);
            }

            // Sorteia prêmio baseado em probabilidade
            $wonPrize = $this->selectPrizeByProbability($prizes);

            // Registra a jogada
            $play = RoulettePlay::create([
                'user_id' => $user->id,
                'prize_id' => $wonPrize->id,
                'claimed' => false,
            ]);

            // Decrementa giros disponíveis
            $user->decrement('roulette_spins_available');

            // Incrementa contador de uso do prêmio
            $wonPrize->increment('quantity_used');

            return response()->json([
                'success' => true,
                'message' => 'Parabéns! Você ganhou!',
                'play_id' => $play->id,
                'prize' => [
                    'id' => $wonPrize->id,
                    'name' => $wonPrize->name,
                    'description' => $wonPrize->description,
                    'type' => $wonPrize->type,
                    'prize_value' => $wonPrize->prize_value,
                    'points_value' => $wonPrize->points_value,
                    'discount_value' => $wonPrize->discount_value,
                    'image_url' => $wonPrize->image_url,
                    'color' => $wonPrize->color,
                ],
                'spins_remaining' => $user->roulette_spins_available,
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao girar roleta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resgata o prêmio ganho
     */
    public function claim(Request $request, $playId)
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

            // Busca a jogada
            $play = RoulettePlay::with('prize')->find($playId);

            if (!$play) {
                return response()->json(['error' => 'Prêmio não encontrado'], 404);
            }

            // Verifica se é do usuário
            if ($play->user_id != $user->id) {
                return response()->json(['error' => 'Este prêmio não pertence a você'], 403);
            }

            // Verifica se já foi resgatado
            if ($play->claimed) {
                return response()->json(['error' => 'Este prêmio já foi resgatado'], 400);
            }

            $prize = $play->prize;

            // Processa o prêmio de acordo com o tipo
            switch ($prize->type) {
                case 'points':
                    $user->increment('score', $prize->points_value);
                    break;

                case 'cashback':
                    $user->increment('economy', $prize->discount_value);
                    break;

                case 'voucher':
                    if ($prize->voucher_id) {
                        // Cria um UserVoucher para o usuário
                        \App\Models\UserVoucher::create([
                            'user_id' => $user->id,
                            'voucher_id' => $prize->voucher_id,
                            'used' => false,
                        ]);
                    }
                    break;

                case 'discount':
                case 'free_item':
                    // Esses prêmios são resgatados manualmente no estabelecimento
                    break;
            }

            // Marca como resgatado
            $play->claimed = true;
            $play->claimed_at = now();
            $play->save();

            return response()->json([
                'success' => true,
                'message' => 'Prêmio resgatado com sucesso!',
                'prize' => $prize,
                'user' => [
                    'score' => $user->score,
                    'economy' => $user->economy,
                ],
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao resgatar prêmio',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pega giro diário gratuito
     */
    public function getDailySpin(Request $request)
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

            if (!$user->canGetDailySpin()) {
                return response()->json(['error' => 'Você já pegou seu giro diário hoje'], 400);
            }

            // Adiciona 1 giro e atualiza a data
            $user->increment('roulette_spins_available');
            $user->last_daily_spin = now();
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Giro diário adicionado!',
                'spins_available' => $user->roulette_spins_available,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao pegar giro diário',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Histórico de prêmios ganhos
     */
    public function history(Request $request)
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

            $plays = RoulettePlay::with('prize')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'history' => $plays,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar histórico'], 500);
        }
    }

    /**
     * Seleciona prêmio baseado em probabilidade
     */
    private function selectPrizeByProbability($prizes)
    {
        $totalProbability = $prizes->sum('probability');
        $random = rand(1, $totalProbability);
        
        $currentProbability = 0;
        foreach ($prizes as $prize) {
            $currentProbability += $prize->probability;
            if ($random <= $currentProbability) {
                return $prize;
            }
        }

        // Fallback: retorna primeiro prêmio
        return $prizes->first();
    }
}
