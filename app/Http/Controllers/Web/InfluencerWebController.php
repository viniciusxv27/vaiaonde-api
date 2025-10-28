<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Boost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InfluencerWebController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Stats
        $totalVideos = Video::where('user_id', $user->id)->count();
        $totalViews = Video::where('user_id', $user->id)->sum('views_count');
        $pendingProposals = Proposal::where('influencer_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $acceptedProposals = Proposal::where('influencer_id', $user->id)
            ->where('status', 'accepted')
            ->count();
        
        // Recent videos
        $recentVideos = Video::where('user_id', $user->id)
            ->with('place')
            ->latest()
            ->take(5)
            ->get();
        
        // Recent proposals
        $recentProposals = Proposal::where('influencer_id', $user->id)
            ->with('place')
            ->latest()
            ->take(5)
            ->get();

        return view('influencer.dashboard', compact(
            'totalVideos',
            'totalViews',
            'pendingProposals',
            'acceptedProposals',
            'recentVideos',
            'recentProposals'
        ));
    }

    public function profile()
    {
        return view('influencer.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            if ($request->filled('current_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->with('error', 'Senha atual incorreta');
                }
                $data['password'] = Hash::make($request->password);
            }
        }

        $user->update($data);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function wallet()
    {
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('influencer.wallet', compact('transactions'));
    }

    public function withdrawWallet(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_method' => 'required|in:pix,bank_transfer',
            'pix_key' => 'required_if:payment_method,pix|string',
        ]);

        if ($request->amount > $user->wallet_balance) {
            return back()->with('error', 'Saldo insuficiente');
        }

        DB::beginTransaction();
        try {
            // Create withdrawal transaction
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'description' => 'Solicitação de saque via ' . $request->payment_method,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'pix_key' => $request->pix_key,
            ]);

            // Deduct from wallet
            $user->wallet_balance -= $request->amount;
            $user->save();

            DB::commit();
            return back()->with('success', 'Solicitação de saque enviada! Aguarde aprovação.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar saque: ' . $e->getMessage());
        }
    }

    public function proposals()
    {
        $user = Auth::user();
        $proposals = Proposal::where('influencer_id', $user->id)
            ->with(['place', 'place.owner'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('influencer.proposals', compact('proposals'));
    }

    public function acceptProposal($id)
    {
        $user = Auth::user();
        $proposal = Proposal::where('id', $id)
            ->where('influencer_id', $user->id)
            ->firstOrFail();

        $proposal->update(['status' => 'accepted']);

        return back()->with('success', 'Proposta aceita com sucesso!');
    }

    public function rejectProposal($id)
    {
        $user = Auth::user();
        $proposal = Proposal::where('id', $id)
            ->where('influencer_id', $user->id)
            ->firstOrFail();

        $proposal->update(['status' => 'rejected']);

        return back()->with('success', 'Proposta recusada.');
    }

    public function chats()
    {
        $user = Auth::user();
        $chats = Chat::where('influencer_id', $user->id)
            ->with(['place', 'place.owner', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->get();

        return view('influencer.chats', compact('chats'));
    }

    public function showChat($id)
    {
        $user = Auth::user();
        $chat = Chat::where('id', $id)
            ->where('influencer_id', $user->id)
            ->with(['place', 'place.owner', 'messages.user'])
            ->firstOrFail();

        // Mark messages as read
        Message::where('chat_id', $chat->id)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('influencer.chat-show', compact('chat'));
    }

    public function sendMessage(Request $request, $id)
    {
        $user = Auth::user();
        $chat = Chat::where('id', $id)
            ->where('influencer_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        Message::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        return back()->with('success', 'Mensagem enviada!');
    }

    public function videos()
    {
        $user = Auth::user();
        $videos = Video::where('user_id', $user->id)
            ->with(['place', 'user'])
            ->latest()
            ->paginate(12);

        return view('influencer.videos', compact('videos'));
    }

    public function depositWallet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
        ]);

        $user = Auth::user();

        try {
            $abacatePayService = new \App\Services\AbacatePayService();
            
            $result = $abacatePayService->createPixQrCode(
                $request->amount,
                'Recarga de saldo - Influenciador - User: ' . $user->id,
                [
                    'name' => $user->name,
                    'cellphone' => $user->phone ?? '',
                    'email' => $user->email,
                    'taxId' => $user->cpf ?? ''
                ],
                [
                    'user_id' => $user->id,
                    'type' => 'influencer_deposit'
                ]
            );

            if (!$result['success']) {
                return back()->with('error', $result['error'] ?? 'Erro ao gerar PIX');
            }

            if (empty($result['id']) || empty($result['qr_code'])) {
                return back()->with('error', 'Erro ao gerar QR Code PIX. Tente novamente.');
            }

            return back()->with([
                'success' => 'QR Code PIX gerado com sucesso! Aguardando pagamento...',
                'pix_qr_code' => $result['qr_code'],
                'pix_qr_code_url' => $result['qr_code_url'],
                'pix_id' => $result['id'],
                'pix_amount' => $request->amount,
                'pix_expires_at' => $result['expires_at'],
                'show_pix_modal' => true
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gerar PIX: ' . $e->getMessage());
        }
    }

    public function checkPixPayment(Request $request)
    {
        $request->validate([
            'pix_id' => 'required|string',
            'amount' => 'required|numeric'
        ]);

        try {
            $abacatePayService = new \App\Services\AbacatePayService();
            $result = $abacatePayService->checkPixQrCode($request->pix_id);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Erro ao verificar pagamento'
                ]);
            }

            if ($result['paid']) {
                $user = Auth::user();
                $amount = $request->amount;

                // Evitar duplicação
                $existingTransaction = Transaction::where('user_id', $user->id)
                    ->where('description', 'LIKE', '%PIX%' . $request->pix_id . '%')
                    ->where('type', 'deposit')
                    ->first();

                if (!$existingTransaction) {
                    $user->increment('wallet_balance', $amount);

                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'deposit',
                        'amount' => $amount,
                        'balance_before' => $user->wallet_balance - $amount,
                        'balance_after' => $user->wallet_balance,
                        'description' => 'Depósito via PIX - ID: ' . $request->pix_id,
                        'payment_method' => 'pix',
                        'status' => 'completed'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'message' => 'Pagamento confirmado! Saldo creditado.'
                ]);
            }

            return response()->json([
                'success' => true,
                'paid' => false,
                'message' => 'Aguardando pagamento...'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao verificar pagamento: ' . $e->getMessage()
            ]);
        }
    }

    public function boostVideo(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:videos,id',
            'amount' => 'required|numeric|min:10',
            'days' => 'required|integer|min:1|max:30',
        ]);

        $user = Auth::user();
        $video = Video::findOrFail($request->video_id);

        // Verificar se o vídeo pertence ao influenciador
        if ($video->user_id !== $user->id) {
            return back()->with('error', 'Você não tem permissão para impulsionar este vídeo');
        }

        // Verificar saldo
        if ($user->wallet_balance < $request->amount) {
            return back()->with('error', 'Saldo insuficiente');
        }

        DB::beginTransaction();
        try {
            $dailyBudget = $request->amount / $request->days;

            // Criar impulsionamento
            $boost = Boost::create([
                'video_id' => $request->video_id,
                'user_id' => $user->id,
                'amount' => $request->amount,
                'days' => $request->days,
                'daily_budget' => $dailyBudget,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays($request->days),
            ]);

            // Debitar saldo
            $user->decrement('wallet_balance', $request->amount);

            // Registrar transação
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'boost',
                'amount' => -$request->amount,
                'balance_before' => $user->wallet_balance + $request->amount,
                'balance_after' => $user->wallet_balance,
                'description' => "Impulsionamento de vídeo por {$request->days} dias - Budget diário: R$ " . number_format($dailyBudget, 2, ',', '.'),
                'status' => 'completed'
            ]);

            DB::commit();
            return back()->with('success', 'Vídeo impulsionado com sucesso! Budget diário: R$ ' . number_format($dailyBudget, 2, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao impulsionar vídeo: ' . $e->getMessage());
        }
    }
}
