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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('event_categories')->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('description');
            $table->string('cover_image', 500)->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->timestamp('booking_deadline')->nullable();
            $table->string('location', 255);
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('booked_count')->default(0);
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed', 'archived'])->default('draft');
            $table->text('booking_rules')->nullable();
            $table->string('organizer_name', 255)->nullable();
            $table->string('organizer_contact', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category_id', 'status']);
            $table->index('start_time');
            $table->index('status');
        });
        
        // Add check constraints
        DB::statement('ALTER TABLE events ADD CONSTRAINT events_time_check CHECK (end_time > start_time)');
        DB::statement('ALTER TABLE events ADD CONSTRAINT events_capacity_check CHECK (capacity >= booked_count)');
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

