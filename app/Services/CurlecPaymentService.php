<?php

namespace App\Services;

use App\Models\GatewayRecord;
use App\Models\InsurancePlan;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurlecPaymentService
{
    private string $baseUrl;
    private ?string $keyId;
    private ?string $keySecret;
    private bool $sandbox;

    public function __construct()
    {
        $this->baseUrl = config('services.curlec.base_url', 'https://api.curlec.com');
        $this->keyId = config('services.curlec.key_id');
        $this->keySecret = config('services.curlec.key_secret');
        $this->sandbox = (bool) config('services.curlec.sandbox', true);
    }

    public function createSubscriptionPlan(InsurancePlan $plan, string $interval = 'monthly'): array
    {
        $intervalMap = [
            'monthly' => 'monthly',
            'quarterly' => 'quarterly', 
            'semi_annually' => 'half_yearly',
            'annually' => 'yearly'
        ];

        // Calculate the correct amount based on interval
        $annualAmountCents = $plan->price_cents; // Already in cents
        $monthlyAmountCents = $annualAmountCents / 12;
        
        $amountCents = match($interval) {
            'quarterly' => $monthlyAmountCents * 3,
            'semi_annually' => $monthlyAmountCents * 6,
            'annually' => $annualAmountCents,
            default => $monthlyAmountCents // monthly
        };
        
        // Razorpay expects amount in cents for MYR currency
        $amount = $amountCents;

        $payload = [
            'item' => [
                'name' => $plan->name ?? 'Medical Insurance Plan',
                'description' => $plan->description ?? 'Medical Insurance Plan',
                'amount' => $amount, // Amount in cents for Razorpay
                'currency' => 'MYR'
            ],
            'period' => $intervalMap[$interval] ?? 'monthly',
            'interval' => 1,
            'notes' => [
                'plan_id' => $plan->id,
                'plan_slug' => $plan->slug ?? 'medical'
            ]
        ];

        // If no keys configured, return a mock response for local testing
        if (! $this->keyId || ! $this->keySecret) {
            return [
                'id' => 'plan_MOCK_' . $plan->id,
                'item' => $payload['item'],
                'period' => $payload['period'],
                'interval' => $payload['interval'],
                'status' => 'active'
            ];
        }

        $response = $this->makeRequest('POST', '/plans', $payload);
        
        // Store gateway record
        GatewayRecord::create([
            'provider' => 'curlec',
            'direction' => 'request',
            'external_ref' => $response['id'] ?? null,
            'payload' => $payload,
            'status_code' => 200,
        ]);

        return $response;
    }

    public function createSubscription(PaymentTransaction $payment, string $planId): array
    {
        $payload = [
            'plan_id' => $planId,
            'total_count' => 12, // 12 months subscription
            'quantity' => 1,
            'customer_notify' => 1,
            'start_at' => now()->addMinutes(1)->timestamp, // Start in 1 minute to avoid timing issues
            'expire_by' => now()->addYear()->timestamp,
            'notes' => [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'plan_id' => $payment->plan_id,
            ],
        ];

        // If no keys configured, return a mock response for local testing
        if (! $this->keyId || ! $this->keySecret) {
            return [
                'id' => 'sub_MOCK_' . $payment->id,
                'plan_id' => $planId,
                'status' => 'created',
                'current_start' => now()->timestamp,
                'current_end' => now()->addMonth()->timestamp,
                'ended_at' => null,
                'quantity' => 1,
                'notes' => $payload['notes']
            ];
        }

        $response = $this->makeRequest('POST', '/subscriptions', $payload);
        
        // Store gateway record
        GatewayRecord::create([
            'provider' => 'curlec',
            'direction' => 'request',
            'external_ref' => $response['id'] ?? null,
            'payload' => $payload,
            'status_code' => 200,
        ]);

        return $response;
    }

    public function createOrder(PaymentTransaction $payment): array
    {
        $payload = [
            'amount' => $payment->amount_cents, // Amount in cents for Razorpay
            'currency' => 'MYR',
            'receipt' => 'KHI-' . $payment->id,
            'notes' => [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id,
                'plan_id' => $payment->plan_id,
            ],
        ];

        // If no keys configured, return a mock response for local testing
        if (! $this->keyId || ! $this->keySecret) {
            return [
                'order_id' => 'MOCK-' . $payment->id,
                'amount' => $payload['amount'],
                'currency' => $payload['currency'],
                'checkout_url' => null,
            ];
        }

        $response = $this->makeRequest('POST', '/orders', $payload);
        
        // Store gateway record
        GatewayRecord::create([
            'provider' => 'curlec',
            'direction' => 'request',
            'external_ref' => $response['id'] ?? null,
            'payload' => $payload,
            'status_code' => 200,
        ]);

        return [
            'order_id' => $response['id'],
            'amount' => $response['amount'],
            'currency' => $response['currency'],
            'checkout_url' => $response['checkout_url'] ?? null,
        ];
    }

    public function verifyWebhook(array $payload, ?string $signature = null): bool
    {
        // In sandbox or if signature is empty, accept to ease local testing
        if ($this->sandbox || empty($signature)) {
            return true;
        }
        $expectedSignature = hash_hmac('sha256', json_encode($payload), $this->keySecret ?? '');
        return hash_equals($expectedSignature, $signature);
    }

    public function handleWebhook(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$orderId || !$status) {
            Log::warning('Invalid Curlec webhook payload', $payload);
            return;
        }

        // Store webhook record
        GatewayRecord::create([
            'provider' => 'curlec',
            'direction' => 'webhook',
            'external_ref' => $orderId,
            'payload' => $payload,
            'status_code' => 200,
        ]);

        // Find payment by order ID
        $payment = PaymentTransaction::where('external_ref', $orderId)
            ->orWhere('meta->order_id', $orderId)
            ->first();

        if (!$payment) {
            Log::warning('Payment not found for Curlec order', ['order_id' => $orderId]);
            return;
        }

        // Check if payment is already processed to prevent duplicate processing
        if ($payment->status === 'paid' && $status === 'paid') {
            Log::info('Payment already processed, skipping duplicate webhook', [
                'payment_id' => $payment->id,
                'order_id' => $orderId,
                'current_status' => $payment->status
            ]);
            return;
        }

        // Update payment status based on webhook status
        if ($status === 'paid') {
            // Use database transaction to ensure atomicity
            \DB::transaction(function () use ($payment) {
                // Double-check status hasn't changed during transaction
                $payment->refresh();
                if ($payment->status === 'paid') {
                    Log::info('Payment already processed in another request', [
                        'payment_id' => $payment->id
                    ]);
                    return;
                }

                // Update payment status
                $payment->status = 'paid';
                $payment->paid_at = now();
                $payment->save();

                // Update the agent who made the payment to have an agent code if they don't have one
                $agent = $payment->user;
                if ($agent && empty($agent->agent_code)) {
                    $seq = str_pad((string) (\App\Models\User::whereNotNull('agent_code')->count() + 1), 5, '0', STR_PAD_LEFT);
                    $updates = ['agent_code' => 'KH' . $seq];
                    if (\Schema::hasColumn('users', 'status')) {
                        $updates['status'] = 'active';
                    }
                    if (\Schema::hasColumn('users', 'mlm_activation_date')) {
                        $updates['mlm_activation_date'] = now();
                    }
                    $agent->update($updates);
                }

                // Trigger commission disbursement
                app(CommissionService::class)->disburseForPayment($payment);

                // Create payment notification
                try {
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->createPaymentNotification(
                        $agent->id,
                        $payment->amount_cents / 100, // Convert cents to dollars
                        'completed',
                        'Curlec',
                        $payment->id
                    );
                } catch (\Exception $e) {
                    Log::error("Failed to create payment notification: " . $e->getMessage());
                }

                // Log the payment verification
                Log::info('Payment verification completed via webhook', [
                    'payment_id' => $payment->id,
                    'agent_id' => $agent->id,
                    'agent_code' => $agent->agent_code,
                    'referrer_code' => $agent->referrer_code,
                ]);
            });
        } elseif ($status === 'failed') {
            $payment->status = 'failed';
            $payment->save();
            
            Log::info('Payment marked as failed via webhook', [
                'payment_id' => $payment->id,
                'order_id' => $orderId
            ]);
        }
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        Log::info('Making Curlec API request', [
            'method' => $method,
            'url' => $url,
            'endpoint' => $endpoint,
            'base_url' => $this->baseUrl,
            'key_id' => $this->keyId,
            'data' => $data
        ]);
        
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($this->keyId . ':' . $this->keySecret),
            'Content-Type' => 'application/json',
        ])->$method($url, $data);

        if (!$response->successful()) {
            Log::error('Curlec API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Curlec payment failed: ' . $response->body());
        }

        return $response->json();
    }
}
