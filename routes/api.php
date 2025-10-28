<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\RatingContoller;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\RouletteController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\InfluencerController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// ========== WEBHOOK ABACATEPAY (sem autenticação) ==========
Route::post('/webhook/abacatepay', [WebhookController::class, 'abacatepay'])->name('webhook.abacatepay');

Route::get('/banner', [BannerController::class, 'list']);

Route::get('/user', [UserController::class, 'show']);

Route::post('/auth', [AuthController::class, 'register']); 
Route::post('/auth/login', [AuthController::class, 'login']); 
Route::post('/auth/recover', [AuthController::class, 'recoverPassword'])->name('password.email');
Route::put('/auth', [AuthController::class, 'updateProfile']); 
Route::delete('/auth/{id}', [AuthController::class, 'deleteProfile']);

Route::get('/places', [PlaceController::class, 'list']);
Route::get('/places/{id}', [PlaceController::class, 'list']);
Route::get('/place/{id}', [PlaceController::class, 'show']); 

Route::get('/top', [PlaceController::class, 'listTop']);

Route::post('/rating/{id}', [RatingContoller::class, 'rate']); 

Route::get('/citys', [FilterController::class, 'city']); 

Route::get('/categories/{id}', [FilterController::class, 'categorie']); 

Route::get('/vouchers/{id}', [VoucherController::class, 'list']); 
Route::post('/vouchers/{id}', [VoucherController::class, 'use']);

Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
Route::post('/subscription', [SubscriptionController::class, 'buy']);

Route::get('/favorites', [FavoriteController::class, 'list']);
Route::post('/favorite/{id}', [FavoriteController::class, 'add']); 
Route::delete('/favorite/{id}', [FavoriteController::class, 'delete']);

// Rotas do Clube
Route::get('/club/benefits', [ClubController::class, 'benefits']);
Route::get('/club/info', [ClubController::class, 'info']);

// Rotas de Planos de Assinatura
Route::get('/subscription/plans', [SubscriptionPlanController::class, 'index']);
Route::get('/subscription/plans/{slug}', [SubscriptionPlanController::class, 'show']);

// Rotas da Roleta
Route::get('/roulette/prizes', [RouletteController::class, 'prizes']);
Route::post('/roulette/spin', [RouletteController::class, 'spin']);
Route::post('/roulette/plays/{id}/claim', [RouletteController::class, 'claim']);
Route::post('/roulette/daily-spin', [RouletteController::class, 'getDailySpin']);
Route::get('/roulette/history', [RouletteController::class, 'history']);

// ========== ROTAS DO FEED DE VÍDEOS (TikTok-style) ==========
Route::get('/videos/feed', [VideoController::class, 'feed']); // Feed principal
Route::get('/videos/influencer/{id}', [VideoController::class, 'influencerVideos']); // Vídeos de um influenciador
Route::post('/videos/{id}/view', [VideoController::class, 'view']); // Registrar visualização
Route::post('/videos/{id}/like', [VideoController::class, 'like']); // Like/Unlike
Route::post('/videos/{id}/share', [VideoController::class, 'share']); // Compartilhar

// Rotas autenticadas de vídeos (requer JWT)
Route::post('/videos/upload', [VideoController::class, 'upload']); // Upload de vídeo (influenciador)
Route::get('/videos/my-videos', [VideoController::class, 'myVideos']); // Meus vídeos
Route::delete('/videos/{id}', [VideoController::class, 'destroy']); // Deletar vídeo

// ========== ROTAS DE PROPOSTAS ==========
Route::post('/proposals', [ProposalController::class, 'create']); // Criar proposta (influenciador)
Route::get('/proposals/my-proposals', [ProposalController::class, 'myProposals']); // Minhas propostas enviadas
Route::get('/proposals/place/{placeId}', [ProposalController::class, 'placeProposals']); // Propostas recebidas (proprietário)
Route::post('/proposals/{id}/accept', [ProposalController::class, 'accept']); // Aceitar (proprietário)
Route::post('/proposals/{id}/reject', [ProposalController::class, 'reject']); // Rejeitar (proprietário)
Route::post('/proposals/{id}/complete', [ProposalController::class, 'complete']); // Marcar concluída (influenciador)

// ========== ROTAS DE CHAT ==========
Route::get('/chats', [ChatController::class, 'index']); // Listar conversas
Route::post('/chats', [ChatController::class, 'create']); // Criar conversa
Route::get('/chats/{id}/messages', [ChatController::class, 'messages']); // Ver mensagens
Route::post('/chats/{id}/send', [ChatController::class, 'send']); // Enviar mensagem
Route::post('/chats/{id}/mark-read', [ChatController::class, 'markRead']); // Marcar como lida

// ========== ROTAS DE CARTEIRA ==========
Route::get('/wallet/balance', [WalletController::class, 'balance']); // Ver saldo
Route::get('/wallet/transactions', [WalletController::class, 'transactions']); // Histórico
Route::post('/wallet/deposit/card', [WalletController::class, 'depositCard']); // Depositar via cartão
Route::post('/wallet/deposit/pix', [WalletController::class, 'depositPix']); // Gerar QR Code PIX
Route::post('/wallet/deposit/pix/{id}/confirm', [WalletController::class, 'confirmPix']); // Confirmar PIX
Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']); // Sacar
Route::put('/wallet/pix-key', [WalletController::class, 'updatePixKey']); // Atualizar chave PIX

// ========== ROTAS DE INFLUENCIADORES (para proprietários) ==========
Route::get('/influencers', [InfluencerController::class, 'index']); // Listar influenciadores
Route::get('/influencers/top', [InfluencerController::class, 'top']); // Ranking
Route::get('/influencers/category/{id}', [InfluencerController::class, 'byCategory']); // Por categoria
Route::get('/influencers/{id}', [InfluencerController::class, 'show']); // Perfil completo
Route::post('/influencers/{id}/contact', [InfluencerController::class, 'contact']); // Iniciar contato
