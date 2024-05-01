
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/banner', [BannerController::class, 'list']);

Route::get('/user', [UserController::class, 'show']);

Route::post('/auth', [AuthController::class, 'register']); 
Route::post('/auth/login', [AuthController::class, 'login']); 
Route::post('/auth/recover', [AuthController::class, 'recoverPassword'])->name('password.email');
Route::put('/auth', [AuthController::class, 'updateProfile']); 
Route::delete('/auth/{id}', [AuthController::class, 'deleteProfile']);

Route::post('/place/{id}', [PlaceController::class, 'rate']); 
Route::get('/place/{id}', [PlaceController::class, 'show']); 
Route::get('/place', [PlaceController::class, 'list']);
Route::get('/top', [PlaceController::class, 'listTop']);

Route::get('/vouchers/{id}', [VoucherController::class, 'list']); 
Route::post('/vouchers/{id}', [VoucherController::class, 'use']);

Route::post('/subscription/cancel/{id}', [SubscriptionController::class, 'cancel']);
Route::post('/subscription/{id}', [SubscriptionController::class, 'buy']);