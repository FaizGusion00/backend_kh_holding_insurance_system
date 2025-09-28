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
        // Fix agent_wallets foreign key constraint
        Schema::table('agent_wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Fix member_policies foreign key constraint
        Schema::table('member_policies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Fix payment_transactions foreign key constraint
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Fix withdrawal_requests foreign key constraint
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Fix pending_payments foreign key constraint
        Schema::table('pending_payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Fix pending_registrations foreign key constraint
        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert agent_wallets foreign key constraint
        Schema::table('agent_wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Revert member_policies foreign key constraint
        Schema::table('member_policies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Revert payment_transactions foreign key constraint
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Revert withdrawal_requests foreign key constraint
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Revert pending_payments foreign key constraint
        Schema::table('pending_payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Revert pending_registrations foreign key constraint
        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->foreign('agent_id')->references('id')->on('users');
        });
    }
};