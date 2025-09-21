<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function () {
    // Auth
    Route::post('/auth/register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/auth/login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/auth/refresh', [\App\Http\Controllers\AuthController::class, 'refresh']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/auth/me', [\App\Http\Controllers\AuthController::class, 'me']);
        Route::get('/auth/profile', [\App\Http\Controllers\AuthController::class, 'me']);
        Route::post('/auth/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        Route::put('/auth/profile', [\App\Http\Controllers\AuthController::class, 'updateProfile']);
        Route::post('/auth/change-password', [\App\Http\Controllers\AuthController::class, 'changePassword']);
        Route::post('/auth/send-phone-verification', [\App\Http\Controllers\AuthController::class, 'sendPhoneVerification']);
        Route::post('/auth/verify-phone-change', [\App\Http\Controllers\AuthController::class, 'verifyPhoneChange']);
        Route::post('/auth/update-agent-code', [\App\Http\Controllers\AuthController::class, 'updateAgentCode']);

        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
        Route::get('/dashboard/members', [\App\Http\Controllers\MlmController::class, 'network']);

        // Wallet
        Route::get('/wallet', [\App\Http\Controllers\WalletController::class, 'summary']);
        Route::get('/wallet/balance', [\App\Http\Controllers\WalletController::class, 'balance']);
        Route::get('/wallet/transactions', [\App\Http\Controllers\WalletController::class, 'transactions']);
        Route::post('/wallet/withdraw', [\App\Http\Controllers\WalletController::class, 'requestWithdraw']);
        Route::get('/wallet/withdrawals', [\App\Http\Controllers\WalletController::class, 'withdrawals']);

        // MLM
        Route::get('/mlm/network', [\App\Http\Controllers\MlmController::class, 'network']);
        Route::get('/mlm/commission-history', [\App\Http\Controllers\MlmController::class, 'commissionHistory']);
        Route::get('/mlm/level-summary', [\App\Http\Controllers\MlmController::class, 'levelSummary']);
        Route::get('/mlm/referrals', [\App\Http\Controllers\MlmController::class, 'getReferrals']);
        Route::get('/mlm/downlines', [\App\Http\Controllers\MlmController::class, 'getDownlines']);
        Route::get('/mlm/commission-summary', [\App\Http\Controllers\MlmController::class, 'getCommissionSummary']);
        Route::get('/mlm/clients', [\App\Http\Controllers\MlmController::class, 'getMedicalClients']);
        Route::get('/mlm/client-policies/{clientId}', [\App\Http\Controllers\MlmController::class, 'getClientPolicies']);
        Route::put('/mlm/policy/{policyId}/status', [\App\Http\Controllers\MlmController::class, 'updatePolicyStatus']);
        Route::post('/mlm/continue-payment', [\App\Http\Controllers\MlmController::class, 'processContinuePayment']);
        Route::post('/mlm/verify-continue-payment', [\App\Http\Controllers\MlmController::class, 'verifyContinuePayment']);
        Route::post('/mlm/register-client', [\App\Http\Controllers\MlmController::class, 'registerClient']);
        Route::post('/mlm/register-bulk-clients', [\App\Http\Controllers\MlmController::class, 'registerBulkClients']);

        // Policies
        Route::get('/policies', [\App\Http\Controllers\PolicyController::class, 'index']);
        Route::get('/policies/{id}', [\App\Http\Controllers\PolicyController::class, 'show']);
        Route::post('/policies/purchase', [\App\Http\Controllers\PolicyController::class, 'purchase']);

        // Payments
        Route::get('/payments', [\App\Http\Controllers\PaymentController::class, 'index']);
        Route::post('/payments/create', [\App\Http\Controllers\PaymentController::class, 'create']);
        Route::post('/payments/create-bulk', [\App\Http\Controllers\PaymentController::class, 'createBulk']);
        Route::post('/payments/verify', [\App\Http\Controllers\PaymentController::class, 'verify']);
        Route::get('/payments/receipts/{id}', [\App\Http\Controllers\PaymentController::class, 'receipt']);

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
        Route::put('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::put('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
        Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount']);
        Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);

        // Medical registration endpoints
        Route::post('/medical-registration/register', [\App\Http\Controllers\MedicalRegistrationController::class, 'register']);
        Route::post('/medical-registration/payment', [\App\Http\Controllers\MedicalRegistrationController::class, 'createPayment']);
        Route::post('/medical-registration/verify', [\App\Http\Controllers\MedicalRegistrationController::class, 'verifyPayment']);
        Route::post('/medical-registration/complete', [\App\Http\Controllers\MedicalRegistrationController::class, 'completeRegistration']);
        Route::get('/medical-registration/pending', [\App\Http\Controllers\MedicalRegistrationController::class, 'getPendingRegistrations']);
        Route::get('/medical-registration/receipt/{payment_id}', [\App\Http\Controllers\MedicalRegistrationController::class, 'getReceipt']);
    });

    // Public endpoints
    Route::get('/plans', [\App\Http\Controllers\PlanController::class, 'index']);
    Route::get('/plans/{id}', [\App\Http\Controllers\PlanController::class, 'show']);
    Route::get('/plans/{id}/commission-rates', [\App\Http\Controllers\PlanController::class, 'commissionRates']);
    Route::get('/hospitals', [\App\Http\Controllers\FacilityController::class, 'hospitals']);
    Route::get('/clinics', [\App\Http\Controllers\FacilityController::class, 'clinics']);
    Route::get('/hospitals/search', [\App\Http\Controllers\FacilityController::class, 'searchHospitals']);
    Route::get('/clinics/search', [\App\Http\Controllers\FacilityController::class, 'searchClinics']);
    
    // Public medical registration endpoints (for external registration)
    Route::post('/medical-registration/external/register', [\App\Http\Controllers\MedicalRegistrationController::class, 'register']);
    Route::post('/medical-registration/external/payment', [\App\Http\Controllers\MedicalRegistrationController::class, 'createPayment']);
    Route::post('/medical-registration/external/verify', [\App\Http\Controllers\MedicalRegistrationController::class, 'verifyPayment']);
    Route::post('/medical-registration/external/complete', [\App\Http\Controllers\MedicalRegistrationController::class, 'completeRegistration']);
    Route::get('/medical-registration/external/receipt/{payment_id}', [\App\Http\Controllers\MedicalRegistrationController::class, 'getReceipt']);
});


