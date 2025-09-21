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
        Schema::create('insurance_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            // monthly, quarterly, semi_annual, annual – base billing cycle for display; payments store exact amount
            $table->enum('default_billing_cycle', ['monthly', 'quarterly', 'semi_annual', 'annual'])->nullable();
            $table->unsignedBigInteger('price_cents')->nullable();
            // when true, apply percentage rates from commission_rates; if false and fixed_amount_cents is set per level, use fixed
            $table->boolean('uses_percentage_commission')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_plans');
    }
};
