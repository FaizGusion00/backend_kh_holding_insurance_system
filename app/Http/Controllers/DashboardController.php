<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CommissionTransaction;
use App\Models\PaymentTransaction;
use App\Models\MemberPolicy;
use App\Models\AgentWallet;
use App\Models\Hospital;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        try {
            // Get total members (direct referrals)
            $totalMembers = User::where('referrer_code', $user->agent_code)->count();
            
            // Get new members this month
            $newMembersThisMonth = User::where('referrer_code', $user->agent_code)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
            
            // Get active members (those with active policies)
            $activeMembers = User::where('referrer_code', $user->agent_code)
                ->whereHas('memberPolicies', function($query) {
                    $query->where('status', 'active');
                })
                ->count();
            
            // Get total commission earned
            $totalCommissionEarned = CommissionTransaction::where('earner_user_id', $user->id)
                ->where('status', 'posted')
                ->sum('commission_cents') / 100;
            
            // Get monthly commission earned
            $monthlyCommissionEarned = CommissionTransaction::where('earner_user_id', $user->id)
                ->where('status', 'posted')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('commission_cents') / 100;
            
            // Get wallet balance
            $wallet = AgentWallet::where('user_id', $user->id)->first();
            $walletBalance = $wallet ? $wallet->balance_cents / 100 : 0;
            
            // Calculate target achievement (assuming 1000 RM target for now)
            $targetAmount = 1000;
            $targetAchievement = $targetAmount > 0 ? min(($monthlyCommissionEarned / $targetAmount) * 100, 100) : 0;
            
            // Get MLM level (calculate based on network depth)
            $mlmLevel = $this->calculateMlmLevel($user->agent_code);
            
            // Get total hospitals and clinics count
            $totalHospitals = Hospital::where('is_active', true)->count();
            $totalClinics = Clinic::where('is_active', true)->count();
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($user);
            
            // Get performance data
            $performanceData = $this->getPerformanceData($user);
            
            return response()->json([
                'status' => 'success', 
                'data' => [
                    'stats' => [
                        'total_members' => $totalMembers,
                        'new_members' => $newMembersThisMonth,
                        'active_members' => $activeMembers,
                        'total_commission_earned' => number_format($totalCommissionEarned, 2),
                        'monthly_commission_earned' => number_format($monthlyCommissionEarned, 2),
                        'target_achievement' => round($targetAchievement, 1),
                        'mlm_level' => $mlmLevel,
                        'wallet_balance' => number_format($walletBalance, 2),
                        'total_hospitals' => $totalHospitals,
                        'total_clinics' => $totalClinics,
                    ],
                    'recent_activities' => $recentActivities,
                    'performance_data' => $performanceData,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard data fetch error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => 'Failed to fetch dashboard data',
                'data' => [
                    'stats' => [
                        'total_members' => 0,
                        'new_members' => 0,
                        'active_members' => 0,
                        'total_commission_earned' => '0.00',
                        'monthly_commission_earned' => '0.00',
                        'target_achievement' => 0,
                        'mlm_level' => 1,
                        'wallet_balance' => '0.00',
                        'total_hospitals' => 0,
                        'total_clinics' => 0,
                    ],
                    'recent_activities' => [],
                    'performance_data' => [
                        'monthly_commissions' => [],
                        'network_growth' => [],
                    ],
                ]
            ]);
        }
    }
    
    private function calculateMlmLevel($agentCode)
    {
        // Calculate MLM level based on network depth
        $level = 1;
        $currentCode = $agentCode;
        
        // Go up the referral chain to find the highest level
        while ($currentCode) {
            $referrer = User::where('agent_code', $currentCode)->first();
            if ($referrer && $referrer->referrer_code) {
                $level++;
                $currentCode = $referrer->referrer_code;
            } else {
                break;
            }
        }
        
        return $level;
    }
    
    private function getRecentActivities($user)
    {
        $activities = [];
        
        // Get recent member registrations
        $recentMembers = User::where('referrer_code', $user->agent_code)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
            
        foreach ($recentMembers as $member) {
            $activities[] = [
                'type' => 'member_registration',
                'description' => "New member {$member->name} registered",
                'created_at' => $member->created_at->toISOString(),
                'amount' => null
            ];
        }
        
        // Get recent commission earnings
        $recentCommissions = CommissionTransaction::where('earner_user_id', $user->id)
            ->where('status', 'posted')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
            
        foreach ($recentCommissions as $commission) {
            $activities[] = [
                'type' => 'commission_earned',
                'description' => "Commission earned from level {$commission->level}",
                'created_at' => $commission->created_at->toISOString(),
                'amount' => $commission->commission_cents / 100
            ];
        }
        
        // Get recent payments
        $recentPayments = PaymentTransaction::where('user_id', $user->id)
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
            
        foreach ($recentPayments as $payment) {
            $activities[] = [
                'type' => 'payment_received',
                'description' => "Payment received for policy",
                'created_at' => $payment->created_at->toISOString(),
                'amount' => $payment->amount_cents / 100
            ];
        }
        
        // Sort by date and return latest 5
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 5);
    }
    
    private function getPerformanceData($user)
    {
        // Get monthly commission data for the last 6 months
        $monthlyCommissions = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $commission = CommissionTransaction::where('earner_user_id', $user->id)
                ->where('status', 'posted')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('commission_cents') / 100;
                
            $monthlyCommissions[] = [
                'month' => $date->format('M Y'),
                'amount' => $commission
            ];
        }
        
        // Get network growth data for the last 6 months
        $networkGrowth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $newMembers = User::where('referrer_code', $user->agent_code)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
                
            $networkGrowth[] = [
                'month' => $date->format('M Y'),
                'count' => $newMembers
            ];
        }
        
        return [
            'monthly_commissions' => $monthlyCommissions,
            'network_growth' => $networkGrowth,
        ];
    }
}


