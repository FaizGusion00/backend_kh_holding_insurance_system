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
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_id')->unique();
            $table->foreignId('agent_id')->constrained('users');
            $table->foreignId('plan_id')->constrained('insurance_plans');
            $table->json('clients_data'); // Store all client data as JSON
            $table->json('amount_breakdown'); // Store amount breakdown
            $table->integer('total_amount_cents');
            $table->string('currency')->default('MYR');
            $table->enum('status', ['pending', 'completed', 'failed', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'expires_at']);
            $table->index('registration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};