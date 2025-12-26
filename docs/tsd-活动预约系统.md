# 活动预约系统 - 技术设计文档 (TSD)

**文档版本**: v1.0  
**创建日期**: 2025-12-26  
**最后更新**: 2025-12-26  
**作者**: eBrook Group

---

## 1. 系统概述

### 1.1 项目背景

活动预约系统是一个基于 Laravel 和 Filament 4.0 的 Web 应用系统，旨在为用户提供活动浏览、预约管理功能，同时为管理员提供活动发布和用户预约管理的后台管理界面。

### 1.2 系统目标

- 提供用户友好的前台界面，支持活动浏览和预约
- 实现用户注册、登录和个人中心管理功能
- 提供功能完善的后台管理系统，支持活动发布和预约管理
- 支持活动图片上传和管理

### 1.3 技术栈

- **后端框架**: Laravel 12.0
- **PHP 版本**: PHP 8.2+
- **管理面板**: Filament 4.0
- **数据库**: SQLite (开发环境) / MySQL/PostgreSQL (生产环境)
- **前端技术**: Blade 模板引擎 + Tailwind CSS + Alpine.js
- **文件存储**: Laravel Filesystem (本地存储/云存储)

---

## 2. 系统架构设计

### 2.1 整体架构

```
┌─────────────────────────────────────────────────────────────┐
│                     前端层 (Frontend)                        │
├─────────────────────────────────────────────────────────────┤
│  前台系统 (Public)          │   后台系统 (Admin Panel)      │
│  - 活动浏览                 │   - Filament Admin Panel      │
│  - 活动预约                 │   - 活动管理 (CRUD)           │
│  - 用户注册/登录            │   - 预约管理                  │
│  - 个人中心                 │   - 用户管理                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                   应用层 (Application Layer)                 │
├─────────────────────────────────────────────────────────────┤
│  Controllers │ Services │ Repositories │ Policies            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     数据层 (Data Layer)                      │
├─────────────────────────────────────────────────────────────┤
│  Models │ Migrations │ Seeders │ Factories                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                   存储层 (Storage Layer)                     │
├─────────────────────────────────────────────────────────────┤
│  Database (SQLite/MySQL) │ File Storage (Local/Cloud)       │
└─────────────────────────────────────────────────────────────┘
```

### 2.2 模块划分

#### 2.2.1 前台模块 (Public Module)
- **活动浏览模块**: 活动列表展示、活动详情查看、活动搜索和筛选
- **预约模块**: 活动预约、预约确认、预约取消
- **用户认证模块**: 用户注册、登录、登出、密码重置
- **个人中心模块**: 个人资料管理、我的预约列表、预约详情查看

#### 2.2.2 后台模块 (Admin Module)
- **活动管理模块**: 活动创建、编辑、删除、发布/下架、图片上传
- **预约管理模块**: 预约列表、预约详情、预约状态管理、预约统计
- **用户管理模块**: 用户列表、用户详情、用户状态管理

### 2.3 路由设计

#### 前台路由 (Public Routes)
```
GET  /                    → 首页（活动列表）
GET  /events              → 活动列表页
GET  /events/{id}         → 活动详情页
POST /events/{id}/book    → 预约活动（需登录）
GET  /login               → 登录页
POST /login               → 登录处理
GET  /register            → 注册页
POST /register            → 注册处理
POST /logout              → 登出
GET  /profile             → 个人中心（需登录）
GET  /profile/bookings    → 我的预约（需登录）
POST /bookings/{id}/cancel → 取消预约（需登录）
```

#### 后台路由 (Admin Routes)
```
/admin                    → Filament 管理面板入口
/admin/events             → 活动管理（Filament Resource）
/admin/bookings           → 预约管理（Filament Resource）
/admin/users              → 用户管理（Filament Resource）
```

---

## 3. 数据模型设计

