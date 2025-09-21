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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // 'new_user', 'commission', 'transaction', 'renewal_reminder', 'due_payment', etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data for the notification
            $table->timestamp('read_at')->nullable();
            $table->string('priority')->default('normal'); // 'low', 'normal', 'high', 'urgent'
            $table->string('category')->default('general'); // 'network', 'payment', 'commission', 'system', 'reminder'
            $table->unsignedBigInteger('related_user_id')->nullable(); // For notifications about other users
            $table->string('related_model_type')->nullable(); // Polymorphic relation
            $table->unsignedBigInteger('related_model_id')->nullable(); // Polymorphic relation
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('related_user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
