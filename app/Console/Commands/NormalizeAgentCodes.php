<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class NormalizeAgentCodes extends Command
{
    protected $signature = 'khh:normalize-agent-codes {--dry-run}';

    protected $description = 'Normalize all users\' agent_code to AGT + 5 digits and fix referrer_code references';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Collect all users with non-null agent_code
        $users = User::whereNotNull('agent_code')
            ->orderBy('id')
            ->get(['id','agent_code']);

        $this->info('Found '.$users->count().' users with agent_code.');

        // Build mapping old -> new ensuring uniqueness
        $oldToNew = [];
        $used = [];
        $seq = 1;

        $format = function (int $n): string {
            return 'AGT'.str_pad((string)$n, 5, '0', STR_PAD_LEFT);
        };

        foreach ($users as $u) {
            $new = $format($seq);
            while (in_array($new, $used, true)) {
                $seq++;
                $new = $format($seq);
            }
            $oldToNew[$u->agent_code] = $new;
            $used[] = $new;
            $seq++;
        }

        // Preview
        $this->table(['Old','New'], array_map(fn($o,$n)=>[$o,$n], array_keys($oldToNew), array_values($oldToNew)));

        if ($dry) {
            $this->info('Dry run complete. No changes written.');
            return self::SUCCESS;
        }

        // Apply updates in a transaction
        \DB::transaction(function () use ($oldToNew) {
            foreach ($oldToNew as $old => $new) {
                // Update owner agent_code
                User::where('agent_code', $old)->update(['agent_code' => $new]);
                // Update referrer references
                User::where('referrer_code', $old)->update(['referrer_code' => $new]);
            }
        });

        $this->info('Agent codes normalized to 5 digits and referrers updated.');
        return self::SUCCESS;
    }
}


