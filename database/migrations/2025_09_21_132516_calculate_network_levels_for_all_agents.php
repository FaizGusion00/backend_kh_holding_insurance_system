<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\NetworkLevelService;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Calculate network levels for all existing agents
        $this->calculateNetworkLevelsForAllAgents();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear all network levels
        \App\Models\NetworkLevel::truncate();
    }

    /**
     * Calculate network levels for all agents
     */
    private function calculateNetworkLevelsForAllAgents()
    {
        $networkLevelService = new NetworkLevelService();
        
        // Get all users with agent codes
        $agents = User::whereNotNull('agent_code')->get();
        
        \Log::info('Starting network levels calculation for ' . $agents->count() . ' agents');
        
        foreach ($agents as $agent) {
            try {
                $networkLevelService->calculateNetworkLevelsForAgent($agent->agent_code);
                \Log::info("Calculated network levels for agent: {$agent->agent_code}");
            } catch (\Exception $e) {
                \Log::error("Failed to calculate network levels for agent {$agent->agent_code}: " . $e->getMessage());
            }
        }
        
        \Log::info('Network levels calculation completed for all agents');
    }
};