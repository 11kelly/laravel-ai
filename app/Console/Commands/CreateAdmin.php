<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create
        {email : 管理员邮箱}
        {--name= : 管理员姓名（可选）}
        {--password= : 密码（可选；不提供会交互输入）}
        {--role=admin : 角色：admin|operator}
        {--inactive : 创建为禁用状态}
        {--update : 若邮箱已存在则更新该账号}
    ';

    protected $description = '创建（或更新）后台管理员账号（admins 表）';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $name = (string) ($this->option('name') ?: Str::before($email, '@'));
        $role = (string) ($this->option('role') ?: 'admin');
        $isActive = ! (bool) $this->option('inactive');
        $allowUpdate = (bool) $this->option('update');

        if (! in_array($role, ['admin', 'operator'], true)) {
            $this->error('role 只能是 admin 或 operator');
            return self::FAILURE;
        }

        /** @var string|null $password */
        $password = $this->option('password');
        if (! filled($password)) {
            $password = $this->secret('请输入密码（至少 8 位）');
        }

        if (! filled($password) || mb_strlen((string) $password) < 8) {
            $this->error('密码不能为空，且至少 8 位');
            return self::FAILURE;
        }

        /** @var Admin|null $existing */
        $existing = Admin::query()->where('email', '=', $email)->first();
        if ($existing && ! $allowUpdate) {
            $this->error('该邮箱已存在，如需更新请加 --update');
            return self::FAILURE;
        }

        $admin = $existing ?? new Admin();
        $admin->fill([
            'email' => $email,
            'name' => $name,
            'role' => $role,
            'is_active' => $isActive,
        ]);

        // 只有在显式输入/传入密码时才更新密码
        $admin->password = (string) $password;
        $admin->save();

        $this->info('✅ 后台账号已就绪：');
        $this->line('- email: ' . $admin->email);
        $this->line('- role: ' . $admin->role);
        $this->line('- is_active: ' . ((int) $admin->is_active));

        return self::SUCCESS;
    }
}


