
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
use Illuminate\Support\Facades\Route;

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