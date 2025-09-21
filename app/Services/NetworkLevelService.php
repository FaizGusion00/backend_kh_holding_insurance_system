<?php

namespace App\Services;

use App\Models\User;
use App\Models\NetworkLevel;
use App\Models\CommissionTransaction;
use App\Models\MemberPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NetworkLevelService
{
    /**
     * Rebuild the entire network levels table
     */
    public function rebuildNetworkLevels()
    {
        Log::info('Starting network levels rebuild');
        
        // Clear existing data
        NetworkLevel::truncate();
        
        // Get all users with agent codes
        $users = User::whereNotNull('agent_code')->get();
        
        foreach ($users as $user) {
            $this->calculateUserNetworkLevel($user);
        }
        
        Log::info('Network levels rebuild completed');
    }

    /**
     * Calculate and store network level for a specific user
     */
    public function calculateUserNetworkLevel(User $user)
    {
        if (!$user->agent_code) {
            return;
        }

        // Find the root agent (top of the hierarchy)
        $rootAgentCode = $this->findRootAgent($user);
        if (!$rootAgentCode) {
            return;
        }

        // Calculate level and path
        $levelData = $this->calculateLevelAndPath($user, $rootAgentCode);
        
        if (!$levelData) {
            return;
        }

        // Calculate additional stats
        $stats = $this->calculateUserStats($user);

        // Create or update network level record
        NetworkLevel::updateOrCreate(
            [
                'user_id' => $user->id,
                'root_agent_code' => $rootAgentCode
            ],
            [
                'agent_code' => $user->agent_code,
                'referrer_code' => $user->referrer_code,
                'level' => $levelData['level'],
                'root_agent_code' => $rootAgentCode,
                'level_path' => $levelData['path'],
                'direct_downlines_count' => $stats['direct_downlines'],
                'total_downlines_count' => $stats['total_downlines'],
                'commission_earned' => $stats['commission_earned'],
                'active_policies_count' => $stats['active_policies'],
                'last_updated' => now()
            ]
        );

        Log::info("Updated network level for user {$user->name} ({$user->agent_code}) - Level {$levelData['level']}");
    }

    /**
     * Find the root agent for a user
     */
    private function findRootAgent(User $user)
    {
        $currentCode = $user->agent_code;
        $visited = [];

        while ($currentCode) {
            if (in_array($currentCode, $visited)) {
                break; // Prevent infinite loops
            }
            $visited[] = $currentCode;

            $referrer = User::where('agent_code', $currentCode)->first();
            if (!$referrer || !$referrer->referrer_code) {
                return $currentCode; // This is the root
            }
            $currentCode = $referrer->referrer_code;
        }

        return $user->agent_code; // Fallback to user's own code
    }

    /**
     * Calculate level and path for a user
     */
    private function calculateLevelAndPath(User $user, $rootAgentCode)
    {
        if ($user->agent_code === $rootAgentCode) {
            return [
                'level' => 1,
                'path' => [$user->agent_code]
            ];
        }

        $level = 1;
        $path = [$user->agent_code];
        $currentCode = $user->referrer_code;
        $visited = [];

        // Walk up the referral chain to find the level
        while ($currentCode && $currentCode !== $rootAgentCode) {
            if (in_array($currentCode, $visited)) {
                break; // Prevent infinite loops
            }
            $visited[] = $currentCode;

            $level++;
            array_unshift($path, $currentCode);

            // Find the referrer of the current code
            $referrer = User::where('agent_code', $currentCode)->first();
            if (!$referrer) {
                break;
            }

            $currentCode = $referrer->referrer_code;
        }

        // Add 1 more level because the user themselves count as a level
        $level++;

        // If we didn't reach the root agent, this user is not in the network
        if ($currentCode !== $rootAgentCode) {
            return null;
        }

        return [
            'level' => $level,
            'path' => $path
        ];
    }

    /**
     * Calculate user statistics
     */
    private function calculateUserStats(User $user)
    {
        // Count direct downlines
        $directDownlines = User::where('referrer_code', $user->agent_code)->count();

        // Count total downlines (all levels below)
        $totalDownlines = $this->countTotalDownlines($user->agent_code);

        // Calculate commission earned from this user's network
        $commissionEarned = CommissionTransaction::where('earner_user_id', $user->id)
            ->where('status', 'posted')
            ->sum('commission_cents') / 100;

        // Count active policies
        $activePolicies = MemberPolicy::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        return [
            'direct_downlines' => $directDownlines,
            'total_downlines' => $totalDownlines,
            'commission_earned' => $commissionEarned,
            'active_policies' => $activePolicies
        ];
    }

    /**
     * Count total downlines recursively
     */
    private function countTotalDownlines($agentCode)
    {
        $count = 0;
        $directDownlines = User::where('referrer_code', $agentCode)->get();

        foreach ($directDownlines as $downline) {
            $count++; // Count this downline
            if ($downline->agent_code) {
                $count += $this->countTotalDownlines($downline->agent_code); // Count their downlines
            }
        }

        return $count;
    }

    /**
     * Get network members for a specific root agent and level
     */
    public function getNetworkMembers($rootAgentCode, $level = null)
    {
        $query = NetworkLevel::where('root_agent_code', $rootAgentCode)
            ->with(['user', 'referrer']);

        if ($level !== null) {
            $query->where('level', $level);
        }

        return $query->orderBy('level')->orderBy('created_at')->get();
    }

    /**
     * Get level breakdown for a root agent
     */
    public function getLevelBreakdown($rootAgentCode)
    {
        return NetworkLevel::where('root_agent_code', $rootAgentCode)
            ->selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->orderBy('level')
            ->pluck('count', 'level')
            ->toArray();
    }

    /**
     * Update network levels when a new user is added
     */
    public function updateNetworkLevelsForNewUser(User $newUser)
    {
        if (!$newUser->agent_code) {
            return;
        }

        // Calculate level for the new user
        $this->calculateUserNetworkLevel($newUser);

        // Recalculate levels for all users in the same network
        $rootAgentCode = $this->findRootAgent($newUser);
        $networkUsers = User::whereHas('networkLevels', function($query) use ($rootAgentCode) {
            $query->where('root_agent_code', $rootAgentCode);
        })->get();

        foreach ($networkUsers as $user) {
            $this->calculateUserNetworkLevel($user);
        }
    }

    /**
     * Calculate network levels for a specific agent as the root
     */
    public function calculateNetworkLevelsForAgent($agentCode)
    {
        $agent = User::where('agent_code', $agentCode)->first();
        if (!$agent) {
            return false;
        }

        // Get all users in this agent's downline network
        $allUsers = $this->getAllDownlineUsers($agentCode);
        
        // Add the agent themselves as level 1
        $allUsers->prepend($agent);

        foreach ($allUsers as $user) {
            $this->calculateUserNetworkLevelForRoot($user, $agentCode);
        }

        return true;
    }

    /**
     * Get all downline users for an agent
     */
    private function getAllDownlineUsers($agentCode)
    {
        $downlines = collect();
        $directDownlines = User::where('referrer_code', $agentCode)->get();

        foreach ($directDownlines as $downline) {
            $downlines->push($downline);
            if ($downline->agent_code) {
                $downlines = $downlines->merge($this->getAllDownlineUsers($downline->agent_code));
            }
        }

        return $downlines;
    }

    /**
     * Calculate user network level for a specific root agent
     */
    private function calculateUserNetworkLevelForRoot(User $user, $rootAgentCode)
    {
        if (!$user->agent_code) {
            return;
        }

        // Calculate level and path for this specific root
        $levelData = $this->calculateLevelAndPathForRoot($user, $rootAgentCode);
        
        if (!$levelData) {
            return;
        }

        // Calculate additional stats
        $stats = $this->calculateUserStats($user);

        // Create or update network level record
        NetworkLevel::updateOrCreate(
            [
                'user_id' => $user->id,
                'root_agent_code' => $rootAgentCode
            ],
            [
                'agent_code' => $user->agent_code,
                'referrer_code' => $user->referrer_code,
                'level' => $levelData['level'],
                'root_agent_code' => $rootAgentCode,
                'level_path' => $levelData['path'],
                'direct_downlines_count' => $stats['direct_downlines'],
                'total_downlines_count' => $stats['total_downlines'],
                'commission_earned' => $stats['commission_earned'],
                'active_policies_count' => $stats['active_policies'],
                'last_updated' => now()
            ]
        );
    }

    /**
     * Calculate level and path for a user with a specific root agent
     */
    private function calculateLevelAndPathForRoot(User $user, $rootAgentCode)
    {
        if ($user->agent_code === $rootAgentCode) {
            return [
                'level' => 1,
                'path' => [$user->agent_code]
            ];
        }

        $level = 1;
        $path = [$user->agent_code];
        $currentCode = $user->referrer_code;
        $visited = [];

        // Walk up the referral chain to find the level
        while ($currentCode && $currentCode !== $rootAgentCode) {
            if (in_array($currentCode, $visited)) {
                break; // Prevent infinite loops
            }
            $visited[] = $currentCode;

            $level++;
            array_unshift($path, $currentCode);

            // Find the referrer of the current code
            $referrer = User::where('agent_code', $currentCode)->first();
            if (!$referrer) {
                break;
            }

            $currentCode = $referrer->referrer_code;
        }

        // Add 1 more level because the user themselves count as a level
        $level++;

        // If we didn't reach the root agent, this user is not in the network
        if ($currentCode !== $rootAgentCode) {
            return null;
        }

        return [
            'level' => $level,
            'path' => $path
        ];
    }
}
