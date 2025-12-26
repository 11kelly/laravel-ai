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
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();

            $table->string('event_type');
            $table->dateTime('occurred_at');

            $table->string('actor_type'); // user | admin
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();

            $table->string('request_id')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_type', 'occurred_at']);
            $table->index(['actor_type', 'actor_id']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};


