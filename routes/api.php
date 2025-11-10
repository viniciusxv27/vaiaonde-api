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

/*
|--------------------------------------------------------------------------
| API Routes - VaiAonde App
|--------------------------------------------------------------------------
| Documentação: /api/docs (se implementado)
| Versão: 1.0
| Base URL: https://vaiaondecapixaba.com.br/api
*/

// ========================================
// WEBHOOKS (sem autenticação)
// ========================================
Route::post('/webhook/abacatepay', [WebhookController::class, 'abacatepay'])->name('webhook.abacatepay');

// ========================================
// AUTENTICAÇÃO (públicas)
// ========================================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/recover-password', [AuthController::class, 'recoverPassword'])->name('api.auth.recover');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.auth.reset');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('api.auth.verify');
});

// ========================================
// ROTAS PÚBLICAS (sem autenticação)
// ========================================

// Banners
Route::get('/banners', [BannerController::class, 'list'])->name('api.banners.list');

// Destaques
Route::get('/highlights', [PlaceController::class, 'listTop'])->name('api.highlights');
Route::get('/featured', [PlaceController::class, 'listFeatured'])->name('api.featured');

// Places (visualização pública)
Route::get('/places', [PlaceController::class, 'list'])->name('api.places.list');
Route::get('/places/{id}', [PlaceController::class, 'show'])->name('api.places.show');

// Categorias e Cidades
Route::get('/cities', [FilterController::class, 'city'])->name('api.cities.list');
Route::get('/categories', [FilterController::class, 'categorie'])->name('api.categories.list');

// Planos de Assinatura
Route::get('/subscription/plans', [SubscriptionPlanController::class, 'index'])->name('api.subscription.plans');
Route::get('/subscription/plans/{slug}', [SubscriptionPlanController::class, 'show'])->name('api.subscription.plans.show');

// Clube (info pública)
Route::get('/club/benefits', [ClubController::class, 'benefits'])->name('api.club.benefits');
Route::get('/club/info', [ClubController::class, 'info'])->name('api.club.info');

// Roleta (prêmios públicos)
Route::get('/roulette/prizes', [RouletteController::class, 'prizes'])->name('api.roulette.prizes');

// Feed de Vídeos (visualização pública)
Route::get('/videos/feed', [VideoController::class, 'feed'])->name('api.videos.feed');
Route::get('/videos/influencer/{id}', [VideoController::class, 'influencerVideos'])->name('api.videos.influencer');

// Influenciadores (visualização pública)
Route::get('/influencers', [InfluencerController::class, 'index'])->name('api.influencers.list');
Route::get('/influencers/top', [InfluencerController::class, 'top'])->name('api.influencers.top');
Route::get('/influencers/category/{id}', [InfluencerController::class, 'byCategory'])->name('api.influencers.category');
Route::get('/influencers/{id}', [InfluencerController::class, 'show'])->name('api.influencers.show');

