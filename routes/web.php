<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\TradeHistoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SignalController as AdminSignalController;
use App\Http\Controllers\Admin\TradeController as AdminTradeController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ExchangeController;
use App\Http\Controllers\User\TradeController as UserTradeController;
use App\Http\Controllers\User\PositionController;
use App\Http\Controllers\User\AccountController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->is_admin ? 'admin.dashboard' : 'user.dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [LoginController::class, 'showForgotPasswordForm'])->name('password.request');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/sync-balance/{exchangeAccount}', [AdminUserController::class, 'syncBalance'])->name('users.sync-balance');
    
    // Signals
    Route::get('/signals', [AdminSignalController::class, 'index'])->name('signals.index');
    Route::post('/signals/generate', [AdminSignalController::class, 'generate'])->name('signals.generate');
    Route::post('/signals/{signal}/execute', [AdminSignalController::class, 'execute'])->name('signals.execute');
    Route::delete('/signals/{signal}', [AdminSignalController::class, 'cancel'])->name('signals.cancel');
    
    // Admin Trades (propagate to all users)
    Route::get('/trades', [AdminTradeController::class, 'index'])->name('trades.index');
    Route::post('/trades', [AdminTradeController::class, 'store'])->name('trades.store');
    Route::delete('/trades/{trade}', [AdminTradeController::class, 'destroy'])->name('trades.destroy');
    Route::post('/trades/close-all', [AdminTradeController::class, 'closeAll'])->name('trades.close-all');
    Route::get('/trades/{trade}/details', [AdminTradeController::class, 'details'])->name('trades.details');
    Route::post('/trades/preview-calculation', [AdminTradeController::class, 'previewCalculation'])->name('trades.preview-calculation');

    // Trade History
    Route::get('/trade-history', [TradeHistoryController::class, 'index'])->name('trade-history.index');
    Route::get('/trade-history/export', [TradeHistoryController::class, 'export'])->name('trade-history.export');
    
    // Monitoring Overview
    Route::get('/monitoring', [MonitoringController::class, 'overview'])->name('monitoring.overview');
    
    // User API Keys Management (view users' API keys)
    Route::get('/api-keys', [AdminSettingsController::class, 'apiKeys'])->name('api-keys.index');
    
    // Admin Exchange Configuration (admin's own API keys)
    Route::post('/api-keys', [AdminSettingsController::class, 'storeApiKey'])->name('api-keys.store');
    Route::delete('/api-keys/{exchange}', [AdminSettingsController::class, 'deleteApiKey'])->name('api-keys.destroy');
    Route::patch('/api-keys/{exchange}/toggle', [AdminSettingsController::class, 'toggleApiKey'])->name('api-keys.toggle');
    // Add this route for admin balance sync
    Route::post('/api-keys/{exchange}/sync', [AdminSettingsController::class, 'syncAdminBalance'])->name('api-keys.sync');
    
    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/chart-data', [AnalyticsController::class, 'getChartData'])->name('analytics.chart-data');
    
    // Settings
    Route::get('/settings/signal-generator', [AdminSettingsController::class, 'signalGenerator'])->name('settings.signal-generator');
    Route::put('/settings/signal-generator', [AdminSettingsController::class, 'updateSignalGenerator'])->name('settings.signal-generator.update');
    Route::get('/settings/system', [AdminSettingsController::class, 'system'])->name('settings.system');
    Route::put('/settings/system', [AdminSettingsController::class, 'updateSystem'])->name('settings.system.update');
});

// User Routes
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    
    // Exchange Management
    Route::get('/exchanges/connect', [ExchangeController::class, 'connect'])->name('exchanges.connect');
    Route::post('/exchanges/connect', [ExchangeController::class, 'store'])->name('exchanges.store');
    Route::get('/exchanges/manage', [ExchangeController::class, 'manage'])->name('exchanges.manage');
    Route::put('/exchanges/{exchange}', [ExchangeController::class, 'update'])->name('exchanges.update');
    Route::delete('/exchanges/{exchange}', [ExchangeController::class, 'destroy'])->name('exchanges.destroy');
    Route::post('/exchanges/{exchange}/sync', [ExchangeController::class, 'syncBalance'])->name('exchanges.sync');
    
    // Trades
    Route::get('/trades', [UserTradeController::class, 'index'])->name('trades.index');
    
    // Positions
    Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
    
    // Account Settings
    Route::get('/account/settings', [AccountController::class, 'settings'])->name('account.settings');
    Route::put('/account/settings', [AccountController::class, 'update'])->name('account.update');
});