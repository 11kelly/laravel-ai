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
            Schema::table('activities', function (Blueprint $table) {
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Throwable) {
                }
                try {
                    $table->dropForeign(['updated_by']);
                } catch (\Throwable) {
                }
            });

            Schema::table('activity_images', function (Blueprint $table) {
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Throwable) {
                }
            });

            return;
        }

        // SQLite 无法在不重建表的情况下修改/删除外键，这里采用“重建表”方式去掉 created_by/updated_by 的 users 外键约束
        DB::statement('PRAGMA foreign_keys = OFF;');

        // 容错：若上次执行中途失败，先恢复到可重试状态
        if (Schema::hasTable('activities__old')) {
            if (Schema::hasTable('activities')) {
                Schema::drop('activities');
            }
            Schema::rename('activities__old', 'activities');
        }

        Schema::rename('activities', 'activities__old');

        // SQLite index 名全局：避免 rename 后与新表创建时同名 index 冲突
        DB::statement('DROP INDEX IF EXISTS activities_status_starts_at_index;');
        DB::statement('DROP INDEX IF EXISTS activities_published_at_index;');

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
            $table->string('status')->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->index(['status', 'starts_at']);
            $table->index('published_at');
        });

        DB::statement("
            INSERT INTO activities (
                id, title, summary, description, starts_at, ends_at, timezone, location,
                capacity, booked_count, status, published_at, created_by, updated_by, created_at, updated_at
            )
            SELECT
                id, title, summary, description, starts_at, ends_at, timezone, location,
                capacity, booked_count, status, published_at, created_by, updated_by, created_at, updated_at
            FROM activities__old
        ");

        Schema::drop('activities__old');

        if (Schema::hasTable('activity_images__old')) {
            if (Schema::hasTable('activity_images')) {
                Schema::drop('activity_images');
            }
            Schema::rename('activity_images__old', 'activity_images');
        }

        Schema::rename('activity_images', 'activity_images__old');

        DB::statement('DROP INDEX IF EXISTS activity_images_activity_id_sort_order_index;');

        Schema::create('activity_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['activity_id', 'sort_order']);
        });

        DB::statement("
            INSERT INTO activity_images (
                id, activity_id, disk, path, mime_type, size_bytes, checksum, sort_order, created_by, created_at
            )
            SELECT
                id, activity_id, disk, path, mime_type, size_bytes, checksum, sort_order, created_by, created_at
            FROM activity_images__old
        ");

        Schema::drop('activity_images__old');

        DB::statement('PRAGMA foreign_keys = ON;');
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'sqlite') {
            // 仅回滚外键（用于本地/测试；生产一般不回滚此类变更）
            Schema::table('activities', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            });

            Schema::table('activity_images', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });

            return;
        }

        // SQLite 回滚同样需要重建表；考虑到回滚风险，这里不做自动重建
    }
};