// ========================================
// ROTAS PROTEGIDAS (requer autenticação JWT)
// ========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // ========== PERFIL DO USUÁRIO ==========
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'show'])->name('api.user.profile');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.user.update');
        Route::delete('/profile', [AuthController::class, 'deleteProfile'])->name('api.user.delete');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.user.logout');
    });

    // ========== PLACES (ações autenticadas) ==========
    Route::prefix('places')->group(function () {
        Route::get('/category/{id}', [PlaceController::class, 'listByCategory'])->name('api.places.category');
        Route::get('/city/{id}', [PlaceController::class, 'listByCity'])->name('api.places.city');
        Route::post('/{id}/rate', [RatingContoller::class, 'rate'])->name('api.places.rate');
    });

    // ========== FAVORITOS ==========
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'list'])->name('api.favorites.list');
        Route::post('/{id}', [FavoriteController::class, 'add'])->name('api.favorites.add');
        Route::delete('/{id}', [FavoriteController::class, 'delete'])->name('api.favorites.delete');
    });

    // ========== VÍDEOS (ações autenticadas) ==========
    Route::prefix('videos')->group(function () {
        // Interações
        Route::post('/{id}/view', [VideoController::class, 'view'])->name('api.videos.view');
        Route::post('/{id}/like', [VideoController::class, 'like'])->name('api.videos.like');
        Route::post('/{id}/share', [VideoController::class, 'share'])->name('api.videos.share');
        
        // Upload e gerenciamento (influenciadores)
        Route::post('/upload', [VideoController::class, 'upload'])->name('api.videos.upload');
        Route::get('/my-videos', [VideoController::class, 'myVideos'])->name('api.videos.mine');
        Route::delete('/{id}', [VideoController::class, 'destroy'])->name('api.videos.delete');
    });

    // ========== INFLUENCIADORES (contato) ==========
    Route::post('/influencers/{id}/contact', [InfluencerController::class, 'contact'])->name('api.influencers.contact');

    // ========== CLUBE DE BENEFÍCIOS ==========
    Route::prefix('club')->group(function () {
        Route::post('/subscribe', [SubscriptionController::class, 'buy'])->name('api.club.subscribe');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('api.club.cancel');
    });

    // ========== ROLETA ==========
    Route::prefix('roulette')->group(function () {
        Route::post('/spin', [RouletteController::class, 'spin'])->name('api.roulette.spin');
        Route::post('/daily-spin', [RouletteController::class, 'getDailySpin'])->name('api.roulette.daily');
        Route::get('/history', [RouletteController::class, 'history'])->name('api.roulette.history');
        Route::post('/plays/{id}/claim', [RouletteController::class, 'claim'])->name('api.roulette.claim');
    });

    // ========== CUPONS/VOUCHERS ==========
    Route::prefix('vouchers')->group(function () {
        Route::get('/{id}', [VoucherController::class, 'list'])->name('api.vouchers.list');
        Route::post('/{id}', [VoucherController::class, 'use'])->name('api.vouchers.use');
    });

    // ========== PROPOSTAS (influenciadores ↔ proprietários) ==========
    Route::prefix('proposals')->group(function () {
        // Influenciador
        Route::post('/', [ProposalController::class, 'create'])->name('api.proposals.create');
        Route::get('/my-proposals', [ProposalController::class, 'myProposals'])->name('api.proposals.mine');
        Route::post('/{id}/complete', [ProposalController::class, 'complete'])->name('api.proposals.complete');
        
        // Proprietário
        Route::get('/place/{placeId}', [ProposalController::class, 'placeProposals'])->name('api.proposals.place');
        Route::post('/{id}/accept', [ProposalController::class, 'accept'])->name('api.proposals.accept');
        Route::post('/{id}/reject', [ProposalController::class, 'reject'])->name('api.proposals.reject');
    });

    // ========== CHAT ==========
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('api.chat.list');
        Route::post('/', [ChatController::class, 'create'])->name('api.chat.create');
        Route::get('/{id}/messages', [ChatController::class, 'messages'])->name('api.chat.messages');
        Route::post('/{id}/send', [ChatController::class, 'send'])->name('api.chat.send');
        Route::post('/{id}/mark-read', [ChatController::class, 'markRead'])->name('api.chat.read');
    });

    // ========== CARTEIRA (Depósitos/Saques) ==========
    Route::prefix('wallet')->group(function () {
        Route::get('/balance', [WalletController::class, 'balance'])->name('api.wallet.balance');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('api.wallet.transactions');
        
        // Depósitos
        Route::post('/deposit/card', [WalletController::class, 'depositCard'])->name('api.wallet.deposit.card');
        Route::post('/deposit/pix', [WalletController::class, 'depositPix'])->name('api.wallet.deposit.pix');
        Route::post('/deposit/pix/{id}/confirm', [WalletController::class, 'confirmPix'])->name('api.wallet.deposit.confirm');
        
        // Saques
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('api.wallet.withdraw');
        Route::put('/pix-key', [WalletController::class, 'updatePixKey'])->name('api.wallet.pix');
    });
});
