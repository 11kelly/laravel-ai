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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('registration_deadline')->nullable();
            $table->string('location');
            $table->string('address', 500)->nullable();
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('booked_count')->default(0);
            $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'start_time']);
            $table->index(['start_time', 'end_time']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};

