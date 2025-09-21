<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('network_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('agent_code')->index();
            $table->string('referrer_code')->nullable()->index();
            $table->integer('level')->default(1);
            $table->string('root_agent_code')->index(); // The top-level agent this user belongs to
            $table->json('level_path')->nullable(); // Array of agent codes from root to this user
            $table->integer('direct_downlines_count')->default(0);
            $table->integer('total_downlines_count')->default(0);
            $table->decimal('commission_earned', 10, 2)->default(0);
            $table->integer('active_policies_count')->default(0);
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['root_agent_code', 'level']);
            $table->index(['agent_code', 'level']);
            $table->unique(['user_id', 'root_agent_code']); // One user can only have one level per root agent
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_levels');
    }
};