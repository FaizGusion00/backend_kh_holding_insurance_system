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
        Schema::create('gateway_records', function (Blueprint $table) {
            $table->id();
            $table->enum('provider', ['curlec']);
            $table->enum('direction', ['request', 'webhook', 'query']);
            $table->string('external_ref')->nullable()->index();
            $table->json('payload')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateway_records');
    }
};
