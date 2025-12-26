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
        Schema::create('activity_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['activity_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_images');
    }
};


