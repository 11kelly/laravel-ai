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
        Schema::create('event_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('image_url', 500);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            $table->index(['event_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_images');
    }
};

