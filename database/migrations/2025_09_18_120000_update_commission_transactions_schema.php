<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure table exists; if not, create with the new canonical schema
        if (! Schema::hasTable('commission_transactions')) {
            Schema::create('commission_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('earner_user_id');
                $table->unsignedBigInteger('source_user_id')->nullable();
                $table->unsignedBigInteger('plan_id')->nullable();
                $table->unsignedBigInteger('payment_transaction_id')->nullable();
                $table->unsignedInteger('level')->nullable();
                $table->unsignedBigInteger('basis_amount_cents')->nullable();
                $table->unsignedBigInteger('commission_cents');
                $table->string('status', 50)->default('posted');
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();

                $table->index('earner_user_id');
            });
            return;
        }

        Schema::table('commission_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('commission_transactions', 'earner_user_id')) {
                $table->unsignedBigInteger('earner_user_id')->nullable()->after('id');
                $table->index('earner_user_id');
            }
            if (! Schema::hasColumn('commission_transactions', 'source_user_id')) {
                $table->unsignedBigInteger('source_user_id')->nullable()->after('earner_user_id');
            }
            if (! Schema::hasColumn('commission_transactions', 'plan_id')) {
                $table->unsignedBigInteger('plan_id')->nullable()->after('source_user_id');
            }
            if (! Schema::hasColumn('commission_transactions', 'payment_transaction_id')) {
                $table->unsignedBigInteger('payment_transaction_id')->nullable()->after('plan_id');
            }
            if (! Schema::hasColumn('commission_transactions', 'level')) {
                $table->unsignedInteger('level')->nullable()->after('payment_transaction_id');
            }
            if (! Schema::hasColumn('commission_transactions', 'basis_amount_cents')) {
                $table->unsignedBigInteger('basis_amount_cents')->nullable()->after('level');
            }
            if (! Schema::hasColumn('commission_transactions', 'commission_cents')) {
                $table->unsignedBigInteger('commission_cents')->nullable()->after('basis_amount_cents');
            }
            if (! Schema::hasColumn('commission_transactions', 'status')) {
                $table->string('status', 50)->default('posted')->after('commission_cents');
            }
            if (! Schema::hasColumn('commission_transactions', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive: leave table as-is to avoid data loss.
        // If needed later, write explicit column drops in a new migration.
    }
};