### 3.1 实体关系图 (ERD)

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│    Users    │         │   Events     │         │  Bookings   │
├─────────────┤         ├──────────────┤         ├─────────────┤
│ id          │◄──┐     │ id           │◄──┐     │ id          │
│ name        │   │     │ title        │   │     │ user_id     │
│ email       │   │     │ description  │   │     │ event_id    │
│ password    │   │     │ image        │   │     │ status      │
│ created_at  │   │     │ start_time   │   │     │ notes       │
│ updated_at  │   │     │ end_time     │   │     │ created_at  │
└─────────────┘   │     │ location     │   │     │ updated_at  │
                  │     │ capacity     │   │     └─────────────┘
                  │     │ booked_count │   │            │
                  │     │ is_published │   │            │
                  │     │ created_at   │   │            │
                  │     │ updated_at   │   │            │
                  │     └──────────────┘   │            │
                  │                        │            │
                  └────────────────────────┴────────────┘
```

### 3.2 数据表设计

#### 3.2.1 users 表（已存在，需扩展）
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    avatar VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### 3.2.2 events 表
```sql
CREATE TABLE events (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL COMMENT '活动标题',
    slug VARCHAR(255) UNIQUE NOT NULL COMMENT 'URL 友好标识',
    description TEXT NULL COMMENT '活动描述',
    image VARCHAR(255) NULL COMMENT '活动封面图片路径',
    start_time DATETIME NOT NULL COMMENT '活动开始时间',
    end_time DATETIME NOT NULL COMMENT '活动结束时间',
    location VARCHAR(255) NOT NULL COMMENT '活动地点',
    capacity INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '活动容量',
    booked_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '已预约数量',
    is_published BOOLEAN NOT NULL DEFAULT FALSE COMMENT '是否发布',
    published_at TIMESTAMP NULL COMMENT '发布时间',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_start_time (start_time),
    INDEX idx_is_published (is_published),
    INDEX idx_slug (slug)
);
```

#### 3.2.3 bookings 表
```sql
CREATE TABLE bookings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
    event_id BIGINT UNSIGNED NOT NULL COMMENT '活动ID',
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT '预约状态',
    notes TEXT NULL COMMENT '备注信息',
    cancelled_at TIMESTAMP NULL COMMENT '取消时间',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_event (user_id, event_id),
    INDEX idx_user_id (user_id),
    INDEX idx_event_id (event_id),
    INDEX idx_status (status)
);
```

### 3.3 Eloquent 模型设计

#### 3.3.1 Event 模型
```php
namespace App\Models;

class Event extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'image',
        'start_time', 'end_time', 'location',
        'capacity', 'booked_count', 'is_published', 'published_at'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'capacity' => 'integer',
        'booked_count' => 'integer',
    ];

    // 关联关系
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // 业务方法
    public function isFull(): bool
    {
        return $this->booked_count >= $this->capacity;
    }

    public function hasAvailableSlots(): bool
    {
        return $this->booked_count < $this->capacity;
    }

    public function getAvailableSlots(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }
}
```

#### 3.3.2 Booking 模型
```php
namespace App\Models;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'event_id', 'status', 'notes', 'cancelled_at'
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    // 关联关系
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // 状态常量
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    // 业务方法
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
```

#### 3.3.3 User 模型扩展
```php
namespace App\Models;

class User extends Authenticatable
{
    // 现有字段...
    
    // 新增关联关系
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function confirmedBookings()
    {
        return $this->bookings()->where('status', Booking::STATUS_CONFIRMED);
    }
}
```

---

## 4. 功能模块详细设计

### 4.1 前台功能模块

#### 4.1.1 活动浏览模块

**功能描述**: 用户可以在前台浏览所有已发布的活动

**核心功能点**:
- 活动列表展示（分页）
- 活动搜索（按标题、地点）
- 活动筛选（按时间、地点）
- 活动详情查看

**技术实现**:
- Controller: `EventController@index`, `EventController@show`
- View: `events.index`, `events.show`
- 使用 Laravel Pagination 实现分页
- 使用 Eloquent Query Builder 实现搜索和筛选

**接口设计**:
```
GET /events
Query Parameters:
  - page: 页码（默认1）
  - search: 搜索关键词
  - location: 地点筛选
  - start_date: 开始日期筛选
  - end_date: 结束日期筛选

