<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Admin;
use Filament\Panel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AdminTest extends TestCase
{
    private Panel|MockObject $panelMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->panelMock = $this->createMock(Panel::class);
    }

    #[Test]
    public function can_access_panel_returns_false_when_panel_id_is_not_admin(): void
    {
        // Ref: TSD Section 2.0（admin guard + /admin）
        $this->panelMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn('not-admin');

        $admin = new Admin();
        $admin->is_active = true;

        $this->assertFalse($admin->canAccessPanel($this->panelMock));
    }

    #[Test]
    public function can_access_panel_returns_false_when_admin_is_inactive(): void
    {
        // Ref: TSD Section 8.1（admins.is_active 禁用不可登录后台）
        $this->panelMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn('admin');

        $admin = new Admin();
        $admin->is_active = false;

        $this->assertFalse($admin->canAccessPanel($this->panelMock));
    }

    #[Test]
    public function can_access_panel_returns_true_when_admin_panel_and_admin_is_active(): void
    {
        // Ref: TSD Section 8.1
        $this->panelMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn('admin');

        $admin = new Admin();
        $admin->is_active = true;

        $this->assertTrue($admin->canAccessPanel($this->panelMock));
    }

    #[Test]
    public function is_admin_and_is_operator_reflect_role_value(): void
    {
        // Ref: TSD Section 8.1（后台角色判断）
        $admin = new Admin();
        $admin->role = 'admin';
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isOperator());

        $admin = new Admin();
        $admin->role = 'operator';
        $this->assertFalse($admin->isAdmin());
        $this->assertTrue($admin->isOperator());
    }
}


