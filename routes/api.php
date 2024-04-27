
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Rotas de autenticação
Route::post('/auth', [AuthController::class, 'register']); // Criação de conta
Route::post('/auth/login', [AuthController::class, 'login']); // Entrar na conta
Route::post('/auth/recover', [AuthController::class, 'recoverPassword'])->name('password.email');

; // Recuperar senha

// Rotas de perfil de usuário
Route::put('/auth/{id}', [AuthController::class, 'updateProfile']); // Alterar perfil de usuário
Route::delete('/auth/{id}', [AuthController::class, 'deleteProfile']); // Remover perfil de usuário

// Rotas de estabelecimentos
Route::post('/place/{id}', [PlaceController::class, 'rate']); // Classificar estabelecimentos
Route::get('/place/{id}', [PlaceController::class, 'show']); // Página de estabelecimentos
Route::get('/place', [PlaceController::class, 'list']); // Listar estabelecimentos

// Rotas de destaques
Route::get('/top', [PlaceController::class, 'listTop']); // Listar destaques

// Rotas de cupons
Route::get('/vouchers/{id}', [VoucherController::class, 'list']); // Listar cupons
Route::post('/vouchers/{id}', [VoucherController::class, 'use']); // Usar cupom

// Rotas de assinatura
Route::post('/subscription/cancel/{id}', [SubscriptionController::class, 'cancel']); // Cancelar assinatura
Route::post('/subscription/{id}', [SubscriptionController::class, 'buy']); // Comprar assinatura