Response: 活动列表（分页）
```

#### 4.1.2 活动预约模块

**功能描述**: 已登录用户可以预约活动

**核心功能点**:
- 预约活动（检查容量、重复预约）
- 预约确认
- 预约取消

**业务规则**:
- 用户必须登录才能预约
- 每个用户对同一活动只能预约一次
- 活动容量已满时不能预约
- 活动开始时间前可以取消预约

**技术实现**:
- Controller: `BookingController@store`, `BookingController@cancel`
- Service: `BookingService`（业务逻辑封装）
- Policy: `BookingPolicy`（权限控制）
- 使用数据库事务确保数据一致性

**接口设计**:
```
POST /events/{event}/book
Request Body:
  - notes: 备注（可选）

Response: 预约成功信息

POST /bookings/{booking}/cancel
Response: 取消成功信息
```

#### 4.1.3 用户认证模块

**功能描述**: 用户注册、登录、登出功能

**技术实现**:
- 使用 Laravel Breeze 或自定义认证
- Controller: `AuthController`
- Middleware: `auth`（保护需要登录的路由）

#### 4.1.4 个人中心模块

**功能描述**: 用户查看和管理个人信息及预约记录

**核心功能点**:
- 个人资料查看和编辑
- 我的预约列表
- 预约详情查看
- 预约取消

**技术实现**:
- Controller: `ProfileController`
- View: `profile.index`, `profile.bookings`
- 使用 Filament 的 User Profile 页面（可选）

---

### 4.2 后台功能模块

#### 4.2.1 活动管理模块（Filament Resource）

**功能描述**: 管理员创建、编辑、删除、发布活动

**核心功能点**:
- 活动 CRUD 操作
- 活动图片上传
- 活动发布/下架
- 活动容量管理
- 已预约数量统计

**技术实现**:
- Filament Resource: `EventResource`
- Form Components: 文本输入、日期时间选择、文件上传、富文本编辑器
- Table Columns: 标题、时间、地点、容量、状态
- Actions: 发布、下架、删除

**Filament Resource 结构**:
```php
namespace App\Filament\Resources;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->required(),
            TextInput::make('slug')->required()->unique(),
            Textarea::make('description'),
            FileUpload::make('image')
                ->image()
                ->directory('events')
                ->maxSize(5120), // 5MB
            DateTimePicker::make('start_time')->required(),
            DateTimePicker::make('end_time')->required(),
            TextInput::make('location')->required(),
            TextInput::make('capacity')
                ->numeric()
                ->required()
                ->default(0),
            Toggle::make('is_published'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            ImageColumn::make('image'),
            TextColumn::make('start_time')->dateTime()->sortable(),
            TextColumn::make('location')->searchable(),
            TextColumn::make('booked_count')
                ->label('已预约')
                ->badge(),
            TextColumn::make('capacity')
                ->label('容量')
                ->badge(),
            ToggleColumn::make('is_published')
                ->label('已发布'),
        ]);
    }
}
```

#### 4.2.2 预约管理模块（Filament Resource）

**功能描述**: 管理员查看和管理所有用户的预约

**核心功能点**:
- 预约列表展示
- 预约详情查看
- 预约状态管理（确认、取消）
- 预约统计

**技术实现**:
- Filament Resource: `BookingResource`
- Table Columns: 用户、活动、状态、创建时间
- Filters: 按状态、活动、用户筛选
- Actions: 确认预约、取消预约

#### 4.2.3 用户管理模块（Filament Resource）

**功能描述**: 管理员查看和管理用户

**技术实现**:
- 使用 Filament 内置的 User Resource 或自定义
- 显示用户基本信息、预约统计

---

## 5. 文件上传设计

### 5.1 图片上传配置

**存储配置**:
- 开发环境: 本地存储 (`storage/app/public/events`)
- 生产环境: 可配置为云存储（S3、OSS 等）

**上传限制**:
- 文件类型: jpg, jpeg, png, webp
- 文件大小: 最大 5MB
- 图片尺寸: 建议 1200x800px

**技术实现**:
- 使用 Filament 的 `FileUpload` 组件
- 使用 Laravel Filesystem
- 图片处理: Intervention Image（可选，用于缩略图生成）

**文件命名规则**:
```
events/{event_id}/{timestamp}_{random}.{ext}
```

---

## 6. 权限与安全设计

### 6.1 权限控制

**前台权限**:
- 公开访问: 活动浏览
- 需登录: 活动预约、个人中心

**后台权限**:
- 仅管理员可访问 `/admin` 路径
- Filament 默认使用 Laravel 的认证系统

### 6.2 安全措施

- CSRF 保护: Laravel 默认启用
- SQL 注入防护: 使用 Eloquent ORM
- XSS 防护: Blade 模板自动转义
- 密码加密: 使用 Laravel Hash
- 文件上传验证: 文件类型和大小限制

---

## 7. 非功能性需求

### 7.1 性能要求

- 页面加载时间: < 2秒
- 数据库查询优化: 使用索引、Eager Loading
- 图片优化: 压缩、CDN（生产环境）

### 7.2 可用性要求

- 系统可用性: 99.5%
- 错误处理: 友好的错误提示页面
- 响应式设计: 支持移动端访问

### 7.3 可维护性要求

- 代码规范: 遵循 PSR-12
- 代码注释: 关键业务逻辑需注释
- 单元测试: 核心业务逻辑需测试覆盖

---

## 8. 数据库迁移计划

### 8.1 迁移文件清单

1. `2025_12_26_000001_create_events_table.php`
2. `2025_12_26_000002_create_bookings_table.php`
3. `2025_12_26_000003_add_fields_to_users_table.php`（如需要扩展用户表）

### 8.2 数据填充

- `EventSeeder`: 创建示例活动数据
- `BookingSeeder`: 创建示例预约数据（可选）

---

## 9. 部署方案

### 9.1 环境要求

- PHP >= 8.2
- Composer
- Node.js & NPM
- Web Server (Apache/Nginx)
- Database (SQLite/MySQL/PostgreSQL)

### 9.2 部署步骤

1. 克隆代码仓库
2. 安装依赖: `composer install && npm install`
3. 配置环境变量: `.env` 文件
4. 生成应用密钥: `php artisan key:generate`
5. 运行数据库迁移: `php artisan migrate`
6. 创建管理员用户: `php artisan make:filament-user`
7. 构建前端资源: `npm run build`
8. 配置 Web Server
9. 设置存储链接: `php artisan storage:link`

---

## 10. 开发计划

### 10.1 开发阶段

**第一阶段: 基础架构**
- 数据库设计和迁移
- 模型创建
- 基础路由配置

**第二阶段: 前台功能**
- 活动浏览功能
- 用户认证功能
- 活动预约功能
- 个人中心功能

**第三阶段: 后台功能**
- Filament Resource 创建
- 活动管理功能
- 预约管理功能
- 用户管理功能

**第四阶段: 优化与测试**
- 性能优化
- 错误处理完善
- 测试编写
- 文档完善

---

## 11. 附录

### 11.1 参考文档

- [Laravel 官方文档](https://laravel.com/docs)
- [Filament 官方文档](https://filamentphp.com/docs)
- [Laravel Filesystem 文档](https://laravel.com/docs/filesystem)

### 11.2 术语表

- **Event**: 活动
- **Booking**: 预约
- **Capacity**: 容量
- **Published**: 已发布状态

---

**文档结束**

