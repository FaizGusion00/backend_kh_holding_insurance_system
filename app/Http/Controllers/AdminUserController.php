<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::with('agentWallet')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6|confirmed',
            'referrer_code' => 'nullable|string|exists:users,agent_code',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'referrer_code' => $data['referrer_code'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $user->load(['agentWallet', 'memberPolicies', 'referredUsers']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'referrer_code' => 'nullable|string|exists:users,agent_code',
        ]);

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }

    public function network(User $user)
    {
        $network = $user->referredUsers()->with(['agentWallet', 'memberPolicies'])->paginate(15);
        return view('admin.users.network', compact('user', 'network'));
    }

    public function medical(User $user)
    {
        // Load all medical-related data
        $user->load([
            'memberPolicies.plan'
        ]);

        // Calculate policy information
        $medicalInfo = $this->calculateMedicalInfo($user);
        
        return view('admin.users.medical', compact('user', 'medicalInfo'));
    }

    private function calculateMedicalInfo(User $user)
    {
        $info = [
            'has_medical_plan' => false,
            'plan_type' => null,
            'plan_name' => null,
            'payment_mode' => null,
            'policy_status' => null,
            'policy_start_date' => null,
            'policy_end_date' => null,
            'next_payment_due' => null,
            'premium_amount' => null,
            'medical_card_type' => null,
            'registration_data' => [],
            'policy_duration' => null,
            'days_remaining' => null,
            'is_active' => false,
            'is_expired' => false,
            'is_pending' => false
        ];

        // Check if user has medical data - prioritize MemberPolicy over user table fields
        if ($user->memberPolicies->count() > 0) {
            $info['has_medical_plan'] = true;
            
            // Get the most recent active policy
            $activePolicy = $user->memberPolicies->where('status', 'active')->first();
            $latestPolicy = $user->memberPolicies->sortByDesc('created_at')->first();
            $policy = $activePolicy ?: $latestPolicy;
            
            if ($policy && $policy->plan) {
                // Get plan information from MemberPolicy and InsurancePlan
                $info['plan_type'] = $policy->plan->name;
                $info['plan_name'] = $policy->plan->name;
                $info['policy_status'] = $policy->status;
                
                // Calculate premium amount based on plan
                $info['premium_amount'] = $this->calculatePlanAmount($policy->plan, 'monthly');
                
                // Get payment mode from user table or default to monthly
                $info['payment_mode'] = $user->current_payment_mode ?? $user->payment_mode ?? 'monthly';
                
                // Get medical card type from user table
                $info['medical_card_type'] = $user->medical_card_type;

                // Calculate policy dates from MemberPolicy
                if ($policy->start_date) {
                    $info['policy_start_date'] = $policy->start_date;
                    $info['policy_end_date'] = $policy->end_date;
                    
                    // Calculate next payment due based on payment mode
                    $info['next_payment_due'] = $this->calculateNextPaymentDue($policy->start_date, $info['payment_mode']);

                    // Calculate policy duration and remaining days
                    if ($policy->end_date) {
                        $startDate = \Carbon\Carbon::parse($policy->start_date);
                        $endDate = \Carbon\Carbon::parse($policy->end_date);
                        $now = \Carbon\Carbon::now();

                        $info['policy_duration'] = $startDate->diffInDays($endDate) . ' days';
                        
                        // Calculate days remaining more accurately
                        $daysRemaining = $now->diffInDays($endDate, false);
                        $info['days_remaining'] = $daysRemaining;
                        
                        // Determine status based on current time and policy dates
                        $info['is_active'] = $now->between($startDate, $endDate) && $policy->status === 'active';
                        $info['is_expired'] = $now->gt($endDate) || $policy->status === 'expired';
                        $info['is_pending'] = $policy->status === 'pending';
                        
                        // Update policy status if needed (live sync)
                        if ($now->gt($endDate) && $policy->status === 'active') {
                            $policy->update(['status' => 'expired']);
                            $info['policy_status'] = 'expired';
                            $info['is_expired'] = true;
                            $info['is_active'] = false;
                        }
                    }
                }
            }

            // Get registration data
            $info['registration_data'] = [
                'personal_info' => [
                    'full_name' => $user->name,
                    'nric' => $user->nric,
                    'race' => $user->race,
                    'height_cm' => $user->height_cm,
                    'weight_kg' => $user->weight_kg,
                    'phone_number' => $user->phone_number,
                    'email' => $user->email,
                    'date_of_birth' => $user->date_of_birth,
                    'gender' => $user->gender,
                    'occupation' => $user->occupation,
                ],
                'address_info' => [
                    'address' => $user->address,
                    'city' => $user->city,
                    'state' => $user->state,
                    'postal_code' => $user->postal_code,
                ],
                'emergency_contact' => [
                    'name' => $user->emergency_contact_name,
                    'phone' => $user->emergency_contact_phone,
                    'relationship' => $user->emergency_contact_relationship,
                ],
                'medical_history' => [
                    'consultation_2_years' => $user->medical_consultation_2_years ? 'Yes' : 'No',
                    'serious_illness_history' => $user->serious_illness_history ? 'Yes' : 'No',
                    'insurance_rejection_history' => $user->insurance_rejection_history ? 'Yes' : 'No',
                    'serious_injury_history' => $user->serious_injury_history ? 'Yes' : 'No',
                ],
                'plan_details' => [
                    'plan_type' => $info['plan_name'],
                    'payment_mode' => $info['payment_mode'],
                    'medical_card_type' => $info['medical_card_type'],
                    'premium_amount' => $info['premium_amount'],
                ]
            ];
        }

        return $info;
    }

    private function calculatePlanAmount($plan, $paymentMode)
    {
        // Calculate the correct amount based on interval
        $annualAmount = $plan->price_cents / 100; // Convert to RM
        $monthlyAmount = $annualAmount / 12;
        
        switch ($paymentMode) {
            case 'quarterly':
                return $monthlyAmount * 3;
            case 'semi_annually':
                return $monthlyAmount * 6;
            case 'annually':
                return $annualAmount;
            default: // monthly
                return $monthlyAmount;
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
}
