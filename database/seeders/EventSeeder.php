<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create categories
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Tech events and workshops', 'display_order' => 1],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business conferences and networking', 'display_order' => 2],
            ['name' => 'Arts & Culture', 'slug' => 'arts-culture', 'description' => 'Cultural events and exhibitions', 'display_order' => 3],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports events and activities', 'display_order' => 4],
            ['name' => 'Education', 'slug' => 'education', 'description' => 'Educational workshops and seminars', 'display_order' => 5],
        ];

        foreach ($categories as $categoryData) {
            EventCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        // Create sample events
        $events = [
            [
                'title' => 'Web Development Workshop 2026',
                'description' => 'Learn the latest web development technologies including React, Vue.js, and modern CSS frameworks. This hands-on workshop will cover practical techniques you can apply immediately to your projects.',
                'category' => 'Technology',
                'start_time' => now()->addDays(10)->setTime(10, 0),
                'end_time' => now()->addDays(10)->setTime(17, 0),
                'location' => 'Tech Hub, Taipei 101, 5F',
                'capacity' => 50,
                'organizer_name' => 'eBrook Group',
                'organizer_contact' => 'events@ebrook.com.tw',
            ],
            [
                'title' => 'Digital Marketing Seminar',
                'description' => 'Explore the latest trends in digital marketing, including social media strategies, SEO optimization, and content marketing. Network with industry professionals and learn from successful case studies.',
                'category' => 'Business',
                'start_time' => now()->addDays(15)->setTime(14, 0),
                'end_time' => now()->addDays(15)->setTime(18, 0),
                'location' => 'Business Center, Xinyi District',
                'capacity' => 100,
                'organizer_name' => 'Marketing Pro Taiwan',
                'organizer_contact' => 'info@marketingpro.tw',
            ],
            [
                'title' => 'Contemporary Art Exhibition',
                'description' => 'Experience stunning contemporary artworks from local and international artists. The exhibition features paintings, sculptures, and digital installations that push the boundaries of artistic expression.',
                'category' => 'Arts & Culture',
                'start_time' => now()->addDays(7)->setTime(10, 0),
                'end_time' => now()->addDays(7)->setTime(20, 0),
                'location' => 'National Art Gallery',
                'capacity' => 200,
                'organizer_name' => 'Taiwan Arts Foundation',
                'organizer_contact' => 'gallery@arts.tw',
            ],
            [
                'title' => 'Marathon Training Camp',
                'description' => 'Join professional coaches for an intensive marathon training program. Suitable for all levels, from beginners to experienced runners. Learn proper techniques, nutrition tips, and injury prevention.',
                'category' => 'Sports',
                'start_time' => now()->addDays(20)->setTime(6, 0),
                'end_time' => now()->addDays(20)->setTime(12, 0),
                'location' => 'Riverside Park, Tamsui',
                'capacity' => 30,
                'organizer_name' => 'Run Taiwan',
                'organizer_contact' => 'coach@runtaiwan.com',
            ],
            [
                'title' => 'Python Programming for Beginners',
                'description' => 'Start your programming journey with Python! This beginner-friendly course covers fundamental programming concepts, data structures, and practical projects. No prior experience required.',
                'category' => 'Education',
                'start_time' => now()->addDays(12)->setTime(9, 0),
                'end_time' => now()->addDays(12)->setTime(16, 0),
                'location' => 'Code Academy, Zhongshan District',
                'capacity' => 40,
                'organizer_name' => 'eBrook Group',
                'organizer_contact' => 'education@ebrook.com.tw',
            ],
        ];

        foreach ($events as $eventData) {
            $category = EventCategory::where('name', $eventData['category'])->first();
            
            Event::firstOrCreate(
                ['slug' => Str::slug($eventData['title']) . '-' . time() . '-' . rand(1000, 9999)],
                [
                    'category_id' => $category->id,
                    'title' => $eventData['title'],
                    'description' => $eventData['description'],
                    'start_time' => $eventData['start_time'],
                    'end_time' => $eventData['end_time'],
                    'booking_deadline' => $eventData['start_time']->copy()->subDay(),
                    'location' => $eventData['location'],
                    'capacity' => $eventData['capacity'],
                    'status' => 'published',
                    'organizer_name' => $eventData['organizer_name'],
                    'organizer_contact' => $eventData['organizer_contact'],
                    'created_by' => $admin->id,
                ]
            );
        }

        $this->command->info('Events seeded successfully!');
    }
}

