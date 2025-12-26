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
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('participants_count')->default(1);
            $table->enum('status', ['confirmed', 'cancelled', 'completed'])->default('confirmed');
            $table->text('notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->enum('cancelled_by', ['user', 'admin'])->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index('created_at');
        });
        
        // Note: Unique constraint for active bookings is enforced at application level
        // MySQL doesn't support partial unique indexes like PostgreSQL
        // Alternative: Use composite unique index (commented out as it doesn't allow multiple cancelled bookings)
        // $table->unique(['event_id', 'user_id', 'status']);
        
        // Add check constraint
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_participants_check CHECK (participants_count > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

