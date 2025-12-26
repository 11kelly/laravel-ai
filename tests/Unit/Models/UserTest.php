<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Filament\Panel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
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
        // Ref: TSD Section 2.0（双 guard + 后台 /admin）
        $this->panelMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn('not-admin');

        $user = new User();
        $user->role = 'admin';

        $this->assertFalse($user->canAccessPanel($this->panelMock));
    }

    #[Test]
    public function can_access_panel_allows_only_admin_or_operator_roles_for_admin_panel(): void
    {
        // Ref: TSD Section 8.1（现状最小角色：admin/operator）
        $this->panelMock
            ->expects($this->exactly(3))
            ->method('getId')
            ->willReturn('admin');

        $user = new User();
        $user->role = 'admin';
        $this->assertTrue($user->canAccessPanel($this->panelMock));

        $user = new User();
        $user->role = 'operator';
        $this->assertTrue($user->canAccessPanel($this->panelMock));

        $user = new User();
        $user->role = 'user';
        $this->assertFalse($user->canAccessPanel($this->panelMock));
    }

    #[Test]
    public function is_admin_and_is_operator_reflect_role_value(): void
    {
        // Ref: TSD Section 8.1（角色判断）
        $user = new User();
        $user->role = 'admin';
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isOperator());

        $user = new User();
        $user->role = 'operator';
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isOperator());
    }
}


