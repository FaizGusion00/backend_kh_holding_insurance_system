<?php

namespace App\Console\Commands;

use App\Services\NetworkLevelService;
use Illuminate\Console\Command;

class RebuildNetworkLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:rebuild {--force : Force rebuild even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the network levels table with accurate MLM hierarchy';

    /**
     * Execute the console command.
     */
    public function handle(NetworkLevelService $networkLevelService)
    {
        $this->info('Starting network levels rebuild...');
        
        if (!$this->option('force') && \App\Models\NetworkLevel::count() > 0) {
            if (!$this->confirm('Network levels table already has data. Do you want to rebuild it?')) {
                $this->info('Rebuild cancelled.');
                return;
            }
        }
        
        // Clear existing data
        \App\Models\NetworkLevel::truncate();
        $this->info('Cleared existing network levels data.');
        
        // Get all agents
        $agents = \App\Models\User::whereNotNull('agent_code')->get();
        $this->info("Found {$agents->count()} agents to process...");
        
        $bar = $this->output->createProgressBar($agents->count());
        $bar->start();
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($agents as $agent) {
            try {
                $networkLevelService->calculateNetworkLevelsForAgent($agent->agent_code);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("Failed to calculate for {$agent->agent_code}: " . $e->getMessage());
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Network levels rebuild completed!");
        $this->info("Successfully processed: {$successCount} agents");
        if ($errorCount > 0) {
            $this->warn("Errors encountered: {$errorCount} agents");
        }
        
        // Show summary
        $totalLevels = \App\Models\NetworkLevel::count();
        $this->info("Total network level records: {$totalLevels}");
        
        // Show agent breakdown
        $agentBreakdown = \App\Models\NetworkLevel::selectRaw('root_agent_code, COUNT(*) as count')
            ->groupBy('root_agent_code')
            ->orderBy('root_agent_code')
            ->get();
            
        $this->table(['Agent Code', 'Network Members'], $agentBreakdown->map(function($item) {
            return [$item->root_agent_code, $item->count];
        }));
    }
}