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
        Schema::create('pending_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('email');
            $table->foreignId('plan_id')->constrained('insurance_plans');
            $table->unsignedBigInteger('amount_cents');
            $table->enum('status', ['pending', 'expired', 'consumed'])->default('pending');
            $table->string('external_ref')->nullable()->index();
            $table->enum('created_by', ['self', 'admin'])->default('self');
            $table->json('meta')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_payments');
    }
};
