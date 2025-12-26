<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::create([
            'title' => 'Laravel 技术分享会',
            'slug' => 'laravel-tech-sharing',
            'description' => '深入探讨 Laravel 12.0 的新特性和最佳实践，包括 Filament 4.0 的使用技巧。',
            'start_time' => now()->addDays(7)->setTime(14, 0),
            'end_time' => now()->addDays(7)->setTime(17, 0),
            'location' => '台北市信义区',
            'capacity' => 50,
            'booked_count' => 0,
            'is_published' => true,
            'published_at' => now(),
        ]);

        Event::create([
            'title' => '前端开发工作坊',
            'slug' => 'frontend-workshop',
            'description' => '学习现代前端开发技术，包括 Vue.js、React 和 Tailwind CSS 的实际应用。',
            'start_time' => now()->addDays(14)->setTime(10, 0),
            'end_time' => now()->addDays(14)->setTime(16, 0),
            'location' => '新北市板桥区',
            'capacity' => 30,
            'booked_count' => 0,
            'is_published' => true,
            'published_at' => now(),
        ]);

        Event::create([
            'title' => '数据库优化讲座',
            'slug' => 'database-optimization',
            'description' => '了解数据库性能优化的方法和技巧，包括索引设计、查询优化等。',
            'start_time' => now()->addDays(21)->setTime(13, 30),
            'end_time' => now()->addDays(21)->setTime(16, 30),
            'location' => '台北市信义区',
            'capacity' => 40,
            'booked_count' => 0,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}

