<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\PartnerWebController;
use App\Http\Controllers\Web\AdminWebController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Login Routes
Route::get('/', [AuthWebController::class, 'showLoginForm'])->name('login');
Route::get('/login', [AuthWebController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

// CSRF Token refresh
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->middleware('web');

// Influencer Routes
Route::prefix('influencer')->name('influencer.')->middleware(['auth', 'check.influencer'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Web\InfluencerWebController::class, 'dashboard'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [App\Http\Controllers\Web\InfluencerWebController::class, 'profile'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\Web\InfluencerWebController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [App\Http\Controllers\Web\InfluencerWebController::class, 'updatePassword'])->name('password.update');
    
    // Wallet
    Route::get('/wallet', [App\Http\Controllers\Web\InfluencerWebController::class, 'wallet'])->name('wallet');
    Route::post('/wallet/withdraw', [App\Http\Controllers\Web\InfluencerWebController::class, 'withdrawWallet'])->name('wallet.withdraw');
    Route::post('/wallet/deposit', [App\Http\Controllers\Web\InfluencerWebController::class, 'depositWallet'])->name('wallet.deposit');
    Route::post('/wallet/check-pix', [App\Http\Controllers\Web\InfluencerWebController::class, 'checkPixPayment'])->name('wallet.check-pix');
    
    // Proposals
    Route::get('/proposals', [App\Http\Controllers\Web\InfluencerWebController::class, 'proposals'])->name('proposals');
    Route::post('/proposals/{id}/accept', [App\Http\Controllers\Web\InfluencerWebController::class, 'acceptProposal'])->name('proposals.accept');
    Route::post('/proposals/{id}/reject', [App\Http\Controllers\Web\InfluencerWebController::class, 'rejectProposal'])->name('proposals.reject');
    
    // Chats
    Route::get('/chats', [App\Http\Controllers\Web\InfluencerWebController::class, 'chats'])->name('chats');
    Route::get('/chats/{id}', [App\Http\Controllers\Web\InfluencerWebController::class, 'showChat'])->name('chats.show');
    Route::post('/chats/{id}/send', [App\Http\Controllers\Web\InfluencerWebController::class, 'sendMessage'])->name('chats.send');
    
    // Videos
    Route::get('/videos', [App\Http\Controllers\Web\InfluencerWebController::class, 'videos'])->name('videos.index');
    Route::post('/videos', [App\Http\Controllers\Web\InfluencerWebController::class, 'storeVideo'])->name('videos.store');
    Route::get('/videos/{id}/edit', [App\Http\Controllers\Web\InfluencerWebController::class, 'editVideo'])->name('videos.edit');
    Route::put('/videos/{id}', [App\Http\Controllers\Web\InfluencerWebController::class, 'updateVideo'])->name('videos.update');
    Route::delete('/videos/{id}', [App\Http\Controllers\Web\InfluencerWebController::class, 'deleteVideo'])->name('videos.delete');
    Route::post('/videos/boost', [App\Http\Controllers\Web\InfluencerWebController::class, 'boostVideo'])->name('videos.boost');
    Route::post('/videos/boost/{id}/toggle', [App\Http\Controllers\Web\InfluencerWebController::class, 'toggleBoost'])->name('videos.boost.toggle');
    Route::post('/videos/boost/{id}/finalize', [App\Http\Controllers\Web\InfluencerWebController::class, 'finalizeBoost'])->name('videos.boost.finalize');
});

// Partner (Proprietário) Routes
Route::prefix('partner')->name('partner.')->middleware(['auth', 'check.partner'])->group(function () {
    Route::get('/dashboard', [PartnerWebController::class, 'dashboard'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [PartnerWebController::class, 'profile'])->name('profile');
    Route::put('/profile', [PartnerWebController::class, 'updateProfile'])->name('profile.update');
    
    // Settings
    Route::get('/settings', [PartnerWebController::class, 'settings'])->name('settings');
    Route::put('/settings', [PartnerWebController::class, 'updateSettings'])->name('settings.update');
    
    // Places
    Route::get('/places', [PartnerWebController::class, 'places'])->name('places');
    Route::get('/places/create', [PartnerWebController::class, 'createPlace'])->name('places.create');
    Route::post('/places', [PartnerWebController::class, 'storePlace'])->name('places.store');
    Route::get('/places/{id}/edit', [PartnerWebController::class, 'editPlace'])->name('places.edit');
    Route::put('/places/{id}', [PartnerWebController::class, 'updatePlace'])->name('places.update');
    Route::delete('/places/{id}', [PartnerWebController::class, 'deletePlace'])->name('places.delete');
    
    // Videos
    Route::get('/videos', [PartnerWebController::class, 'videos'])->name('videos');
    Route::post('/videos/boost', [PartnerWebController::class, 'boostVideo'])->name('videos.boost');
    
    // Proposals
    Route::get('/proposals', [PartnerWebController::class, 'proposals'])->name('proposals');
    Route::post('/proposals/{id}/accept', [PartnerWebController::class, 'acceptProposal'])->name('proposals.accept');
    Route::post('/proposals/{id}/reject', [PartnerWebController::class, 'rejectProposal'])->name('proposals.reject');
    
    // Chats
    Route::get('/chats', [PartnerWebController::class, 'chats'])->name('chats');
    Route::get('/chats/{id}', [PartnerWebController::class, 'showChat'])->name('chats.show');
    Route::post('/chats/{id}/send', [PartnerWebController::class, 'sendMessage'])->name('chats.send');
    
    // Wallet
    Route::get('/wallet', [PartnerWebController::class, 'wallet'])->name('wallet');
    Route::post('/wallet/deposit', [PartnerWebController::class, 'depositWallet'])->name('wallet.deposit');
    Route::post('/wallet/withdraw', [PartnerWebController::class, 'withdrawWallet'])->name('wallet.withdraw');
    Route::post('/wallet/check-pix', [PartnerWebController::class, 'checkPixPayment'])->name('wallet.check-pix');
    
    // Featured
    Route::get('/featured', [PartnerWebController::class, 'featured'])->name('featured');
    Route::post('/featured/purchase', [PartnerWebController::class, 'purchaseFeatured'])->name('featured.purchase');
    
    // Subscription Plans
    Route::get('/plans', [PartnerWebController::class, 'plans'])->name('plans');
    Route::post('/subscription/subscribe', [PartnerWebController::class, 'subscribe'])->name('subscription.subscribe');
    Route::post('/subscription/cancel', [PartnerWebController::class, 'cancelSubscription'])->name('subscription.cancel');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'check.admin'])->group(function () {
    Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('dashboard');
    
    // Profile & Settings
    Route::get('/profile', [AdminWebController::class, 'profile'])->name('profile');
    Route::put('/profile', [AdminWebController::class, 'updateProfile'])->name('profile.update');
    Route::get('/settings', [AdminWebController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminWebController::class, 'updateSettings'])->name('settings.update');
    Route::post('/cache/clear', [AdminWebController::class, 'clearCache'])->name('cache.clear');
    
    // Users
    Route::get('/users', [AdminWebController::class, 'users'])->name('users');
    Route::get('/users/create', [AdminWebController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminWebController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/edit', [AdminWebController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminWebController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminWebController::class, 'deleteUser'])->name('users.delete');
    
    // Places
    Route::get('/places', [AdminWebController::class, 'places'])->name('places');
    Route::get('/places/create', [AdminWebController::class, 'createPlace'])->name('places.create');
    Route::post('/places', [AdminWebController::class, 'storePlace'])->name('places.store');
    Route::delete('/places/{id}', [AdminWebController::class, 'deletePlace'])->name('places.delete');
    
    // Videos
    Route::get('/videos', [AdminWebController::class, 'videos'])->name('videos');
    Route::delete('/videos/{id}', [AdminWebController::class, 'deleteVideo'])->name('videos.delete');
    
    // Proposals
    Route::get('/proposals', [AdminWebController::class, 'proposals'])->name('proposals');
    
    // Transactions
    Route::get('/transactions', [AdminWebController::class, 'transactions'])->name('transactions');
    Route::post('/transactions/{id}/approve', [AdminWebController::class, 'approveWithdrawal'])->name('transactions.approve');
    Route::post('/transactions/{id}/reject', [AdminWebController::class, 'rejectWithdrawal'])->name('transactions.reject');
    
    // Banners
    Route::get('/banners', [AdminWebController::class, 'banners'])->name('banners');
    Route::get('/banners/create', [AdminWebController::class, 'createBanner'])->name('banners.create');
    Route::post('/banners', [AdminWebController::class, 'storeBanner'])->name('banners.store');
    Route::get('/banners/{id}/edit', [AdminWebController::class, 'editBanner'])->name('banners.edit');
    Route::put('/banners/{id}', [AdminWebController::class, 'updateBanner'])->name('banners.update');
    Route::delete('/banners/{id}', [AdminWebController::class, 'deleteBanner'])->name('banners.delete');
    
    // Subscriptions
    Route::get('/subscriptions', [AdminWebController::class, 'subscriptions'])->name('subscriptions');
    
    // Cities
    Route::get('/cities', [AdminWebController::class, 'cities'])->name('cities');
    Route::get('/cities/create', [AdminWebController::class, 'createCity'])->name('cities.create');
    Route::post('/cities', [AdminWebController::class, 'storeCity'])->name('cities.store');
    Route::get('/cities/{id}/edit', [AdminWebController::class, 'editCity'])->name('cities.edit');
    Route::put('/cities/{id}', [AdminWebController::class, 'updateCity'])->name('cities.update');
    Route::delete('/cities/{id}', [AdminWebController::class, 'deleteCity'])->name('cities.delete');
    
    // Categories
    Route::get('/categories', [AdminWebController::class, 'categories'])->name('categories');
    Route::get('/categories/create', [AdminWebController::class, 'createCategory'])->name('categories.create');
    Route::post('/categories', [AdminWebController::class, 'storeCategory'])->name('categories.store');
    Route::get('/categories/{id}/edit', [AdminWebController::class, 'editCategory'])->name('categories.edit');
    Route::put('/categories/{id}', [AdminWebController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminWebController::class, 'deleteCategory'])->name('categories.delete');
});
