<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminPlaceController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVoucherController;
use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminRouletteController;
use App\Http\Controllers\Admin\AdminSubscriptionPlanController;
use App\Http\Controllers\Admin\AdminVideoController;
use App\Http\Controllers\Admin\AdminProposalController;
use App\Http\Controllers\Admin\AdminTransactionController;
use Illuminate\Support\Facades\Route;

/**
 * Rotas do Painel Administrativo
 * Todas as rotas requerem autenticação de administrador via middleware IsAdmin
 */
Route::prefix('admin')->middleware('admin')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/analytics', [DashboardController::class, 'analytics']);
    
    // Gestão de Places (Estabelecimentos)
    Route::get('/places', [AdminPlaceController::class, 'index']);
    Route::get('/places/{id}', [AdminPlaceController::class, 'show']);
    Route::post('/places', [AdminPlaceController::class, 'store']);
    Route::put('/places/{id}', [AdminPlaceController::class, 'update']);
    Route::delete('/places/{id}', [AdminPlaceController::class, 'destroy']);
    
    // Gestão de Usuários
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    
    // Gestão de Vouchers
    Route::get('/vouchers', [AdminVoucherController::class, 'index']);
    Route::get('/vouchers/{id}', [AdminVoucherController::class, 'show']);
    Route::post('/vouchers', [AdminVoucherController::class, 'store']);
    Route::put('/vouchers/{id}', [AdminVoucherController::class, 'update']);
    Route::delete('/vouchers/{id}', [AdminVoucherController::class, 'destroy']);
    
    // Gestão de Banners
    Route::get('/banners', [AdminBannerController::class, 'index']);
    Route::get('/banners/{id}', [AdminBannerController::class, 'show']);
    Route::post('/banners', [AdminBannerController::class, 'store']);
    Route::put('/banners/{id}', [AdminBannerController::class, 'update']);
    Route::delete('/banners/{id}', [AdminBannerController::class, 'destroy']);
    
    // Gestão de Prêmios da Roleta
    Route::get('/roulette/prizes', [AdminRouletteController::class, 'index']);
    Route::get('/roulette/prizes/{id}', [AdminRouletteController::class, 'show']);
    Route::post('/roulette/prizes', [AdminRouletteController::class, 'store']);
    Route::put('/roulette/prizes/{id}', [AdminRouletteController::class, 'update']);
    Route::delete('/roulette/prizes/{id}', [AdminRouletteController::class, 'destroy']);
    
    // Gestão de Planos de Assinatura
    Route::get('/subscription/plans', [AdminSubscriptionPlanController::class, 'index']);
    Route::get('/subscription/plans/{id}', [AdminSubscriptionPlanController::class, 'show']);
    Route::post('/subscription/plans', [AdminSubscriptionPlanController::class, 'store']);
    Route::put('/subscription/plans/{id}', [AdminSubscriptionPlanController::class, 'update']);
    Route::delete('/subscription/plans/{id}', [AdminSubscriptionPlanController::class, 'destroy']);
    
    // Gestão de Vídeos (Social Media)
    Route::get('/videos', [AdminVideoController::class, 'index']);
    Route::get('/videos/stats', [AdminVideoController::class, 'stats']);
    Route::delete('/videos/{id}', [AdminVideoController::class, 'destroy']);
    
    // Gestão de Propostas
    Route::get('/proposals', [AdminProposalController::class, 'index']);
    Route::get('/proposals/stats', [AdminProposalController::class, 'stats']);
    Route::get('/proposals/{id}', [AdminProposalController::class, 'show']);
    Route::post('/proposals/{id}/cancel', [AdminProposalController::class, 'cancel']);
    
    // Gestão de Transações Financeiras
    Route::get('/transactions', [AdminTransactionController::class, 'index']);
    Route::get('/transactions/stats', [AdminTransactionController::class, 'stats']);
    Route::post('/transactions/{id}/approve', [AdminTransactionController::class, 'approve']);
    Route::post('/transactions/{id}/reject', [AdminTransactionController::class, 'reject']);
});
