<?php

use App\Http\Controllers\Partner\PartnerDashboardController;
use App\Http\Controllers\Partner\PartnerHighlightController;
use App\Http\Controllers\Partner\PartnerWalletController;
use Illuminate\Support\Facades\Route;

/**
 * Rotas da Área do Parceiro (Proprietário)
 * Todas as rotas requerem autenticação JWT e role 'proprietario'
 */
Route::prefix('partner')->group(function () {
    
    // Dashboard do Parceiro
    Route::get('/dashboard', [PartnerDashboardController::class, 'index']);
    Route::get('/places/{placeId}/metrics', [PartnerDashboardController::class, 'placeMetrics']);
    Route::get('/places/{placeId}/videos', [PartnerDashboardController::class, 'placeVideos']);
    Route::get('/contracts/active', [PartnerDashboardController::class, 'activeContracts']);
    
    // Gestão de Destaques
    Route::get('/highlights', [PartnerHighlightController::class, 'index']);
    Route::post('/highlights/purchase', [PartnerHighlightController::class, 'purchase']);
    Route::post('/highlights/{highlightId}/cancel', [PartnerHighlightController::class, 'cancel']);
    
    // Carteira do Parceiro
    Route::get('/wallet/balance', [PartnerWalletController::class, 'balance']);
    Route::post('/wallet/add-balance', [PartnerWalletController::class, 'addBalance']);
    Route::get('/wallet/transactions', [PartnerWalletController::class, 'transactions']);
});

// Rota pública para listar lugares em destaque
Route::get('/highlighted-places', [PartnerHighlightController::class, 'highlighted']);
