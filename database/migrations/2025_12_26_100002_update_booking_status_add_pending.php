<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change status enum to include 'pending'
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending'");
        
        // Add approval fields
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['status']);
            $table->dropColumn(['approved_by', 'approved_at']);
        });
        
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed'");
    }
};

