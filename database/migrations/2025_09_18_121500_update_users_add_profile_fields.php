<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'nric')) {
                $table->string('nric')->nullable()->after('phone_number');
            }
            if (! Schema::hasColumn('users', 'race')) {
                $table->string('race')->nullable()->after('nric');
            }
            if (! Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('race');
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('users', 'occupation')) {
                $table->string('occupation')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'height_cm')) {
                $table->decimal('height_cm', 5, 2)->nullable()->after('occupation');
            }
            if (! Schema::hasColumn('users', 'weight_kg')) {
                $table->decimal('weight_kg', 6, 2)->nullable()->after('height_cm');
            }
            if (! Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('weight_kg');
            }
            if (! Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
            if (! Schema::hasColumn('users', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            }
            if (! Schema::hasColumn('users', 'address')) {
                $table->string('address', 500)->nullable()->after('emergency_contact_relationship');
            }
            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (! Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (! Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 10)->nullable()->after('state');
            }
            if (! Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('postal_code');
            }
            if (! Schema::hasColumn('users', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('users', 'bank_account_owner')) {
                $table->string('bank_account_owner')->nullable()->after('bank_account_number');
            }
            if (! Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            }
        });
    }

    public function down(): void
    {
        // Keep non-destructive; no column drops to avoid data loss.
    }
};


