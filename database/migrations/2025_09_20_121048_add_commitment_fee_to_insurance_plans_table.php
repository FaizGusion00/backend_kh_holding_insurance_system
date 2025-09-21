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
        Schema::table('insurance_plans', function (Blueprint $table) {
            $table->unsignedBigInteger('commitment_fee_cents')->nullable()->after('price_cents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurance_plans', function (Blueprint $table) {
            $table->dropColumn('commitment_fee_cents');
        });
    }
};