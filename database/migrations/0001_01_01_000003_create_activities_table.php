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
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('location')->nullable();

            $table->unsignedInteger('capacity');
            $table->unsignedInteger('booked_count')->default(0);

            $table->string('status')->default('draft'); // draft | published | archived
            $table->dateTime('published_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'starts_at']);
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};


