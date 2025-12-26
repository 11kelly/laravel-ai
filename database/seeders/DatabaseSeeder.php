<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace Database\Seeders;

use App\Models\Activity;
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
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Create regular user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // Create sample activities
        $activities = [
            [
                'title' => '周末瑜伽体验课',
                'description' => '放松身心，舒展筋骨，专业教练带你入门瑜伽。',
                'content' => '<p>本次瑜伽体验课适合所有级别的参与者，无论你是初学者还是有经验的练习者。</p><p>活动包括：</p><ul><li>呼吸练习</li><li>基础体式</li><li>冥想放松</li></ul>',
                'location' => '城市健身中心',
                'address' => '市中心大道100号 3楼',
                'start_time' => now()->addDays(7)->setHour(10)->setMinute(0),
                'end_time' => now()->addDays(7)->setHour(12)->setMinute(0),
                'registration_deadline' => now()->addDays(5),
                'capacity' => 20,
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'title' => '户外摄影工作坊',
                'description' => '学习户外摄影技巧，捕捉自然之美。',
                'content' => '<p>由专业摄影师带领，在公园中实践拍摄技巧。</p><p>请自带相机设备。</p>',
                'location' => '中央公园',
                'address' => '公园北门集合',
                'start_time' => now()->addDays(14)->setHour(8)->setMinute(0),
                'end_time' => now()->addDays(14)->setHour(12)->setMinute(0),
                'registration_deadline' => now()->addDays(12),
                'capacity' => 15,
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'title' => '亲子手工制作',
                'description' => '与孩子一起动手，创作独一无二的手工作品。',
                'content' => '<p>适合5-12岁儿童与家长参与。材料费用已包含在内。</p>',
                'location' => '社区活动中心',
                'address' => '幸福路50号',
                'start_time' => now()->addDays(10)->setHour(14)->setMinute(0),
                'end_time' => now()->addDays(10)->setHour(16)->setMinute(30),
                'registration_deadline' => now()->addDays(8),
                'capacity' => 30,
                'status' => 'published',
                'is_featured' => false,
            ],
            [
                'title' => '编程入门讲座',
                'description' => '零基础也能学会编程，开启你的程序员之路。',
                'content' => '<p>本讲座将介绍编程基础概念，适合对编程感兴趣但没有经验的朋友。</p>',
                'location' => '科技图书馆',
                'address' => '创新大道200号',
                'start_time' => now()->addDays(21)->setHour(19)->setMinute(0),
                'end_time' => now()->addDays(21)->setHour(21)->setMinute(0),
                'registration_deadline' => now()->addDays(19),
                'capacity' => 50,
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'title' => '烘焙初体验',
                'description' => '学做美味甜点，享受烘焙的乐趣。',
                'content' => '<p>专业甜点师教你制作马卡龙和蛋糕。所有材料和工具已准备好。</p>',
                'location' => '甜蜜烘焙坊',
                'address' => '美食街88号',
                'start_time' => now()->addDays(5)->setHour(14)->setMinute(0),
                'end_time' => now()->addDays(5)->setHour(17)->setMinute(0),
                'registration_deadline' => now()->addDays(3),
                'capacity' => 12,
                'status' => 'published',
                'is_featured' => false,
            ],
            [
                'title' => '读书分享会',
                'description' => '与书友交流读书心得，分享阅读的快乐。',
                'content' => '<p>本月主题：《人类简史》</p><p>欢迎阅读过本书的朋友参与讨论。</p>',
                'location' => '城市书房',
                'address' => '文化路66号',
                'start_time' => now()->addDays(3)->setHour(19)->setMinute(30),
                'end_time' => now()->addDays(3)->setHour(21)->setMinute(30),
                'registration_deadline' => now()->addDays(2),
                'capacity' => 25,
                'status' => 'published',
                'is_featured' => false,
            ],
        ];

        foreach ($activities as $data) {
            Activity::create(array_merge($data, ['created_by' => $admin->id]));
        }
    }
}
