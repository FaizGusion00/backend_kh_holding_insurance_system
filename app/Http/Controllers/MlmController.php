<?php

namespace App\Http\Controllers;

use App\Models\InsurancePlan;
use App\Models\User;
use App\Models\NetworkLevel;
use App\Services\NetworkLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MlmController extends Controller
{
    public function network(Request $request, NetworkLevelService $networkLevelService)
    {
        $user = auth('api')->user();
        
        if (!$user || !$user->agent_code) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found or no agent code'
            ], 404);
        }
        
        $level = $request->get('level', 5); // Default to 5 levels
        
        // Get network members using the new system - only downlines (level > 1)
        $allMembers = $networkLevelService->getNetworkMembers($user->agent_code, null);
        
        // Filter out the current user (level 1) - only show downlines
        $downlineMembers = $allMembers->where('level', '>', 1);
        
        // Transform to the expected format
        $transformedMembers = $downlineMembers->map(function ($networkLevel) {
            $user = $networkLevel->user;
            
            // Get the user's wallet balance
            $wallet = \App\Models\AgentWallet::where('user_id', $user->id)->first();
            $balance = $wallet ? $wallet->balance_cents / 100 : 0;
            
            return (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nric' => $user->nric,
                'agent_code' => $user->agent_code,
                'phone_number' => $user->phone_number,
                'balance' => $balance,
                'created_at' => $user->created_at,
                'mlm_level' => $networkLevel->level,
                'registration_date' => $user->created_at,
                'status' => $this->getUserStatus($user),
                'active_policies_count' => $networkLevel->active_policies_count,
                'total_commission_earned' => $networkLevel->commission_earned,
                'downline_count' => $networkLevel->direct_downlines_count,
                'referrer_code' => $user->referrer_code,
            ];
        });
        
        // Separate direct referrals (level 2) from the rest
        $directReferrals = $transformedMembers->where('mlm_level', 2);
        
        // Get level breakdown - exclude level 1 (current user)
        $levelBreakdown = $networkLevelService->getLevelBreakdown($user->agent_code);
        
        // Get total commission for current user
        $totalCommission = $this->getTotalCommissionForUser($user->id);
        
        return response()->json([
            'status' => 'success', 
            'data' => [
                'network_members' => $transformedMembers->values(),
                'total_members' => $transformedMembers->count(),
                'direct_referrals' => $directReferrals->values(),
                'direct_referrals_count' => $directReferrals->count(),
                'total_downlines' => $transformedMembers->where('mlm_level', '>', 2)->count(),
                'total_commission' => $totalCommission,
                'level_breakdown' => [
                    'level_1' => 0, // Always 0 since we don't show current user
                    'level_2' => $levelBreakdown[2] ?? 0,
                    'level_3' => $levelBreakdown[3] ?? 0,
                    'level_4' => $levelBreakdown[4] ?? 0,
                    'level_5' => $levelBreakdown[5] ?? 0,
                ]
            ]
        ]);
    }

    private function getDownlinesRecursive($agentCode, $levels, $currentLevel = 1, $visited = [])
    {
        if ($levels <= 0 || in_array($agentCode, $visited)) return collect();
        
        // Add current agent code to visited to prevent infinite loops
        $visited[] = $agentCode;
        
        $selectFields = ['id', 'name', 'email', 'agent_code', 'phone_number', 'created_at', 'referrer_code'];
        if (Schema::hasColumn('users', 'status')) {
            $selectFields[] = 'status';
        }
        
        // Get users at the current level who are referred by the given agent code
        $currentLevelUsers = User::where('referrer_code', $agentCode)
            ->whereNotNull('agent_code') // Only users with agent codes
            ->select($selectFields)
            ->get()
            ->map(function ($m) use ($currentLevel, $agentCode) {
                // Calculate the correct MLM level for this user
                $correctLevel = $this->calculateUserMlmLevel($m->id, $agentCode);
                
                // Only include users that are actually at the correct level
                if ($correctLevel !== $currentLevel) {
                    return null;
                }
                
                // Calculate commission earned from this user's network
                $commissionEarned = \App\Models\CommissionTransaction::where('earner_user_id', auth('api')->id())
                    ->where('source_user_id', $m->id)
                    ->where('status', 'posted')
                    ->sum('commission_cents') / 100;
                
                // Count active policies
                $activePoliciesCount = \App\Models\MemberPolicy::where('user_id', $m->id)
                    ->where('status', 'active')
                    ->count();
                
                // Count direct downlines
                $downlineCount = User::where('referrer_code', $m->agent_code)->count();
                
                $m->mlm_level = $correctLevel;
                $m->registration_date = $m->created_at;
                $m->status = $m->status ?? 'active';
                $m->active_policies_count = $activePoliciesCount;
                $m->total_commission_earned = $commissionEarned;
                $m->downline_count = $downlineCount;
                return $m;
            })
            ->filter(); // Remove null entries

        // Recursively get deeper levels
        $deeperDownlines = collect();
        foreach ($currentLevelUsers as $user) {
            if ($user->agent_code && !in_array($user->agent_code, $visited)) {
            $deeperDownlines = $deeperDownlines->concat(
                    $this->getDownlinesRecursive($user->agent_code, $levels - 1, $currentLevel + 1, $visited)
            );
            }
        }

        return $currentLevelUsers->concat($deeperDownlines);
    }

    public function commissionHistory(Request $request)
    {
        $user = auth('api')->user();
        $perPage = $request->get('per_page', 15);
        $from = $request->get('from');
        $to = $request->get('to');
        
        $query = \App\Models\CommissionTransaction::where('earner_user_id', $user->id);
        
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        
        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return response()->json(['status' => 'success', 'data' => $transactions]);
    }

    public function levelSummary()
    {
        $user = auth('api')->user();
        
        // Get counts for each level
        $levelCounts = [];
        for ($level = 1; $level <= 5; $level++) {
            $count = $this->getDownlinesCount($user->agent_code, $level);
            $levelCounts["level_{$level}"] = $count;
        }
        
        return response()->json([
            'status' => 'success', 
            'data' => [
                'level_summary' => $levelCounts,
                'total_network' => array_sum($levelCounts)
            ]
        ]);
    }

    private function getDownlinesCount($agentCode, $level)
    {
        if ($level <= 0) return 0;
        
        $directCount = User::where('referrer_code', $agentCode)->count();
        
        if ($level == 1) return $directCount;
        
        $totalCount = 0;
        $directDownlines = User::where('referrer_code', $agentCode)->get();
        
        foreach ($directDownlines as $downline) {
            $totalCount += $this->getDownlinesCount($downline->agent_code, $level - 1);
        }
        
        return $totalCount;
    }

    public function getReferrals()
    {
        return $this->network(request());
    }

    public function getDownlines($level = 2)
    {
        $request = request();
        $request->merge(['level' => $level]);
        return $this->network($request);
    }

    public function getCommissionSummary()
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $totalEarnedCents = \App\Models\CommissionTransaction::where('earner_user_id', $user->id)
            ->where('status', 'posted')
            ->sum('commission_cents');
            
        $pendingAmountCents = \App\Models\CommissionTransaction::where('earner_user_id', $user->id)
            ->where('status', 'pending')
            ->sum('commission_cents');
            
        // Calculate monthly commission (current month)
        $monthlyCommissionCents = \App\Models\CommissionTransaction::where('earner_user_id', $user->id)
            ->where('status', 'posted')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('commission_cents');
            
        return response()->json([
            'status' => 'success',
            'data' => [
                'total_earned' => ($totalEarnedCents ?? 0) / 100,
                'pending_amount' => ($pendingAmountCents ?? 0) / 100,
                'total_commission' => (($totalEarnedCents ?? 0) + ($pendingAmountCents ?? 0)) / 100,
                'monthly_commission' => ($monthlyCommissionCents ?? 0) / 100
            ]
        ]);
    }

    /**
     * Calculate the correct MLM level for a user based on their position in the referral chain
     */
    private function calculateUserMlmLevel($userId, $rootAgentCode)
    {
        $user = User::find($userId);
        if (!$user || !$user->agent_code) return 0;
        
        $level = 1;
        $currentCode = $user->referrer_code;
        
        // Walk up the referral chain until we reach the root agent
        while ($currentCode && $currentCode !== $rootAgentCode) {
            $referrer = User::where('agent_code', $currentCode)->first();
            if ($referrer && $referrer->referrer_code) {
                $level++;
                $currentCode = $referrer->referrer_code;
            } else {
                break;
            }
        }
        
        // If we didn't reach the root agent, this user is not in our network
        if ($currentCode !== $rootAgentCode) {
            return 0;
        }
        
        return $level;
    }

    public function registerClient(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
        ]);
        $user = auth('api')->user();
        $client = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt('password'),
            'referrer_code' => $user->agent_code,
        ]);
        return response()->json(['status' => 'success', 'data' => ['client' => $client]]);
    }

    public function registerBulkClients(Request $request)
    {
        $validated = $request->validate([
            'clients' => 'required|array|min:1',
            'clients.*.name' => 'required|string',
            'clients.*.email' => 'required|email',
            'clients.*.insurance_plan_id' => 'required|integer',
        ]);

        $registrationId = now()->timestamp;
        return response()->json(['status' => 'success', 'data' => [
            'registration_id' => $registrationId,
            'clients' => $validated['clients'],
            'policies' => [],
            'total_amount' => 0,
        ]]);
    }

    public function getMedicalClients(Request $request)
    {
        $user = auth('api')->user();
        $perPage = $request->get('per_page', 15);
        
        // Get users who have medical policies under this agent
        $clients = \App\Models\MemberPolicy::with(['user', 'plan'])
            ->whereHas('user', function($query) use ($user) {
                $query->where('referrer_code', $user->agent_code);
            })
            ->paginate($perPage);
            
        // Transform the data to match frontend expectations
        $transformedClients = $clients->map(function($policy) {
            // Get user's payment mode, default to monthly if not set
            $paymentMode = $policy->user->payment_mode ?? $policy->user->current_payment_mode ?? 'monthly';
            
            return [
                'id' => $policy->user->id,
                'name' => $policy->user->name,
                'full_name' => $policy->user->name, // Add full_name for consistency
                'email' => $policy->user->email,
                'phone_number' => $policy->user->phone_number,
                'nric' => $policy->user->nric,
                'plan' => $policy->plan->name ?? 'Medical Card',
                'plan_name' => $policy->plan->name ?? 'Medical Card',
                'status' => $policy->status,
                'payment_status' => $policy->status === 'active' ? 'Paid' : 'Pending',
                'amount' => $policy->plan ? $this->calculatePlanAmount($policy->plan, $paymentMode) : 0,
                'payment_mode' => $paymentMode, // Add payment mode for period display
                'created_at' => $policy->created_at,
                'card_type' => 'e-Medical Card',
                'medical_card_type' => $policy->user->medical_card_type ?? 'e-Medical Card', // Use actual medical card type
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $transformedClients,
                'total' => $clients->total(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
            ]
        ]);
    }

    public function getClientPolicies(Request $request, $clientId)
    {
        $user = auth('api')->user();
        
        // Get policies for the specific client
        $policies = \App\Models\MemberPolicy::with(['user', 'plan'])
            ->where('user_id', $clientId)
            ->whereHas('user', function($query) use ($user) {
                $query->where('referrer_code', $user->agent_code);
            })
            ->get();
            
        // Transform the data to match frontend expectations
        $transformedPolicies = $policies->map(function($policy) {
            return [
                'id' => $policy->id,
                'user_id' => $policy->user_id,
                'plan_id' => $policy->plan_id,
                'policy_number' => $policy->policy_number,
                'start_date' => $policy->start_date,
                'end_date' => $policy->end_date,
                'status' => $policy->status,
                'auto_renew' => $policy->auto_renew,
                'amount' => $policy->plan ? $this->calculatePlanAmount($policy->plan, 'monthly') : 0,
                'user' => [
                    'id' => $policy->user->id,
                    'name' => $policy->user->name,
                    'email' => $policy->user->email,
                    'phone_number' => $policy->user->phone_number,
                    'nric' => $policy->user->nric,
                ],
                'plan' => [
                    'id' => $policy->plan->id,
                    'name' => $policy->plan->name,
                    'plan_code' => $policy->plan->slug,
                ]
            ];
        });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $transformedPolicies
            ]
        ]);
    }

    public function updatePolicyStatus(Request $request, $policyId)
    {
        $user = auth('api')->user();
        
        Log::info('Updating policy status', [
            'policy_id' => $policyId,
            'user_id' => $user->id,
            'agent_code' => $user->agent_code,
            'user_name' => $user->name
        ]);
        
        if (!$user->agent_code) {
            Log::warning('User does not have agent_code', [
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'User is not an agent'
            ], 403);
        }
        
        // Find the policy and ensure it belongs to a client under this agent
        $policy = \App\Models\MemberPolicy::with('user')
            ->where('id', $policyId)
            ->whereHas('user', function($query) use ($user) {
                $query->where('referrer_code', $user->agent_code);
            })
            ->first();
            
        if (!$policy) {
            Log::warning('Policy not found or access denied', [
                'policy_id' => $policyId,
                'user_id' => $user->id,
                'agent_code' => $user->agent_code
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Policy not found or access denied'
            ], 404);
        }
        
        Log::info('Policy found', [
            'policy_id' => $policy->id,
            'client_id' => $policy->user_id,
            'client_name' => $policy->user->name
        ]);
        
        $validated = $request->validate([
            'status' => 'required|in:active,expired,pending'
        ]);
        
        $policy->update([
            'status' => $validated['status']
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Policy status updated successfully',
            'data' => [
                'policy_id' => $policy->id,
                'status' => $policy->status,
                'client_name' => $policy->user->name
            ]
        ]);
    }

    public function processContinuePayment(Request $request, \App\Services\CurlecPaymentService $curlecService, \App\Services\CommissionService $commissionService)
    {
        $user = auth('api')->user();
        
        Log::info('Processing continue payment', [
            'user_id' => $user->id,
            'agent_code' => $user->agent_code,
            'user_name' => $user->name
        ]);
        
        if (!$user->agent_code) {
            Log::warning('User does not have agent_code', [
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'User is not an agent'
            ], 403);
        }
        
        $validated = $request->validate([
            'policy_id' => 'required|integer|exists:member_policies,id',
            'payment_method' => 'required|string|in:curlec,manual',
            'return_url' => 'nullable|string',
            'cancel_url' => 'nullable|string',
        ]);
        
        try {
            // Get the policy and verify it belongs to a client under this agent
            $policy = \App\Models\MemberPolicy::with(['user', 'plan'])
                ->where('id', $validated['policy_id'])
                ->whereHas('user', function($query) use ($user) {
                    $query->where('referrer_code', $user->agent_code);
                })
                ->first();
                
            if (!$policy) {
                Log::warning('Policy not found or access denied for continue payment', [
                    'policy_id' => $validated['policy_id'],
                    'user_id' => $user->id,
                    'agent_code' => $user->agent_code
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Policy not found or access denied'
                ], 404);
            }
            
            Log::info('Policy found for continue payment', [
                'policy_id' => $policy->id,
                'client_id' => $policy->user_id,
                'client_name' => $policy->user->name,
                'plan_id' => $policy->plan_id
            ]);
            
            // Find existing pending payment transaction for this client
            $payment = \App\Models\PaymentTransaction::where('user_id', $policy->user_id)
                ->where('status', 'pending')
                ->where('gateway', 'curlec')
                ->orderBy('created_at', 'desc')
                ->first();
                
            if (!$payment) {
                Log::warning('No pending payment found for continue payment', [
                    'client_id' => $policy->user_id,
                    'client_name' => $policy->user->name
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'No pending payment found for this client'
                ], 404);
            }
            
            Log::info('Found existing payment transaction', [
                'payment_id' => $payment->id,
                'amount_cents' => $payment->amount_cents,
                'amount_rm' => $payment->amount_cents / 100,
                'client_name' => $policy->user->name
            ]);
            
            
            if ($validated['payment_method'] === 'curlec') {
                // Create Curlec one-time payment for continue payment
                try {
                    // Create one-time order instead of subscription
                    $order = $curlecService->createOrder($payment);
                    Log::info('Order created for continue payment', [
                        'order_id' => $order['order_id'] ?? 'unknown'
                    ]);
                    
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
                        'amount' => $payment->amount_cents / 100, // Use existing payment amount
                        'currency' => 'MYR',
                        'order_id' => $order['order_id'],
                        'checkout_url' => null,
                    ];
                    
                } catch (\Exception $e) {
                    Log::warning('Curlec subscription creation failed for continue payment, using mock: ' . $e->getMessage());
                    
                    $checkoutData = [
                        'payment_id' => $payment->id,
                        'amount' => $payment->amount_cents / 100, // Use existing payment amount
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
                        'client' => [
                            'id' => $policy->user->id,
                            'name' => $policy->user->name,
                            'email' => $policy->user->email,
                            'phone_number' => $policy->user->phone_number,
                        ],
                        'policy' => [
                            'id' => $policy->id,
                            'policy_number' => $policy->policy_number,
                            'plan_name' => $policy->plan->name,
                        ]
                    ]
                ]);
                
            } else {
                // Manual payment - just create the payment, verification will handle the rest
                Log::info('Manual payment created, awaiting verification', [
                    'payment_id' => $payment->id,
                    'policy_id' => $policy->id
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Manual payment created, please verify payment',
                    'data' => [
                        'payment' => $payment,
                        'policy' => $policy,
                        'commissions_distributed' => false
                    ]
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Continue payment processing failed: ' . $e->getMessage(), [
                'policy_id' => $validated['policy_id'] ?? 'unknown',
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function verifyContinuePayment(Request $request, \App\Services\CommissionService $commissionService)
    {
        $validated = $request->validate([
            'payment_id' => 'required|exists:payment_transactions,id',
            'status' => 'required|in:success,failed',
            'external_ref' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $commissionService) {
                $payment = \App\Models\PaymentTransaction::findOrFail($validated['payment_id']);
                
                if ($validated['status'] === 'success') {
                    // Update payment status
                    $payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'external_ref' => $validated['external_ref'] ?? $payment->external_ref,
                    ]);

                    // Update policy status to active
                    if (isset($payment->meta['policy_id'])) {
                        \App\Models\MemberPolicy::where('id', $payment->meta['policy_id'])
                            ->update(['status' => 'active']);
                    }

                    // Ensure client has agent code
                    $client = $payment->user;
                    if (!$client->agent_code) {
                        $updates = ['agent_code' => $this->generateAgentCode()];
                        if (\Schema::hasColumn('users', 'status')) {
                            $updates['status'] = 'active';
                        }
                        if (\Schema::hasColumn('users', 'mlm_activation_date')) {
                            $updates['mlm_activation_date'] = now();
                        }
                        $client->update($updates);
                    }

                    // Calculate and disburse commissions
                    $commissionService->disburseForPayment($payment);

                    Log::info('Continue payment verification completed', [
                        'payment_id' => $payment->id,
                        'client_id' => $client->id,
                        'policy_id' => $payment->meta['policy_id'] ?? 'unknown'
                    ]);

                } else {
                    // Update payment as failed
                    $payment->update(['status' => 'failed']);
                    
                    // Update policy status to failed
                    if (isset($payment->meta['policy_id'])) {
                        \App\Models\MemberPolicy::where('id', $payment->meta['policy_id'])
                            ->update(['status' => 'failed']);
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verification completed'
            ]);

        } catch (\Exception $e) {
            Log::error('Continue payment verification failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 422);
        }
    }

    private function generateAgentCode()
    {
        $seq = str_pad((string) (User::whereNotNull('agent_code')->count() + 1), 5, '0', STR_PAD_LEFT);
        return 'AGT' . $seq;
    }

    public function calculatePlanAmount($plan, $paymentMode)
    {
        // The plan has annual price_cents, so we need to calculate monthly amount
        $annualAmount = $plan->price_cents ?? 0;
        $monthlyAmount = $annualAmount / 12; // Convert annual to monthly
        
        switch ($paymentMode) {
            case 'quarterly':
                return $monthlyAmount * 3;
            case 'semi_annually':
                return $monthlyAmount * 6;
            case 'annually':
                return $annualAmount;
            default: // monthly
                // Monthly amount only (commitment fee is separate)
                return $monthlyAmount;
        }
    }

    /**
     * Get user status based on their data
     */
    private function getUserStatus($user)
    {
        // Check if user has active policies
        $hasActivePolicies = \App\Models\MemberPolicy::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
            
        // Check if user has pending payments
        $hasPendingPayments = \App\Models\PaymentTransaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
            
        // Check if user is suspended or terminated
        if (isset($user->status)) {
            if (in_array($user->status, ['suspended', 'terminated'])) {
                return 'inactive';
            }
        }
        
        // If user has active policies, they are active
        if ($hasActivePolicies) {
            return 'active';
        }
        
        // If user has pending payments, they are pending
        if ($hasPendingPayments) {
            return 'pending';
        }
        
        // Default to active if no specific status
        return 'active';
    }

    /**
     * Get total commission for a user
     */
    private function getTotalCommissionForUser($userId)
    {
        $totalEarnedCents = \App\Models\CommissionTransaction::where('earner_user_id', $userId)
            ->where('status', 'posted')
            ->sum('commission_cents');
            
        return $totalEarnedCents / 100; // Convert cents to RM
    }
}


