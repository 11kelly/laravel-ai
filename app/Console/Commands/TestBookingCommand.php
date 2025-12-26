<?php

namespace App\Console\Commands;

use App\Http\Controllers\ActivityController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestBookingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:booking {user_email?} {--activity_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test booking functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userEmail = $this->argument('user_email') ?: 'testuser@example.com';
        $activityId = $this->option('activity_id');

        // 查找或创建测试用户
        $user = \App\Models\User::where('email', $userEmail)->first();
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => '测试用户',
                'email' => $userEmail,
                'password' => bcrypt('password123')
            ]);
            $this->info("Created test user: {$user->email}");
        }

        // 登录用户
        Auth::login($user);
        $this->info("Logged in as: {$user->email}");

        // 创建预约请求
        $controller = app(ActivityController::class);
        $request = new Request();
        $request->merge([
            'contact_info' => [
                'name' => '测试用户',
                'email' => $userEmail,
                'phone' => '13800138000'
            ],
            'special_requirements' => '命令行测试预约'
        ]);

        try {
            $response = $controller->book($request, $activityId);

            if ($response->getStatusCode() === 201) {
                $data = json_decode($response->getContent(), true);
                $this->info('✅ 预约成功！');
                $this->info("预约编号: {$data['booking_reference']}");
                $this->info("活动: {$data['activity']['title']}");
                $this->info("状态: {$data['status']}");
            } else {
                $data = json_decode($response->getContent(), true);
                $this->error('❌ 预约失败: ' . ($data['error'] ?? '未知错误'));
            }
        } catch (\Exception $e) {
            $this->error('❌ 预约异常: ' . $e->getMessage());
        }
    }
}
