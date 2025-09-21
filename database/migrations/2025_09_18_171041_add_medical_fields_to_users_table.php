<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'medical_consultation_2_years')) {
                $table->boolean('medical_consultation_2_years')->default(false)->after('emergency_contact_relationship');
            }
            if (!Schema::hasColumn('users', 'serious_illness_history')) {
                $table->boolean('serious_illness_history')->default(false)->after('medical_consultation_2_years');
            }
            if (!Schema::hasColumn('users', 'insurance_rejection_history')) {
                $table->boolean('insurance_rejection_history')->default(false)->after('serious_illness_history');
            }
            if (!Schema::hasColumn('users', 'serious_injury_history')) {
                $table->boolean('serious_injury_history')->default(false)->after('insurance_rejection_history');
            }
            if (!Schema::hasColumn('users', 'mlm_level')) {
                $table->integer('mlm_level')->default(0)->after('bank_account_owner');
            }
            if (!Schema::hasColumn('users', 'total_commission_earned')) {
                $table->decimal('total_commission_earned', 10, 2)->default(0)->after('mlm_level');
            }
            if (!Schema::hasColumn('users', 'monthly_commission_target')) {
                $table->decimal('monthly_commission_target', 10, 2)->default(0)->after('total_commission_earned');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('monthly_commission_target');
            }
            if (!Schema::hasColumn('users', 'mlm_activation_date')) {
                $table->timestamp('mlm_activation_date')->nullable()->after('phone_verified_at');
            }
            if (!Schema::hasColumn('users', 'plan_name')) {
                $table->string('plan_name')->nullable()->after('mlm_activation_date');
            }
            if (!Schema::hasColumn('users', 'payment_mode')) {
                $table->string('payment_mode')->nullable()->after('plan_name');
            }
            if (!Schema::hasColumn('users', 'medical_card_type')) {
                $table->string('medical_card_type')->nullable()->after('payment_mode');
            }
            if (!Schema::hasColumn('users', 'current_insurance_plan_id')) {
                $table->foreignId('current_insurance_plan_id')->nullable()->constrained('insurance_plans')->after('medical_card_type');
            }
            if (!Schema::hasColumn('users', 'policy_start_date')) {
                $table->date('policy_start_date')->nullable()->after('current_insurance_plan_id');
            }
            if (!Schema::hasColumn('users', 'policy_end_date')) {
                $table->date('policy_end_date')->nullable()->after('policy_start_date');
            }
            if (!Schema::hasColumn('users', 'next_payment_due')) {
                $table->date('next_payment_due')->nullable()->after('policy_end_date');
            }
            if (!Schema::hasColumn('users', 'policy_status')) {
                $table->string('policy_status')->nullable()->after('next_payment_due');
            }
            if (!Schema::hasColumn('users', 'premium_amount')) {
                $table->decimal('premium_amount', 10, 2)->nullable()->after('policy_status');
            }
            if (!Schema::hasColumn('users', 'current_payment_mode')) {
                $table->string('current_payment_mode')->nullable()->after('premium_amount');
            }
            if (!Schema::hasColumn('users', 'agent_code')) {
                $table->string('agent_code')->nullable()->unique()->after('current_payment_mode');
            }
            if (!Schema::hasColumn('users', 'referrer_code')) {
                $table->string('referrer_code')->nullable()->after('agent_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'medical_consultation_2_years',
                'serious_illness_history', 
                'insurance_rejection_history',
                'serious_injury_history',
                'mlm_level',
                'total_commission_earned',
                'monthly_commission_target',
                'status',
                'mlm_activation_date',
                'plan_name',
                'payment_mode', 
                'medical_card_type',
                'current_insurance_plan_id',
                'policy_start_date',
                'policy_end_date',
                'next_payment_due',
                'policy_status',
                'premium_amount',
                'current_payment_mode',
                'agent_code',
                'referrer_code'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};