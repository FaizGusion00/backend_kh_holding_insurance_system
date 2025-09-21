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
        Schema::create('commission_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('earner_user_id');
            $table->unsignedBigInteger('source_user_id')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->unsignedTinyInteger('level');
            $table->unsignedBigInteger('basis_amount_cents');
            $table->unsignedBigInteger('commission_cents');
            $table->enum('status', ['pending', 'posted', 'reversed'])->default('posted');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index('earner_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_transactions');
    }
};
