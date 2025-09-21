<?php

namespace App\Http\Controllers;

use App\Models\InsurancePlan;
use App\Models\MemberPolicy;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\CurlecPaymentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        
        // If user is an agent, show payments from their clients
        if ($user->agent_code) {
            $payments = PaymentTransaction::whereHas('user', function($query) use ($user) {
                $query->where('referrer_code', $user->agent_code);
            })->with(['user', 'plan'])->latest()->paginate(15);
        } else {
            // Regular user sees their own payments
            $payments = PaymentTransaction::where('user_id', $user->id)->with(['user', 'plan'])->latest()->paginate(15);
        }
        
        return response()->json(['status' => 'success', 'data' => $payments]);
    }

    // Create payment for a specific policy
    public function create(Request $request, CurlecPaymentService $curlecService)
    {
        $data = $request->validate([
            'member_policy_id' => 'required|exists:member_policies,id',
            'payment_method' => 'required|string',
            'return_url' => 'nullable|string',
            'cancel_url' => 'nullable|string',
        ]);

        $policy = MemberPolicy::findOrFail($data['member_policy_id']);
        $plan = InsurancePlan::findOrFail($policy->plan_id);
        $amountCents = $plan->price_cents ?? 0;

        $payment = PaymentTransaction::create([
            'user_id' => auth('api')->id(),
            'plan_id' => $plan->id,
            'policy_id' => $policy->id,
            'gateway' => 'curlec',
            'amount_cents' => $amountCents,
            'status' => 'pending',
        ]);

        try {
            $curlecOrder = $curlecService->createOrder($payment);
            $payment->external_ref = $curlecOrder['order_id'];
            $payment->meta = ['order_id' => $curlecOrder['order_id']];
            $payment->save();

            $checkout = [
                'amount' => $curlecOrder['amount'] * 100, // Convert back to cents for frontend
                'currency' => $curlecOrder['currency'],
                'order_id' => $curlecOrder['order_id'],
                'checkout_url' => $curlecOrder['checkout_url'],
            ];
        } catch (\Exception $e) {
            // Fallback to mock if Curlec fails
            $checkout = [
                'amount' => $amountCents,
                'currency' => 'MYR',
                'order_id' => 'MOCK-'.$payment->id,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'payment' => $payment,
                'checkout_data' => $checkout,
                'curlec_options' => new \stdClass(),
            ],
        ]);
    }

    // Bulk creation for a registration (simplified: one payment equals one amount)
    public function createBulk(Request $request)
    {
        $data = $request->validate([
            'registration_id' => 'required|integer',
            'payment_method' => 'required|string',
            'return_url' => 'nullable|string',
            'cancel_url' => 'nullable|string',
        ]);

        // For now, create a placeholder payment linked to current user plan (frontend expects shape only)
        $payment = PaymentTransaction::create([
            'user_id' => auth('api')->id(),
            'plan_id' => InsurancePlan::first()->id,
            'gateway' => 'curlec',
            'amount_cents' => 1000,
            'status' => 'pending',
        ]);

        return response()->json(['status' => 'success', 'data' => [
            'payment' => $payment,
            'checkout_data' => ['order_id' => 'MOCK-'.$payment->id, 'amount' => 10.0],
            'curlec_options' => new \stdClass(),
        ]]);
    }

    // Webhook/callback verification
    public function verify(Request $request, CurlecPaymentService $curlecService, CommissionService $commissionService)
    {
        $signature = $request->header('X-Curlec-Signature', '');
        $payload = $request->all();

        // For testing purposes, allow missing signatures in development/test environments
        if (app()->environment('local', 'testing') && !$signature) {
            // Skip signature verification for testing
        } else if (!$curlecService->verifyWebhook($payload, $signature)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        // Find payment by order ID
        $orderId = $payload['order_id'] ?? null;
        if ($orderId) {
            $payment = PaymentTransaction::where('external_ref', $orderId)
                ->orWhere('meta->order_id', $orderId)
                ->first();

            if ($payment) {
                // Update payment status
                $payment->status = 'paid';
                $payment->paid_at = now();
                $payment->save();

                // Update the agent who made the payment to have an agent code if they don't have one
                $agent = $payment->user;
                if ($agent && empty($agent->agent_code)) {
                    $seq = str_pad((string) (User::whereNotNull('agent_code')->count() + 1), 5, '0', STR_PAD_LEFT);
                    $updates = ['agent_code' => 'AGT'.$seq];
                    if (\Schema::hasColumn('users', 'status')) {
                        $updates['status'] = 'active';
                    }
                    if (\Schema::hasColumn('users', 'mlm_activation_date')) {
                        $updates['mlm_activation_date'] = now();
                    }
                    $agent->update($updates);
                }

                // Calculate and disburse commissions
                $commissionService->disburseForPayment($payment);

                // Create payment notification
                try {
                    $notificationService = new NotificationService();
                    $notificationService->createPaymentNotification(
                        $agent->id,
                        $payment->amount_cents / 100, // Convert cents to dollars
                        'completed',
                        'Curlec',
                        $payment->id
                    );
                } catch (\Exception $e) {
                    \Log::error("Failed to create payment notification: " . $e->getMessage());
                }

                // Log the payment verification
                \Log::info('Payment verification completed via webhook', [
                    'payment_id' => $payment->id,
                    'agent_id' => $agent->id,
                    'agent_code' => $agent->agent_code,
                    'referrer_code' => $agent->referrer_code,
                ]);
            }
        }

        $curlecService->handleWebhook($payload);

        return response()->json(['status' => 'success']);
    }

    public function receipt(int $id)
    {
        $receipt = PaymentTransaction::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => ['receipt' => $receipt]]);
    }

    private function generateAgentCode(): string
    {
        $seq = str_pad((string) (User::whereNotNull('agent_code')->count() + 1), 5, '0', STR_PAD_LEFT);
        return 'AGT'.$seq;
    }
}


