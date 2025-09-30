<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateAgentCodesToKH extends Command
{
    protected $signature = 'khh:update-agent-codes-to-kh {--dry-run : Run without making changes}';

    protected $description = 'Update all existing AGT agent codes to KH format';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Find all users with AGT prefix
        $users = User::where('agent_code', 'LIKE', 'AGT%')->get();
        
        if ($users->isEmpty()) {
            $this->info('✅ No AGT codes found. All agent codes are already updated!');
            return Command::SUCCESS;
        }

        $this->info("Found {$users->count()} users with AGT prefix");
        $this->newLine();

        $updated = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                $oldCode = $user->agent_code;
                
                // Replace AGT with KH
                $newCode = str_replace('AGT', 'KH', $oldCode);
                
                $this->line("  {$oldCode} → {$newCode}");
                
                if (!$dryRun) {
                    // Update the user's agent_code
                    $user->agent_code = $newCode;
                    $user->save();
                    
                    // Update referrer_code for users who reference this agent
                    User::where('referrer_code', $oldCode)
                        ->update(['referrer_code' => $newCode]);
                }
                
                $updated++;
            }

            if (!$dryRun) {
                DB::commit();
                $this->newLine();
                $this->info("✅ Successfully updated {$updated} agent codes from AGT to KH");
            } else {
                DB::rollBack();
                $this->newLine();
                $this->info("✅ Dry run complete. Would update {$updated} agent codes");
                $this->info("💡 Run without --dry-run to apply changes");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error updating agent codes: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
