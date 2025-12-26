<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create demo user
        User::create([
            'name' => '測試用戶',
            'email' => 'user@example.com',
            'phone' => '0912345678',
            'password' => Hash::make('password'),
        ]);

        // Create demo activities
        $activities = [
            [
                'title' => '2025 新年音樂會',
                'description' => '迎接新年的美妙音樂盛宴，邀請國內外知名音樂家共同演出。現場將演奏古典與現代音樂的精彩曲目，為您帶來難忘的聽覺饗宴。',
                'start_time' => now()->addDays(7)->setHour(19)->setMinute(0),
                'end_time' => now()->addDays(7)->setHour(21)->setMinute(30),
                'capacity' => 200,
                'status' => 'published',
            ],
            [
                'title' => '攝影工作坊：城市街拍技巧',
                'description' => '由專業攝影師帶領，學習城市街拍的構圖技巧、光線運用與故事敘述。適合對攝影有興趣的初學者與進階愛好者。',
                'start_time' => now()->addDays(14)->setHour(10)->setMinute(0),
                'end_time' => now()->addDays(14)->setHour(16)->setMinute(0),
                'capacity' => 20,
                'status' => 'published',
            ],
            [
                'title' => '親子手作：陶藝體驗課程',
                'description' => '適合親子同樂的陶藝體驗活動，在專業老師指導下，一起動手製作獨一無二的陶藝作品。完成的作品可帶回家留念。',
                'start_time' => now()->addDays(21)->setHour(14)->setMinute(0),
                'end_time' => now()->addDays(21)->setHour(17)->setMinute(0),
                'capacity' => 15,
                'status' => 'published',
            ],
            [
                'title' => '戶外瑜伽：晨曦冥想',
                'description' => '在大自然中進行瑜伽練習，感受清晨的寧靜與美好。活動包含基礎瑜伽動作與冥想引導，適合各程度參與者。',
                'start_time' => now()->addDays(3)->setHour(6)->setMinute(30),
                'end_time' => now()->addDays(3)->setHour(8)->setMinute(0),
                'capacity' => 30,
                'status' => 'published',
            ],
            [
                'title' => '美食講座：義式料理的秘密',
                'description' => '由義大利籍主廚親自教授正統義式料理的烹飪技巧，從食材選擇到調味搭配，深入了解義大利美食文化。',
                'start_time' => now()->addDays(10)->setHour(18)->setMinute(30),
                'end_time' => now()->addDays(10)->setHour(21)->setMinute(0),
                'capacity' => 25,
                'status' => 'published',
            ],
            [
                'title' => '程式設計入門：Python 基礎課程',
                'description' => '專為程式設計新手設計的入門課程，從基礎概念開始，循序漸進學習 Python 程式語言。完成課程後可獲得結業證書。',
                'start_time' => now()->addDays(5)->setHour(9)->setMinute(0),
                'end_time' => now()->addDays(5)->setHour(17)->setMinute(0),
                'capacity' => 40,
                'status' => 'published',
            ],
        ];

        foreach ($activities as $activityData) {
            Activity::create([
                'admin_id' => $admin->id,
                ...$activityData,
            ]);
        }
    }
}
