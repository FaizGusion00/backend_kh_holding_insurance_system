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
        Schema::create('commission_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('insurance_plans');
            $table->unsignedTinyInteger('level'); // 1..5
            // Either use percentage or fixed amount (in cents) per level
            $table->decimal('rate_percent', 6, 2)->nullable();
            $table->unsignedInteger('fixed_amount_cents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_rates');
    }
};
