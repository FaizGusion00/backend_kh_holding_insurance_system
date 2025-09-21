<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminFacilityController;
use App\Http\Controllers\AdminPlanController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminCommissionController;
use App\Http\Controllers\AdminWithdrawalController;

Route::get('/', function () {
    return view('welcome');
});

// Include test routes
require __DIR__.'/test.php';

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin auth routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Admin routes (protected)
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Plans management
    Route::resource('plans', AdminPlanController::class);
    Route::get('plans/{plan}/commission-rates', [AdminPlanController::class, 'commissionRates'])->name('plans.commission-rates');
    Route::post('plans/{plan}/commission-rates', [AdminPlanController::class, 'updateCommissionRates'])->name('plans.commission-rates.update');
    
    // Users management
    Route::resource('users', AdminUserController::class);
    Route::get('users/{user}/network', [AdminUserController::class, 'network'])->name('users.network');
    Route::get('users/{user}/medical', [AdminUserController::class, 'medical'])->name('users.medical');
    
    // Commission management
    Route::get('commissions', [AdminCommissionController::class, 'index'])->name('commissions.index');
    Route::get('commissions/transactions', [AdminCommissionController::class, 'transactions'])->name('commissions.transactions');
    
    // Withdrawal management
    Route::get('withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('withdrawals/{withdrawal}', [AdminWithdrawalController::class, 'show'])->name('withdrawals.show');
    Route::post('withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
    Route::post('withdrawals/{withdrawal}/mark-paid', [AdminWithdrawalController::class, 'markPaid'])->name('withdrawals.mark-paid');
    
    // Facilities management
    Route::get('hospitals', [AdminFacilityController::class, 'index'])->defaults('type', 'hospital')->name('facilities.hospitals.index');
    Route::get('hospitals/create', [AdminFacilityController::class, 'create'])->defaults('type', 'hospital')->name('facilities.hospitals.create');
    Route::post('hospitals', [AdminFacilityController::class, 'store'])->defaults('type', 'hospital')->name('facilities.hospitals.store');
    Route::get('hospitals/{id}', [AdminFacilityController::class, 'show'])->defaults('type', 'hospital')->name('facilities.hospitals.show');
    Route::get('hospitals/{id}/edit', [AdminFacilityController::class, 'edit'])->defaults('type', 'hospital')->name('facilities.hospitals.edit');
    Route::put('hospitals/{id}', [AdminFacilityController::class, 'update'])->defaults('type', 'hospital')->name('facilities.hospitals.update');
    Route::delete('hospitals/{id}', [AdminFacilityController::class, 'destroy'])->defaults('type', 'hospital')->name('facilities.hospitals.destroy');
    
    Route::get('clinics', [AdminFacilityController::class, 'index'])->defaults('type', 'clinic')->name('facilities.clinics.index');
    Route::get('clinics/create', [AdminFacilityController::class, 'create'])->defaults('type', 'clinic')->name('facilities.clinics.create');
    Route::post('clinics', [AdminFacilityController::class, 'store'])->defaults('type', 'clinic')->name('facilities.clinics.store');
    Route::get('clinics/{id}', [AdminFacilityController::class, 'show'])->defaults('type', 'clinic')->name('facilities.clinics.show');
    Route::get('clinics/{id}/edit', [AdminFacilityController::class, 'edit'])->defaults('type', 'clinic')->name('facilities.clinics.edit');
    Route::put('clinics/{id}', [AdminFacilityController::class, 'update'])->defaults('type', 'clinic')->name('facilities.clinics.update');
    Route::delete('clinics/{id}', [AdminFacilityController::class, 'destroy'])->defaults('type', 'clinic')->name('facilities.clinics.destroy');
});

require __DIR__.'/auth.php';
