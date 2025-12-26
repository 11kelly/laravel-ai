<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_id')->constrained('activities')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('status')->default('active'); // active | cancelled
            $table->dateTime('booked_at');
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->string('idempotency_key')->nullable();
            $table->json('activity_snapshot')->nullable();

            $table->timestamps();

            $table->unique(['activity_id', 'user_id']);
            $table->unique(['activity_id', 'user_id', 'idempotency_key']);
            $table->index(['user_id', 'status', 'booked_at']);
            $table->index(['activity_id', 'status', 'booked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};


