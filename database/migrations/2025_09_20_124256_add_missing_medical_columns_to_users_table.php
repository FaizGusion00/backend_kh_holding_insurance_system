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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'medical_card_type')) {
                $table->string('medical_card_type')->nullable()->after('current_payment_mode');
            }
            if (!Schema::hasColumn('users', 'payment_mode')) {
                $table->string('payment_mode')->nullable()->after('medical_card_type');
            }
            if (!Schema::hasColumn('users', 'plan_name')) {
                $table->string('plan_name')->nullable()->after('payment_mode');
            }
            if (!Schema::hasColumn('users', 'policy_start_date')) {
                $table->date('policy_start_date')->nullable()->after('plan_name');
            }
            if (!Schema::hasColumn('users', 'policy_end_date')) {
                $table->date('policy_end_date')->nullable()->after('policy_start_date');
            }
            if (!Schema::hasColumn('users', 'policy_status')) {
                $table->string('policy_status')->nullable()->after('policy_end_date');
            }
            if (!Schema::hasColumn('users', 'premium_amount')) {
                $table->decimal('premium_amount', 10, 2)->nullable()->after('policy_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'medical_card_type',
                'payment_mode',
                'plan_name',
                'policy_start_date',
                'policy_end_date',
                'policy_status',
                'premium_amount'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};