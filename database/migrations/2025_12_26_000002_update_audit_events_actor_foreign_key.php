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
        $driver = DB::getDriverName();

        if ($driver !== 'sqlite') {
            Schema::table('audit_events', function (Blueprint $table) {
                // 原设计 actor_type = user|admin，但 actor_id 外键固定指向 users，无法记录 admin actor
                try {
                    $table->dropForeign(['actor_id']);
                } catch (\Throwable) {
                }
            });

            return;
        }

        // SQLite 无法在不重建表的情况下修改/删除外键，这里采用“重建表”方式去掉 actor_id 的 users 外键约束
        DB::statement('PRAGMA foreign_keys = OFF;');

        // 容错：如果上一次尝试在中途失败，可能留下 audit_events__old / audit_events（半成品）
        if (Schema::hasTable('audit_events__old')) {
            if (Schema::hasTable('audit_events')) {
                Schema::drop('audit_events');
            }
            Schema::rename('audit_events__old', 'audit_events');
        }

        Schema::rename('audit_events', 'audit_events__old');

        // SQLite 的 index 名称是全局的，rename 表不会改 index 名；避免与新表创建时的同名 index 冲突
        DB::statement('DROP INDEX IF EXISTS audit_events_event_type_occurred_at_index;');
        DB::statement('DROP INDEX IF EXISTS audit_events_actor_type_actor_id_index;');
        DB::statement('DROP INDEX IF EXISTS audit_events_target_type_target_id_index;');

        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->dateTime('occurred_at');
            $table->string('actor_type');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('request_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['event_type', 'occurred_at']);
            $table->index(['actor_type', 'actor_id']);
            $table->index(['target_type', 'target_id']);
        });

        DB::statement("
            INSERT INTO audit_events (
                id, event_type, occurred_at, actor_type, actor_id, target_type, target_id, request_id, metadata, created_at
            )
            SELECT
                id, event_type, occurred_at, actor_type, actor_id, target_type, target_id, request_id, metadata, created_at
            FROM audit_events__old
        ");

        Schema::drop('audit_events__old');
        DB::statement('PRAGMA foreign_keys = ON;');
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'sqlite') {
            // 回滚时恢复到 users 外键（仅用于本地/测试，生产一般不回滚此类变更）
            Schema::table('audit_events', function (Blueprint $table) {
                $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }
};


