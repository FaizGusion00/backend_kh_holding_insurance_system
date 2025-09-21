<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AgentWallet;
use App\Services\NetworkLevelService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Main Agent (Level 1 - Top Level)
        $mainAgent = User::updateOrCreate(
            ['email' => 'agent@khholdings.com'],
            [
            'name' => 'Main Agent',
            'email' => 'agent@khholdings.com',
            'phone_number' => '0123456789',
            'nric' => '900101-01-0001',
            'race' => 'Malay',
            'height_cm' => 170,
            'weight_kg' => 70,
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_phone' => '0123456790',
            'emergency_contact_relationship' => 'Family',
            'password' => Hash::make('agent123'),
            'agent_code' => 'AGT00001',
            'referrer_code' => null, // Top level agent
            ]
        );

        // Create agent wallet for main agent
        AgentWallet::updateOrCreate(
            ['user_id' => $mainAgent->id],
            ['balance_cents' => 0]
        );

        // Create Level 2 Agent (referred by main agent)
        $level2Agent = User::updateOrCreate(
            ['email' => 'level2@khholdings.com'],
            [
            'name' => 'Level 2 Agent',
            'email' => 'level2@khholdings.com',
            'phone_number' => '0123456791',
            'nric' => '900101-01-0002',
            'race' => 'Chinese',
            'height_cm' => 165,
            'weight_kg' => 60,
            'emergency_contact_name' => 'Emergency Contact 2',
            'emergency_contact_phone' => '0123456792',
            'emergency_contact_relationship' => 'Spouse',
            'password' => Hash::make('agent123'),
            'agent_code' => 'AGT00002',
            'referrer_code' => 'AGT00001', // Referred by main agent
            ]
        );

        // Create agent wallet for level 2 agent
        AgentWallet::updateOrCreate(
            ['user_id' => $level2Agent->id],
            ['balance_cents' => 0]
        );

        // Create Level 3 Agent (referred by level 2 agent)
        $level3Agent = User::updateOrCreate(
            ['email' => 'level3@khholdings.com'],
            [
            'name' => 'Level 3 Agent',
            'email' => 'level3@khholdings.com',
            'phone_number' => '0123456793',
            'nric' => '900101-01-0003',
            'race' => 'Indian',
            'height_cm' => 175,
            'weight_kg' => 75,
            'emergency_contact_name' => 'Emergency Contact 3',
            'emergency_contact_phone' => '0123456794',
            'emergency_contact_relationship' => 'Sibling',
            'password' => Hash::make('agent123'),
            'agent_code' => 'AGT00003',
            'referrer_code' => 'AGT00002', // Referred by level 2 agent
            ]
        );

        // Create agent wallet for level 3 agent
        AgentWallet::updateOrCreate(
            ['user_id' => $level3Agent->id],
            ['balance_cents' => 0]
        );

        // Create a sample client (referred by level 3 agent)
        $client = User::updateOrCreate(
            ['email' => 'client@example.com'],
            [
            'name' => 'Sample Client',
            'email' => 'client@example.com',
            'phone_number' => '0123456795',
            'nric' => '900101-01-0004',
            'race' => 'Malay',
            'height_cm' => 160,
            'weight_kg' => 55,
            'emergency_contact_name' => 'Emergency Contact 4',
            'emergency_contact_phone' => '0123456796',
            'emergency_contact_relationship' => 'Parent',
            'password' => Hash::make('client123'),
            'agent_code' => 'AGT00004',
            'referrer_code' => 'AGT00003', // Referred by level 3 agent
            ]
        );

        // Create agent wallet for client (they can also be agents)
        AgentWallet::updateOrCreate(
            ['user_id' => $client->id],
            ['balance_cents' => 0]
        );

        // Calculate network levels for all created users
        $this->command->info('Calculating network levels for all users...');
        $networkLevelService = new NetworkLevelService();
        
        $users = User::whereNotNull('agent_code')->get();
        foreach ($users as $user) {
            try {
                $networkLevelService->calculateNetworkLevelsForAgent($user->agent_code);
                $this->command->info("Calculated network levels for {$user->agent_code}");
            } catch (\Exception $e) {
                $this->command->error("Failed to calculate network levels for {$user->agent_code}: " . $e->getMessage());
            }
        }

        $this->command->info('Created 4 users with proper MLM hierarchy:');
        $this->command->info('1. Main Agent (AGT00001) - Top Level');
        $this->command->info('2. Level 2 Agent (AGT00002) - Referred by AGT00001');
        $this->command->info('3. Level 3 Agent (AGT00003) - Referred by AGT00002');
        $this->command->info('4. Sample Client (AGT00004) - Referred by AGT00003');
        $this->command->info('Network levels calculated for all agents!');
    }
}
