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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('operator'); // admin | operator
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index(['role', 'is_active']);
        });

        // 从旧的 users(role=admin/operator) 迁移一份到 admins，避免上线后无法登录后台
        $rows = DB::table('users')
            ->select(['name', 'email', 'password', 'role'])
            ->whereIn('role', ['admin', 'operator'])
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $now = now();
        foreach ($rows as $row) {
            DB::table('admins')->updateOrInsert(
                ['email' => $row->email],
                [
                    'name' => $row->name,
                    'password' => $row->password,
                    'role' => in_array($row->role, ['admin', 'operator'], true) ? $row->role : 'operator',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};


