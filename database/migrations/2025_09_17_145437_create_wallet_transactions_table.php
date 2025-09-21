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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('agent_wallets');
            $table->enum('type', ['credit', 'debit']);
            $table->enum('source', ['commission', 'withdrawal', 'admin_adjustment', 'reversal']);
            $table->unsignedBigInteger('amount_cents');
            $table->foreignId('commission_transaction_id')->nullable()->constrained('commission_transactions');
            // changed: keep as nullable column without FK to avoid ordering dependency
            $table->unsignedBigInteger('withdrawal_request_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
