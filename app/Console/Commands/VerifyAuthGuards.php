<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

/**
 * 验证认证守卫配置
 * 
 * 运行: php artisan auth:verify-guards
 */
final class VerifyAuthGuards extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'auth:verify-guards';

    /**
     * The console command description.
     */
    protected $description = '验证前后台认证守卫配置是否正确';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 开始验证认证守卫配置...');
        $this->newLine();

        // 检查 guards 配置
        $guards = config('auth.guards');
        
        if (!isset($guards['web'])) {
            $this->error('❌ web guard 未配置');
            return self::FAILURE;
        }
        $this->info('✅ web guard: ' . json_encode($guards['web']));

        if (!isset($guards['admin'])) {
            $this->error('❌ admin guard 未配置');
            return self::FAILURE;
        }
        $this->info('✅ admin guard: ' . json_encode($guards['admin']));

        // 检查 providers 配置
        $providers = config('auth.providers');
        
        if (!isset($providers['users'])) {
            $this->error('❌ users provider 未配置');
            return self::FAILURE;
        }
        $this->info('✅ users provider: ' . json_encode($providers['users']));

        if (!isset($providers['admins'])) {
            $this->error('❌ admins provider 未配置');
            return self::FAILURE;
        }
        $this->info('✅ admins provider: ' . json_encode($providers['admins']));

        // 验证 admin provider 使用自定义 driver
        if ($providers['admins']['driver'] !== 'admin_eloquent') {
            $this->error('❌ admins provider 应使用 admin_eloquent driver');
            return self::FAILURE;
        }
        $this->info('✅ admins provider 使用自定义 admin_eloquent driver');

        $this->newLine();
        $this->info('🎉 所有认证守卫配置验证通过！');
        $this->newLine();
        
        $this->comment('前端认证: web guard -> users provider (role=user)');
        $this->comment('后台认证: admin guard -> admins provider (role=admin)');
        
        return self::SUCCESS;
    }
}

