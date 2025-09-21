<?php

namespace App\Http\Controllers;

use App\Models\InsurancePlan;
use App\Models\MemberPolicy;
use App\Models\PaymentTransaction;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\CurlecPaymentService;
use App\Services\NetworkLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MedicalRegistrationController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'clients' => 'required|array|min:1|max:10',
            'clients.*.plan_type' => 'required|string',
            'clients.*.full_name' => 'required|string|max:255',
            'clients.*.nric' => 'required|string|max:20',
            'clients.*.race' => 'required|string',
            'clients.*.height_cm' => 'required|numeric|min:50|max:300',
            'clients.*.weight_kg' => 'required|numeric|min:10|max:500',
            'clients.*.phone_number' => 'required|string|max:20',
            'clients.*.email' => 'required|email',
            'clients.*.password' => 'required|string|min:6',
            'clients.*.medical_consultation_2_years' => 'required|boolean',
            'clients.*.serious_illness_history' => 'required|boolean',
            'clients.*.insurance_rejection_history' => 'required|boolean',
            'clients.*.serious_injury_history' => 'required|boolean',
            'clients.*.emergency_contact_name' => 'required|string|max:255',
            'clients.*.emergency_contact_phone' => 'required|string|max:20',
            'clients.*.emergency_contact_relationship' => 'required|string|max:100',
            'clients.*.payment_mode' => 'required|in:monthly,quarterly,semi_annually,annually',
            'clients.*.medical_card_type' => 'required|string',
            'plan_id' => 'required|integer|exists:insurance_plans,id',
            'payment_mode' => 'required|in:monthly,quarterly,semi_annually,annually',
            'agent_code' => 'nullable|string|exists:users,agent_code', // For external registration
        ]);

        $agent = auth('api')->user();
        
        // For external registration, find agent by agent_code
        if (!$agent && isset($validated['agent_code']) && $validated['agent_code']) {
            $agent = User::where('agent_code', $validated['agent_code'])->first();
            if (!$agent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid agent code provided'
                ], 422);
            }
        }
        
        if (!$agent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required or valid agent code must be provided'
            ], 401);
        }
        $plan = InsurancePlan::find($validated['plan_id']);
        if (!$plan) {
            return response()->json(['status' => 'error', 'message' => 'Invalid plan ID'], 404);
        }

        try {
            // Calculate total amount and breakdown
            $totalAmountCents = 0;
            $breakdown = [];

            foreach ($validated['clients'] as $clientData) {
                $planAmountCents = $this->calculatePlanAmount($plan, $clientData['payment_mode']);
                $nfcCardFeeCents = $this->calculateNfcCardFee($clientData['medical_card_type']);
                $clientTotalCents = $planAmountCents + $nfcCardFeeCents;
                
                $totalAmountCents += $clientTotalCents;

                $breakdown[] = [
                    'client_name' => $clientData['full_name'],
                    'plan_name' => $plan->name,
                    'payment_mode' => $clientData['payment_mode'],
                    'amount_cents' => $planAmountCents,
                    'nfc_card_fee_cents' => $nfcCardFeeCents,
                    'line_total' => $clientTotalCents / 100,
                ];
            }

            // Create pending registration
            $registrationId = 'REG' . time() . rand(1000, 9999);
            $pendingRegistration = PendingRegistration::create([
                'registration_id' => $registrationId,
                'agent_id' => $agent->id,
                'plan_id' => $plan->id,
                'clients_data' => $validated['clients'],
                'amount_breakdown' => $breakdown,
                'total_amount_cents' => $totalAmountCents,
                'currency' => 'MYR',
                'status' => 'pending',
                'expires_at' => now()->addHours(24), // Expire in 24 hours
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Registration data prepared successfully',
                'data' => [
                    'registration_id' => $registrationId,
                    'total_amount' => $totalAmountCents / 100,
                    'amount_breakdown' => $breakdown,
                    'expires_at' => $pendingRegistration->expires_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Registration preparation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Registration preparation failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function registerOld(Request $request)
    {
        $validated = $request->validate([
            'clients' => 'required|array|min:1|max:10',
            'clients.*.plan_type' => 'required|string',
            'clients.*.full_name' => 'required|string|max:255',
            'clients.*.nric' => 'required|string|max:20',
            'clients.*.race' => 'required|string',
            'clients.*.height_cm' => 'required|numeric|min:50|max:300',
            'clients.*.weight_kg' => 'required|numeric|min:10|max:500',
            'clients.*.phone_number' => 'required|string|max:20',
            'clients.*.email' => 'required|email',
            'clients.*.password' => 'required|string|min:6',
            'clients.*.medical_consultation_2_years' => 'required|boolean',
            'clients.*.serious_illness_history' => 'required|boolean',
            'clients.*.insurance_rejection_history' => 'required|boolean',
            'clients.*.serious_injury_history' => 'required|boolean',
            'clients.*.emergency_contact_name' => 'required|string|max:255',
            'clients.*.emergency_contact_phone' => 'required|string|max:20',
            'clients.*.emergency_contact_relationship' => 'required|string|max:100',
            'clients.*.payment_mode' => 'required|in:monthly,quarterly,semi_annually,annually',
            'clients.*.medical_card_type' => 'required|string',
            'plan_id' => 'required|integer|exists:insurance_plans,id',
            'payment_mode' => 'required|in:monthly,quarterly,semi_annually,annually',
            'agent_code' => 'nullable|string|exists:users,agent_code', // For external registration
        ]);

        $agent = auth('api')->user();
        
        // For external registration, find agent by agent_code
        if (!$agent && isset($validated['agent_code']) && $validated['agent_code']) {
            $agent = User::where('agent_code', $validated['agent_code'])->first();
            if (!$agent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid agent code provided'
                ], 422);
            }
        }
        
        if (!$agent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required or valid agent code must be provided'
            ], 401);
        }
        $registrationId = time() . $agent->id;
        $clients = [];
        $policies = [];
        $totalAmountCents = 0;
        $breakdown = [];

        try {
            DB::transaction(function () use ($validated, $agent, &$clients, &$policies, &$totalAmountCents, &$breakdown) {
                foreach ($validated['clients'] as $clientData) {
                    // Find the insurance plan using the plan_id from the main payload
                    $plan = InsurancePlan::find($validated['plan_id']);
                    if (!$plan) {
                        throw new \Exception("Invalid plan ID: {$validated['plan_id']}");
                    }

                    // Generate unique email if not provided
                    $email = $clientData['email'] ?? $this->generateUniqueEmail($clientData['full_name']);

                    // Create the client user
                    $clientDataToCreate = [
                        'name' => $clientData['full_name'],
                        'email' => $email,
                        'phone_number' => $clientData['phone_number'],
                        'nric' => $clientData['nric'],
                        'race' => $clientData['race'],
                        'height_cm' => $clientData['height_cm'],
                        'weight_kg' => $clientData['weight_kg'],
                        'emergency_contact_name' => $clientData['emergency_contact_name'],
                        'emergency_contact_phone' => $clientData['emergency_contact_phone'],
                        'emergency_contact_relationship' => $clientData['emergency_contact_relationship'],
                        'password' => Hash::make($clientData['password']), // Use password from request
                        'referrer_code' => $agent->agent_code,
                        'medical_card_type' => $clientData['medical_card_type'], // Add medical card type
                        'payment_mode' => $clientData['payment_mode'], // Add payment mode
                        'current_payment_mode' => $clientData['payment_mode'], // Add current payment mode
                        'plan_name' => $plan->name, // Add plan name
                        'current_insurance_plan_id' => $plan->id, // Add plan ID
                    ];
                    if (\Schema::hasColumn('users', 'status')) {
                        $clientDataToCreate['status'] = 'pending_payment';
                    }
                    // Include optional medical flags only if columns exist
                    if (\Schema::hasColumn('users', 'medical_consultation_2_years')) {
                        $clientDataToCreate['medical_consultation_2_years'] = (bool) ($clientData['medical_consultation_2_years'] ?? false);
                    }
                    if (\Schema::hasColumn('users', 'serious_illness_history')) {
                        $clientDataToCreate['serious_illness_history'] = (bool) ($clientData['serious_illness_history'] ?? false);
                    }
                    if (\Schema::hasColumn('users', 'insurance_rejection_history')) {
                        $clientDataToCreate['insurance_rejection_history'] = (bool) ($clientData['insurance_rejection_history'] ?? false);
                    }
                    if (\Schema::hasColumn('users', 'serious_injury_history')) {
                        $clientDataToCreate['serious_injury_history'] = (bool) ($clientData['serious_injury_history'] ?? false);
                    }
                    // Filter keys to existing columns to avoid unknown column errors
                    $existing = collect(\Schema::getColumnListing('users'))->flip();
                    $safePayload = collect($clientDataToCreate)->filter(function ($v, $k) use ($existing) {
                        return $existing->has($k);
                    })->all();

                    $client = User::create($safePayload);

                    // Calculate policy dates
                    $startDate = now()->toDateString();
                    $endDate = $this->calculateEndDate($clientData['payment_mode']);
                    $nextPaymentDue = $this->calculateNextPaymentDue($startDate, $clientData['payment_mode']);

                    // Create policy for the client
                    $policy = MemberPolicy::create([
                        'user_id' => $client->id,
                        'plan_id' => $plan->id,
                        'policy_number' => 'POL' . time() . $client->id,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'pending',
                        'auto_renew' => true,
                    ]);

                    // Update user with policy information
                    $client->update([
                        'policy_start_date' => $startDate,
                        'policy_end_date' => $endDate,
                        'next_payment_due' => $nextPaymentDue,
                        'policy_status' => 'pending',
                        'premium_amount' => $this->calculatePlanAmount($plan, $clientData['payment_mode']),
                    ]);

                    $clients[] = $client;
                    $policies[] = $policy;

                    // Calculate amount based on payment mode
                    $planAmountCents = $this->calculatePlanAmount($plan, $clientData['payment_mode']);
                    
                    // Add NFC card fee if physical card is selected
                    $nfcCardFeeCents = $this->calculateNfcCardFee($clientData['medical_card_type']);
                    $clientTotalCents = $planAmountCents + $nfcCardFeeCents;
                    
                    $totalAmountCents += $clientTotalCents;

                    $breakdown[] = [
                        'client_name' => $client->name,
                        'plan_name' => $plan->name,
                        'payment_mode' => $clientData['payment_mode'],
                        'amount_cents' => $planAmountCents,
                        'nfc_card_fee_cents' => $nfcCardFeeCents,
                        'line_total' => $clientTotalCents / 100,
                    ];
                }
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'registration_id' => $registrationId,
                    'clients' => $clients,
                    'policies' => $policies,
                    'total_amount' => $totalAmountCents / 100,
                    'amount_breakdown' => $breakdown,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Medical registration failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function createPayment(Request $request, CurlecPaymentService $curlecService)
    {
        $validated = $request->validate([
            'registration_id' => 'required|string|exists:pending_registrations,registration_id',
            'payment_method' => 'required|string',
            'return_url' => 'nullable|string',
            'cancel_url' => 'nullable|string',
        ]);

        try {
            Log::info('Creating payment for registration ID: ' . $validated['registration_id']);
            
            $pendingRegistration = PendingRegistration::where('registration_id', $validated['registration_id'])->first();
            if (!$pendingRegistration) {
                return response()->json(['status' => 'error', 'message' => 'Registration not found'], 404);
            }

            if ($pendingRegistration->isExpired()) {
                return response()->json(['status' => 'error', 'message' => 'Registration has expired'], 400);
            }

            if ($pendingRegistration->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Registration already processed'], 400);
            }

            $totalAmountCents = $pendingRegistration->total_amount_cents;
            $plan = $pendingRegistration->plan;

            // For external registrations, use the agent as the payment user
            $paymentUserId = auth('api')->id();
            if (!$paymentUserId) {
                $paymentUserId = $pendingRegistration->agent_id;
            }

            // Create a consolidated payment transaction
            $payment = PaymentTransaction::create([
                'user_id' => $paymentUserId,
                'plan_id' => $plan->id,
                'gateway' => 'curlec',
                'amount_cents' => $totalAmountCents,
                'currency' => 'MYR',
                'status' => 'pending',
                'meta' => [
                    'registration_id' => $validated['registration_id'],
                    'pending_registration_id' => $pendingRegistration->id,
                ],
            ]);

            // Create Curlec one-time payment (same as continue payment)
            try {
                // Create one-time order instead of subscription
                Log::info('Creating one-time order for payment ID: ' . $payment->id . ', amount: ' . $totalAmountCents);
                $order = $curlecService->createOrder($payment);
                Log::info('Order created: ' . json_encode($order));
                
                if (!isset($order['order_id'])) {
                    throw new \Exception('Failed to create order: ' . json_encode($order));
                }
                
                $payment->update([
                    'external_ref' => $order['order_id'],
                    'meta' => array_merge($payment->meta, [
                        'curlec_order' => $order
                    ])
                ]);

                $checkoutData = [
                    'payment_id' => $payment->id,
                    'amount' => $totalAmountCents / 100,
                    'currency' => 'MYR',
                    'order_id' => $order['order_id'],
                    'checkout_url' => null, // Will be handled by frontend
                ];
            } catch (\Exception $e) {
                Log::warning('Curlec order creation failed, using mock: ' . $e->getMessage());
                
                $checkoutData = [
                    'payment_id' => $payment->id,
                    'amount' => $totalAmountCents / 100,
                    'currency' => 'MYR',
                    'order_id' => 'order_MOCK_' . $payment->id,
                    'checkout_url' => null,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'payment' => $payment,
                    'checkout_data' => $checkoutData,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Payment creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Payment creation failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function verifyPayment(Request $request, CommissionService $commissionService)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payment_transactions,id',
            'status' => 'required|in:success,failed',
            'external_ref' => 'nullable|string',
            'order_id' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $commissionService) {
                $payment = PaymentTransaction::findOrFail($validated['payment_id']);
                
                if ($validated['status'] === 'success') {
                    // Update payment status
                    $payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'external_ref' => $validated['external_ref'] ?? $payment->external_ref,
                    ]);

                    // Complete the registration by creating users and policies
                    if (isset($payment->meta['pending_registration_id'])) {
                        $pendingRegistration = PendingRegistration::find($payment->meta['pending_registration_id']);
                        if ($pendingRegistration && $pendingRegistration->status === 'pending') {
                            // Complete registration from payment
                            $this->completeRegistrationFromPayment($pendingRegistration, $commissionService);
                        }
                    }

                    // Update the agent who made the payment to have an agent code if they don't have one
                    $agent = $payment->user;
                    if ($agent && empty($agent->agent_code)) {
                        $updates = ['agent_code' => $this->generateAgentCode()];
                        if (\Schema::hasColumn('users', 'status')) {
                            $updates['status'] = 'active';
                        }
                        if (\Schema::hasColumn('users', 'mlm_activation_date')) {
                            $updates['mlm_activation_date'] = now();
                        }
                        $agent->update($updates);
                    }

                    // Commission disbursement is handled in completeRegistrationFromPayment

                    // Log the payment verification
                    Log::info('Payment verification completed', [
                        'payment_id' => $payment->id,
                        'agent_id' => $agent->id,
                        'agent_code' => $agent->agent_code,
                        'referrer_code' => $agent->referrer_code,
                    ]);

                } else {
                    // Update payment as failed
                    $payment->update(['status' => 'failed']);
                    
                    // Update related policies to failed
                    if (isset($payment->meta['policy_ids'])) {
                        MemberPolicy::whereIn('id', $payment->meta['policy_ids'])
                            ->update(['status' => 'failed']);
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verification completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 422);
        }
    }

    public function getReceipt(int $paymentId)
    {
        try {
            $payment = PaymentTransaction::with(['user', 'plan'])->findOrFail($paymentId);
            
            $receiptData = [
                'payment_id' => $payment->id,
                'payment_date' => $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : null,
                'amount' => $payment->amount_cents / 100,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'agent_name' => $payment->user->name,
                'agent_email' => $payment->user->email,
                'breakdown' => [],
            ];

            // Get client details from policy IDs in meta
            if (isset($payment->meta['policy_ids'])) {
                $policies = MemberPolicy::whereIn('id', $payment->meta['policy_ids'])
                    ->with(['user', 'plan'])
                    ->get();

                foreach ($policies as $policy) {
                    $receiptData['breakdown'][] = [
                        'client_name' => $policy->user->name,
                        'client_email' => $policy->user->email,
                        'plan_name' => $policy->plan->name,
                        'policy_number' => $policy->policy_number,
                        'start_date' => $policy->start_date,
                        'end_date' => $policy->end_date,
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => ['receipt' => $receiptData]
            ]);

        } catch (\Exception $e) {
            Log::error('Receipt generation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Receipt not found'
            ], 404);
        }
    }

    private function generateUniqueEmail(string $name): string
    {
        $baseEmail = strtolower(str_replace(' ', '.', $name)) . '@generated.local';
        $counter = 1;
        $email = $baseEmail;
        
        while (User::where('email', $email)->exists()) {
            $email = strtolower(str_replace(' ', '.', $name)) . $counter . '@generated.local';
            $counter++;
        }
        
        return $email;
    }

    private function calculateEndDate(string $paymentMode): string
    {
        $startDate = now();
        
        switch ($paymentMode) {
            case 'monthly':
                return $startDate->addMonth()->toDateString();
            case 'quarterly':
                return $startDate->addMonths(3)->toDateString();
            case 'semi_annually':
                return $startDate->addMonths(6)->toDateString();
            case 'annually':
                return $startDate->addYear()->toDateString();
            default:
                return $startDate->addYear()->toDateString();
        }
    }

    private function calculatePlanAmount(InsurancePlan $plan, string $paymentMode): int
    {
        // Base annual amount
        $annualAmountCents = $plan->price_cents ?? 0;
        
        switch ($paymentMode) {
            case 'monthly':
                // Monthly amount only (commitment fee is separate)
                return intval($annualAmountCents / 12);
            case 'quarterly':
                return intval($annualAmountCents / 4);
            case 'semi_annually':
                return intval($annualAmountCents / 2);
            case 'annually':
                return $annualAmountCents;
            default:
                return $annualAmountCents;
        }
    }

    private function calculateNextPaymentDue($startDate, $paymentMode)
    {
        $start = \Carbon\Carbon::parse($startDate);
        $now = \Carbon\Carbon::now();
        
        switch ($paymentMode) {
            case 'monthly':
                // Find next monthly payment
                $nextDue = $start->copy();
                while ($nextDue->lte($now)) {
                    $nextDue->addMonth();
                }
                return $nextDue->toDateString();
            case 'quarterly':
                // Find next quarterly payment
                $nextDue = $start->copy();
                while ($nextDue->lte($now)) {
                    $nextDue->addMonths(3);
                }
                return $nextDue->toDateString();
            case 'semi_annually':
                // Find next semi-annual payment
                $nextDue = $start->copy();
                while ($nextDue->lte($now)) {
                    $nextDue->addMonths(6);
                }
                return $nextDue->toDateString();
            case 'annually':
                // Find next annual payment
                $nextDue = $start->copy();
                while ($nextDue->lte($now)) {
                    $nextDue->addYear();
                }
                return $nextDue->toDateString();
            default:
                return null;
        }
    }

    private function calculateNfcCardFee($medicalCardType)
    {
        if ($medicalCardType === 'e-Medical Card & Fizikal Medical Card dengan fungsi NFC Touch n Go (RRP RM34.90)') {
            return 3490; // RM 34.90 in cents
        }
        return 0; // No fee for e-Medical Card only
    }

    private function generateAgentCode(): string
    {
        $sequence = User::whereNotNull('agent_code')->count() + 1;
        // Enforce 5-digit suffix (AGT00001)
        return 'AGT' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Complete registration from payment verification
     */
    private function completeRegistrationFromPayment(PendingRegistration $pendingRegistration, CommissionService $commissionService)
    {
        $clients = [];
        $policies = [];

        $agent = $pendingRegistration->agent;
        $plan = $pendingRegistration->plan;

        foreach ($pendingRegistration->clients_data as $clientData) {
            // Generate unique email if not provided
            $email = $clientData['email'] ?? $this->generateUniqueEmail($clientData['full_name']);

            // Create the client user
            $clientDataToCreate = [
                'name' => $clientData['full_name'],
                'email' => $email,
                'phone_number' => $clientData['phone_number'],
                'nric' => $clientData['nric'],
                'race' => $clientData['race'],
                'height_cm' => $clientData['height_cm'],
                'weight_kg' => $clientData['weight_kg'],
                'emergency_contact_name' => $clientData['emergency_contact_name'],
                'emergency_contact_phone' => $clientData['emergency_contact_phone'],
                'emergency_contact_relationship' => $clientData['emergency_contact_relationship'],
                'password' => Hash::make($clientData['password']),
                'referrer_code' => $agent->agent_code,
                'medical_card_type' => $clientData['medical_card_type'],
                'payment_mode' => $clientData['payment_mode'],
                'current_payment_mode' => $clientData['payment_mode'],
                'plan_name' => $plan->name,
                'current_insurance_plan_id' => $plan->id,
            ];

            $client = User::create($clientDataToCreate);

            // Set policy dates
            $startDate = now()->toDateString();
            $endDate = $this->calculateEndDate($clientData['payment_mode']);
            $nextPaymentDue = $this->calculateNextPaymentDue($startDate, $clientData['payment_mode']);

            // Create policy for the client
            $policy = MemberPolicy::create([
                'user_id' => $client->id,
                'plan_id' => $plan->id,
                'policy_number' => 'POL' . time() . $client->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active', // Set to active after successful payment
                'auto_renew' => true,
            ]);

            // Update user with policy information
            $client->update([
                'policy_start_date' => $startDate,
                'policy_end_date' => $endDate,
                'next_payment_due' => $nextPaymentDue,
                'policy_status' => 'active',
                'premium_amount' => $this->calculatePlanAmount($plan, $clientData['payment_mode']),
            ]);

            $clients[] = $client;
            $policies[] = $policy;
        }

        // Mark registration as completed
        $pendingRegistration->markAsCompleted();

        // Calculate and disburse commissions for the payment
        $payment = PaymentTransaction::where('meta->pending_registration_id', $pendingRegistration->id)->first();
        if ($payment) {
            $commissionService->disburseForPayment($payment);
        }

        // Calculate network levels for all agents after new clients are added
        try {
            $networkLevelService = new NetworkLevelService();
            
            // Recalculate for the agent who registered the clients
            $networkLevelService->calculateNetworkLevelsForAgent($agent->agent_code);
            
            // Recalculate for all agents in the network to ensure consistency
            $allAgents = User::whereNotNull('agent_code')->get();
            foreach ($allAgents as $agentUser) {
                $networkLevelService->calculateNetworkLevelsForAgent($agentUser->agent_code);
            }
        } catch (\Exception $e) {
            Log::error("Failed to calculate network levels after client registration: " . $e->getMessage());
        }

        return ['clients' => $clients, 'policies' => $policies];
    }

    /**
     * Get pending registrations for an agent
     */
    public function getPendingRegistrations(Request $request)
    {
        $user = auth('api')->user();
        
        if (!$user || !$user->agent_code) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found or no agent code'
            ], 404);
        }

        $pendingRegistrations = PendingRegistration::where('agent_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with(['plan'])
            ->orderBy('created_at', 'desc')
            ->get();

        $transformedRegistrations = $pendingRegistrations->map(function ($registration) {
            return [
                'id' => $registration->id,
                'registration_id' => $registration->registration_id,
                'plan_name' => $registration->plan->name,
                'total_amount' => $registration->total_amount_cents / 100,
                'currency' => $registration->currency,
                'clients_count' => count($registration->clients_data),
                'clients_data' => $registration->clients_data,
                'amount_breakdown' => $registration->amount_breakdown,
                'created_at' => $registration->created_at,
                'expires_at' => $registration->expires_at,
                'status' => $registration->status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $transformedRegistrations,
                'total' => $transformedRegistrations->count(),
            ]
        ]);
    }

    /**
     * Complete registration after successful payment
     */
    public function completeRegistration(Request $request)
    {
        $validated = $request->validate([
            'registration_id' => 'required|string|exists:pending_registrations,registration_id',
            'payment_id' => 'required|integer|exists:payment_transactions,id',
        ]);

        try {
            $pendingRegistration = PendingRegistration::where('registration_id', $validated['registration_id'])->first();
            
            if (!$pendingRegistration) {
                return response()->json(['status' => 'error', 'message' => 'Registration not found'], 404);
            }

            if ($pendingRegistration->isExpired()) {
                return response()->json(['status' => 'error', 'message' => 'Registration has expired'], 400);
            }

            if ($pendingRegistration->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Registration already processed'], 400);
            }

            $clients = [];
            $policies = [];

            DB::transaction(function () use ($pendingRegistration, &$clients, &$policies) {
                $agent = $pendingRegistration->agent;
                $plan = $pendingRegistration->plan;

                foreach ($pendingRegistration->clients_data as $clientData) {
                    // Generate unique email if not provided
                    $email = $clientData['email'] ?? $this->generateUniqueEmail($clientData['full_name']);

                    // Create the client user
                    $clientDataToCreate = [
                        'name' => $clientData['full_name'],
                        'email' => $email,
                        'phone_number' => $clientData['phone_number'],
                        'nric' => $clientData['nric'],
                        'race' => $clientData['race'],
                        'height_cm' => $clientData['height_cm'],
                        'weight_kg' => $clientData['weight_kg'],
                        'emergency_contact_name' => $clientData['emergency_contact_name'],
                        'emergency_contact_phone' => $clientData['emergency_contact_phone'],
                        'emergency_contact_relationship' => $clientData['emergency_contact_relationship'],
                        'password' => Hash::make($clientData['password']),
                        'referrer_code' => $agent->agent_code,
                        'medical_card_type' => $clientData['medical_card_type'],
                        'payment_mode' => $clientData['payment_mode'],
                        'current_payment_mode' => $clientData['payment_mode'],
                        'plan_name' => $plan->name,
                        'current_insurance_plan_id' => $plan->id,
                    ];

                    $client = User::create($clientDataToCreate);

                    // Set policy dates
                    $startDate = now()->toDateString();
                    $endDate = $this->calculateEndDate($clientData['payment_mode']);
                    $nextPaymentDue = $this->calculateNextPaymentDue($startDate, $clientData['payment_mode']);

                    // Create policy for the client
                    $policy = MemberPolicy::create([
                        'user_id' => $client->id,
                        'plan_id' => $plan->id,
                        'policy_number' => 'POL' . time() . $client->id,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'active', // Set to active after successful payment
                        'auto_renew' => true,
                    ]);

                    // Update user with policy information
                    $client->update([
                        'policy_start_date' => $startDate,
                        'policy_end_date' => $endDate,
                        'next_payment_due' => $nextPaymentDue,
                        'policy_status' => 'active',
                        'premium_amount' => $this->calculatePlanAmount($plan, $clientData['payment_mode']),
                    ]);

                    $clients[] = $client;
                    $policies[] = $policy;
                }

                // Mark registration as completed
                $pendingRegistration->markAsCompleted();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Registration completed successfully',
                'data' => [
                    'clients' => $clients,
                    'policies' => $policies,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Registration completion failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Registration completion failed: ' . $e->getMessage()
            ], 422);
        }
    }
}
