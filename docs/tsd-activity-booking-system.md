/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

# 活动预约系统 - 技术设计文档 (TSD)

**文档版本**: 1.1.0  
**创建日期**: 2025-12-26  
**最后更新**: 2025-12-26  
**技术栈**: Laravel 12.x + Filament 4.0 + PHP 8.2+  
**文档状态**: 已实现

---

## 版本历史

| 版本 | 日期 | 修改内容 | 修改人 |
|-----|------|----------|--------|
| 1.0.0 | 2025-12-26 | 初稿 | AI Assistant |
| 1.1.0 | 2025-12-26 | 更新实现细节、新增用户管理模块、修正路由定义 | AI Assistant |

---

## 1. 背景与目标 (Background & Goals)

### 1.1 业务与技术痛点

- 缺乏统一的活动发布与预约管理平台
- 用户无法便捷地浏览、筛选和预约活动
- 管理员需要高效的后台工具来发布活动、管理预约
- 需要美观现代的前台界面以提升用户体验

### 1.2 本需求明确解决的问题

1. **前台用户端**：提供活动浏览、详情查看、在线预约功能，支持用户注册与登录
2. **个人中心**：用户可管理个人预约记录、编辑个人资料、修改密码
3. **管理后台**：管理员可发布/编辑/删除活动、管理用户预约、管理用户账号、上传活动图片

### 1.3 架构目标

| 目标维度 | 描述 |
|---------|------|
| **可扩展性** | 模块化设计，支持未来扩展活动类型、支付集成等功能 |
| **可维护性** | 遵循 Laravel 最佳实践，使用 Service Layer 解耦业务逻辑 |
| **可审计性** | 关键业务操作记录日志，支持预约状态追踪 |
| **性能** | 首屏加载 < 3s，API 响应 < 500ms |
| **安全性** | 输入验证、CSRF 防护、XSS 防护、认证授权 |

### 1.4 Out of Scope（本期不解决但已识别的问题）

- 在线支付功能（待确认）
- 活动评价与评论系统
- 多语言国际化
- 活动提醒推送通知
- 第三方社交登录集成

---

## 2. 核心接口定义 (API / Service Contracts)

### 2.1 前台公开路由

> **实现说明**：本系统采用传统的 Web 路由而非 API 路由，使用 Blade 模板渲染页面，配合 Alpine.js 实现交互。

#### 2.1.1 活动列表页面

| 属性 | 说明 |
|-----|------|
| **路由** | `GET /activities` |
| **控制器** | `ActivityController@index` |
| **职责** | 显示活动列表页面，支持分页 |
| **调用方** | 前台 Web 页面 |
| **被调用方** | `ActivityService` |

**输入参数**：

| 参数 | 类型 | 必填 | 校验规则 |
|-----|------|-----|----------|
| `page` | integer | 否 | min:1 |

**输出**：Blade 视图 `activities.index`

---

#### 2.1.2 活动详情页面

| 属性 | 说明 |
|-----|------|
| **路由** | `GET /activities/{activity:slug}` |
| **控制器** | `ActivityController@show` |
| **职责** | 显示单个活动详情页面 |
| **调用方** | 前台活动列表 |
| **被调用方** | `ActivityService` |

**路由参数**：

| 参数 | 类型 | 必填 | 校验规则 |
|-----|------|-----|----------|
| `slug` | string | 是 | 使用 Route Model Binding 自动解析 |

**输出**：Blade 视图 `activities.show`

---

#### 2.1.3 预约活动接口

| 属性 | 说明 |
|-----|------|
| **路由** | `POST /bookings` |
| **控制器** | `BookingController@store` |
| **职责** | 用户预约活动 |
| **调用方** | 已登录用户 |
| **被调用方** | `BookingService` |
| **认证** | 必须登录 |
| **速率限制** | 10次/分钟/用户 |

**输入参数**：

| 参数 | 类型 | 必填 | 校验规则 |
|-----|------|-----|----------|
| `activity_id` | integer | 是 | exists:activities,id |
| `participants` | integer | 否 | min:1, max:10, 默认 1 |
| `remarks` | string | 否 | max:500 |

**成功响应**：重定向至活动详情页，显示成功消息

**失败响应**：

| 错误码 | 说明 | 责任归属 |
|-------|------|----------|
| 401 | 未登录 | 调用方 |
| 404 | 活动不存在 | 调用方 |
| 409 | 活动已满/已结束/已预约过 | 业务逻辑 |
| 422 | 参数校验失败 | 调用方 |

**幂等性**: 否（创建资源）  
**副作用**: 创建预约记录，更新活动已预约人数

---

#### 2.1.4 取消预约接口

| 属性 | 说明 |
|-----|------|
| **路由** | `DELETE /bookings/{booking}` |
| **控制器** | `BookingController@destroy` |
| **职责** | 用户取消预约 |
| **调用方** | 已登录用户（仅限本人） |
| **被调用方** | `BookingService` |
| **认证** | 必须登录，仅限本人操作 |
| **速率限制** | 10次/分钟/用户 |

**输入参数**：

| 参数 | 类型 | 必填 | 校验规则 |
|-----|------|-----|----------|
| `booking` | integer | 是 | exists:bookings,id |
| `cancellation_reason` | string | 否 | max:255 |

**成功响应**：重定向至我的预约页面，显示成功消息

**失败响应**：

| 错误码 | 说明 |
|-------|------|
| 401 | 未登录 |
| 403 | 无权操作（非本人预约） |
| 404 | 预约不存在 |
| 409 | 活动已开始/已结束，无法取消 |

**幂等性**: 是（重复取消返回相同结果）  
**副作用**: 更新预约状态，更新活动已预约人数

---

### 2.2 用户个人中心路由

> **说明**：所有个人中心路由都需要用户登录认证。

#### 2.2.1 用户仪表盘

| 属性 | 说明 |
|-----|------|
| **路由** | `GET /user/dashboard` |
| **控制器** | `UserController@dashboard` |
| **职责** | 显示用户个人中心仪表盘 |

**输出**：Blade 视图 `user.dashboard`，包含：
- 用户基本信息
- 预约统计（总数、已完成、已取消）
- 近期预约列表
- 快捷操作链接

---

#### 2.2.2 我的预约列表

| 属性 | 说明 |
|-----|------|
| **路由** | `GET /user/bookings` |
| **控制器** | `UserController@bookings` |
| **职责** | 显示当前用户的预约列表 |

**输入参数**：

| 参数 | 类型 | 必填 | 校验规则 |
|-----|------|-----|----------|
| `status` | string | 否 | enum: all, confirmed, completed, cancelled |

**输出**：Blade 视图 `user.bookings`

---

#### 2.2.3 个人资料管理

| 属性 | 说明 |
|-----|------|
| **路由 (查看)** | `GET /user/profile` |
| **路由 (更新)** | `PUT /user/profile` |
| **控制器** | `UserController@profile` / `UserController@updateProfile` |
| **职责** | 查看或更新用户资料 |

**更新参数**：

| 参数 | 类型 | 必填 | 校验规则 |
|-----|------|-----|----------|
| `name` | string | 否 | max:50 |
| `phone` | string | 否 | max:20 |
| `avatar` | file | 否 | image, max:2MB |

---

#### 2.2.4 密码管理

| 属性 | 说明 |
|-----|------|
| **路由 (查看)** | `GET /user/password` |
| **路由 (更新)** | `PUT /user/password` |
| **控制器** | `UserController@password` / `UserController@updatePassword` |
| **职责** | 修改用户密码 |

**更新参数**：

| 参数 | 类型 | 必填 | 校验规则 |
|-----|------|-----|----------|
| `current_password` | string | 是 | 验证当前密码 |
| `password` | string | 是 | min:8, confirmed |
| `password_confirmation` | string | 是 | 与 password 匹配 |

---

### 2.3 管理后台接口

管理后台采用 Filament 4.0 内置的 CRUD 机制，通过 Resource 类定义。

> **访问控制**：管理后台访问通过 `User::canAccessPanel()` 方法控制，检查 `is_admin` 字段。

#### 2.3.1 活动管理 (ActivityResource)

| 操作 | 路由 | 权限 |
|-----|------|------|
| 列表 | `GET /admin/activities` | 管理员 |
| 创建 | `GET/POST /admin/activities/create` | 管理员 |
| 查看 | `GET /admin/activities/{record}` | 管理员 |
| 编辑 | `GET/PUT /admin/activities/{record}/edit` | 管理员 |
| 删除 | `DELETE /admin/activities/{record}` | 管理员 |

**表单字段**：
- 基本信息：标题、Slug、简介、详细内容
- 时间设置：开始时间、结束时间、报名截止时间
- 地点信息：地点名称、详细地址
- 容量设置：容量上限、已预约人数
- 发布设置：状态（草稿/已发布/已取消）、是否推荐
- 媒体文件：封面图片、图片集

---

#### 2.3.2 预约管理 (BookingResource)

| 操作 | 路由 | 权限 |
|-----|------|------|
| 列表 | `GET /admin/bookings` | 管理员 |
| 查看详情 | `GET /admin/bookings/{record}` | 管理员 |
| 编辑状态 | `GET/PUT /admin/bookings/{record}/edit` | 管理员 |

> **说明**：预约不支持通过后台直接创建，只能通过前台用户预约生成。

---

#### 2.3.3 用户管理 (UserResource) - 新增功能

| 操作 | 路由 | 权限 |
|-----|------|------|
| 列表 | `GET /admin/users` | 管理员 |
| 创建 | `GET/POST /admin/users/create` | 管理员 |
| 查看 | `GET /admin/users/{record}` | 管理员 |
| 编辑 | `GET/PUT /admin/users/{record}/edit` | 管理员 |
| 删除 | `DELETE /admin/users/{record}` | 管理员 |

**表单字段**：
- 基本信息：用户名、邮箱、电话、密码
- 权限设置：管理员权限开关 (`is_admin`)
- 头像：头像上传（支持圆形裁剪）

**列表功能**：
- 搜索：用户名、邮箱、电话
- 筛选：管理员/普通用户
- 排序：注册时间
- 统计：预约数量

---

## 3. 数据库 / 数据结构设计 (Schema Design)

### 3.1 数据表设计

#### 3.1.1 users（用户表，Laravel 默认扩展）

| 字段 | 类型 | 约束 | 说明 |
|-----|------|-----|------|
| id | bigint unsigned | PK, AI | 主键 |
| name | varchar(255) | NOT NULL | 用户名 |
| email | varchar(255) | UNIQUE, NOT NULL | 邮箱 |
| email_verified_at | timestamp | NULLABLE | 邮箱验证时间 |
| password | varchar(255) | NOT NULL | 密码哈希 |
| phone | varchar(20) | NULLABLE | 手机号 |
| avatar | varchar(255) | NULLABLE | 头像路径 |
| is_admin | boolean | DEFAULT false | 是否管理员 |
| remember_token | varchar(100) | NULLABLE | 记住令牌 |
| created_at | timestamp | - | 创建时间 |
| updated_at | timestamp | - | 更新时间 |

**索引**：
- UNIQUE(email)
- INDEX(phone)
- INDEX(is_admin)

---

#### 3.1.2 activities（活动表）

| 字段 | 类型 | 约束 | 说明 |
|-----|------|-----|------|
| id | bigint unsigned | PK, AI | 主键 |
| title | varchar(255) | NOT NULL | 活动标题 |
| slug | varchar(255) | UNIQUE | URL 友好标识 |
| description | text | NULLABLE | 简短描述 |
| content | longtext | NULLABLE | 详细内容 (HTML) |
| cover_image | varchar(255) | NULLABLE | 封面图路径 |
| gallery | json | NULLABLE | 图片集 |
| start_time | datetime | NOT NULL | 开始时间 |
| end_time | datetime | NOT NULL | 结束时间 |
| registration_deadline | datetime | NULLABLE | 报名截止时间 |
| location | varchar(255) | NOT NULL | 地点名称 |
| address | varchar(500) | NULLABLE | 详细地址 |
| capacity | int unsigned | NOT NULL | 容量上限 |
| booked_count | int unsigned | DEFAULT 0 | 已预约人数 |
| status | enum | DEFAULT 'draft' | draft/published/cancelled |
| is_featured | boolean | DEFAULT false | 是否推荐 |
| created_by | bigint unsigned | FK(users.id) | 创建者 |
| created_at | timestamp | - | 创建时间 |
| updated_at | timestamp | - | 更新时间 |
| deleted_at | timestamp | NULLABLE | 软删除 |

**索引**：
- UNIQUE(slug)
- INDEX(status, start_time)
- INDEX(start_time, end_time)
- INDEX(created_by)
- INDEX(is_featured)

**约束**：
- `start_time < end_time`
- `registration_deadline <= start_time` (应用层校验)
- `booked_count <= capacity`

---

#### 3.1.3 bookings（预约表）

| 字段 | 类型 | 约束 | 说明 |
|-----|------|-----|------|
| id | bigint unsigned | PK, AI | 主键 |
| booking_code | varchar(30) | UNIQUE, NOT NULL | 预约编号 |
| user_id | bigint unsigned | FK(users.id), NOT NULL | 用户 ID |
| activity_id | bigint unsigned | FK(activities.id), NOT NULL | 活动 ID |
| participants | int unsigned | DEFAULT 1 | 参与人数 |
| status | enum | DEFAULT 'confirmed' | confirmed/cancelled/completed |
| remarks | text | NULLABLE | 备注 |
| cancelled_at | timestamp | NULLABLE | 取消时间 |
| cancellation_reason | varchar(255) | NULLABLE | 取消原因 |
| created_at | timestamp | - | 创建时间 |
| updated_at | timestamp | - | 更新时间 |

**索引**：
- UNIQUE(booking_code)
- UNIQUE(user_id, activity_id) — 防止重复预约
- INDEX(user_id, status)
- INDEX(activity_id, status)
- INDEX(created_at)

**约束**：
- 同一用户不能重复预约同一活动（唯一约束）

---

### 3.2 预约编号生成规则

预约编号 (`booking_code`) 采用以下格式：

```
BK + YYYYMMDD + XXXXXX + MMM
```

| 部分 | 说明 |
|-----|------|
| `BK` | 固定前缀 |
| `YYYYMMDD` | 当前日期，8位 |
| `XXXXXX` | 6位随机数 (000001-999999) |
| `MMM` | 毫秒时间戳后3位 |

**示例**：`BK20251226123456789`

**唯一性保证**：
1. 首次生成后检查数据库唯一性
2. 若冲突，最多重试10次
3. 超过重试次数，使用 MD5 哈希作为备选方案

---

### 3.3 并发与一致性假设

| 场景 | 策略 |
|-----|------|
| 预约时名额竞争 | 使用数据库事务 + 悲观锁（`lockForUpdate()`） |
| 取消预约时释放名额 | 事务内原子操作 |
| booked_count 一致性 | 通过应用层事务保证 |

### 3.4 数据增长与查询模式预期

| 预期指标 | 估算值 |
|---------|--------|
| 活动数量 | 1000+/年 |
| 用户数量 | 10,000+ |
| 预约记录 | 100,000+/年 |
| 主要查询 | 活动列表（按时间筛选）、用户预约历史 |

### 3.5 向后兼容性与数据迁移风险

- 使用 Laravel Migration 管理数据库版本
- 新增字段使用 NULLABLE 或提供默认值
- 软删除保留历史数据
- 枚举类型扩展需注意向后兼容

---

## 4. 前端与交互入口（Frontend & Interaction）

### 4.1 功能入口与触发方式

| 页面/功能 | 路由 | 触发方式 |
|----------|------|----------|
| 首页 | `/` | 直接访问 |
| 活动列表 | `/activities` | 导航点击 |
| 活动详情 | `/activities/{slug}` | 列表点击 |
| 用户注册 | `/register` | 导航点击/预约时跳转 |
| 用户登录 | `/login` | 导航点击/预约时跳转 |
| 用户仪表盘 | `/user/dashboard` | 登录后导航 |
| 我的预约 | `/user/bookings` | 个人中心菜单 |
| 个人资料 | `/user/profile` | 个人中心菜单 |
| 修改密码 | `/user/password` | 个人中心菜单 |
| 管理后台 | `/admin` | 管理员登录后访问 |
| 活动管理 | `/admin/activities` | 后台侧边栏 |
| 预约管理 | `/admin/bookings` | 后台侧边栏 |
| 用户管理 | `/admin/users` | 后台侧边栏 |

### 4.2 与后端契约的边界

| 层级 | 职责 |
|-----|------|
| 前端 | UI 渲染、表单验证（基础）、状态管理、交互反馈 |
| 后端 | 业务逻辑、数据验证（权威）、认证授权、数据持久化 |

### 4.3 认证模型

> **重要说明**：前台和后台共享同一套 Laravel Session 认证机制。

| 区域 | 访问控制 |
|-----|----------|
| 前台公开页面 | 无需认证 |
| 前台预约功能 | 需要登录（`auth` 中间件） |
| 个人中心 | 需要登录（`auth` 中间件） |
| 管理后台 | 需要登录 + `is_admin = true` |

**后台访问控制实现**：

```php
// app/Models/User.php
public function canAccessPanel(Panel $panel): bool
{
    return $this->is_admin === true;
}
```

### 4.4 性能与渲染假设

| 指标 | 目标值 |
|-----|--------|
| 首屏加载 (LCP) | < 2.5s |
| 交互响应 (FID) | < 100ms |
| 布局稳定性 (CLS) | < 0.1 |
| API 响应时间 | < 500ms |

**前端技术选型**：
- 前台：Laravel Blade + Alpine.js + Tailwind CSS（美观现代 UI）
- 后台：Filament 4.0（内置组件）

**缓存策略**：
- 活动列表页：服务端缓存 5 分钟
- 静态资源：CDN + 长期缓存
- 图片：懒加载 + WebP 格式

---

## 5. 核心业务流程 (Business Flow)

### 5.1 用户预约活动流程

```
[开始]
   │
   ▼
[用户浏览活动列表]
   │
   ▼
[用户点击活动进入详情页]
   │
   ▼
[用户点击"立即预约"]
   │
   ├── 未登录 ──► [跳转登录页] ──► [登录成功] ──┐
   │                                           │
   ▼ 已登录 ◄──────────────────────────────────┘
   │
   ▼
[检查活动状态]
   │
   ├── 活动已结束/已取消 ──► [显示错误提示] ──► [结束]
   │
   ├── 已过报名截止时间 ──► [显示错误提示] ──► [结束]
   │
   ├── 名额已满 ──► [显示"已满"提示] ──► [结束]
   │
   ├── 用户已预约过 ──► [显示"已预约"提示] ──► [结束]
   │
   ▼ 检查通过
   │
[显示预约确认弹窗]
   │
   ▼
[用户填写参与人数/备注]
   │
   ▼
[用户点击"确认预约"]
   │
   ▼
[开启数据库事务]
   │
   ▼
[加锁查询活动记录]
   │
   ▼
[再次检查名额]
   │
   ├── 名额不足 ──► [回滚事务] ──► [返回错误] ──► [结束]
   │
   ▼
[创建预约记录]
   │
   ▼
[更新活动 booked_count]
   │
   ▼
[提交事务]
   │
   ▼
[返回预约成功信息]
   │
   ▼
[显示预约成功页面]
   │
   ▼
[结束]
```

### 5.2 用户取消预约流程

```
[开始]
   │
   ▼
[用户进入"我的预约"]
   │
   ▼
[用户点击"取消预约"]
   │
   ▼
[显示确认弹窗（包含取消原因输入）]
   │
   ├── 用户点击"取消" ──► [关闭弹窗] ──► [结束]
   │
   ▼ 用户点击"确认"
   │
   ▼
[检查活动时间]
   │
   ├── 活动已开始/已结束 ──► [返回错误"活动已开始，无法取消"] ──► [结束]
   │
   ▼
[开启数据库事务]
   │
   ▼
[更新预约状态为 cancelled]
   │
   ▼
[记录取消时间和原因]
   │
   ▼
[减少活动 booked_count]
   │
   ▼
[提交事务]
   │
   ▼
[返回成功信息]
   │
   ▼
[结束]
```

### 5.3 管理员发布活动流程

```
[开始]
   │
   ▼
[管理员登录后台]
   │
   ▼
[进入活动管理页面]
   │
   ▼
[点击"新建活动"]
   │
   ▼
[填写活动信息表单]
   │
   ├── 上传封面图片 ──► [存储到 public disk]
   │
   ├── 上传图片集 ──► [批量存储到 public disk]
   │
   ▼
[选择发布状态（草稿/发布）]
   │
   ▼
[点击"保存"]
   │
   ▼
[后端验证表单数据]
   │
   ├── 验证失败 ──► [返回错误信息] ──► [显示错误] ──► [继续编辑]
   │
   ▼
[创建活动记录]
   │
   ▼
[返回成功]
   │
   ▼
[跳转到活动列表]
   │
   ▼
[结束]
```

### 5.4 管理员管理用户流程（新增）

```
[开始]
   │
   ▼
[管理员登录后台]
   │
   ▼
[进入用户管理页面]
   │
   ▼
[查看用户列表]
   │
   ├── 搜索/筛选用户 ──► [显示筛选结果]
   │
   ├── 点击"新建用户" ──► [创建用户流程]
   │
   ├── 点击"编辑" ──► [编辑用户流程]
   │
   ├── 点击"查看" ──► [查看用户详情]
   │
   ▼
[结束]
```

### 5.5 与外部系统交互节点

| 节点 | 外部系统 | 说明 |
|-----|---------|------|
| 文件上传 | 文件存储系统 (local/S3) | 活动图片、用户头像存储 |
| 邮件通知 | 邮件服务 (SMTP/Mailgun) | 预约确认通知（待确认） |

---

## 6. 边缘情况与一致性问题 (Edge Cases & Consistency)

### 6.1 并发预约场景

| 场景 | 问题 | 解决方案 |
|-----|------|----------|
| 多人同时预约最后名额 | 超卖风险 | 悲观锁 + 事务 |
| 预约时活动被取消 | 数据不一致 | 事务内检查活动状态 |
| 预约时活动被编辑 | 数据竞争 | 版本号或锁机制 |

### 6.2 幂等性与去重策略

| 操作 | 策略 |
|-----|------|
| 重复预约同一活动 | 数据库唯一约束 (user_id, activity_id) |
| 重复取消预约 | 检查当前状态，已取消则返回成功 |
| 表单重复提交 | 前端禁用按钮 + 后端幂等性检查 |

### 6.3 异常中断后的恢复假设

| 场景 | 恢复策略 |
|-----|----------|
| 事务中途失败 | 自动回滚，数据保持一致 |
| 网络中断 | 前端重试 + 后端幂等性保证 |
| 服务重启 | 无状态设计，请求可重新发起 |

---

## 7. 非功能性需求（Non-Functional Requirements）

### 7.1 性能 (Performance)

| 指标 | 目标值 | 测量方式 |
|-----|--------|----------|
| 页面首屏加载 | < 3s (3G) | Lighthouse |
| API 响应时间 | P95 < 500ms | APM 监控 |
| 数据库查询 | < 50ms | 慢查询日志 |
| 并发预约处理 | 100 req/s | 压力测试 |

### 7.2 可用性 (Availability)

| 指标 | 目标值 |
|-----|--------|
| 系统可用性 | 99.5% |
| 计划内维护窗口 | 凌晨 2:00-4:00 |
| 故障恢复时间 (RTO) | < 1h |

### 7.3 可扩展性 (Scalability)

- 水平扩展：支持多实例部署（无状态应用）
- 数据库扩展：主从分离（读写分离）
- 缓存扩展：Redis 集群
- 文件存储：S3 兼容存储

### 7.4 可维护性 (Maintainability)

- 代码规范：遵循 PSR-12
- 版本管理：Git Flow 分支策略
- 依赖管理：Composer 锁定版本
- 配置管理：环境变量分离

### 7.5 可测试性 (Testability)

| 测试类型 | 覆盖目标 |
|---------|----------|
| 单元测试 | Service 层 80% |
| 功能测试 | 核心流程 100% |
| 集成测试 | API 端点 100% |

**测试框架**：PHPUnit 10.x + Laravel Testing

---

## 8. 信息安全、合规与审计 (Security, Compliance & Audit)

### 8.1 认证与授权边界

| 区域 | 认证方式 | 授权控制 |
|-----|---------|----------|
| 前台公开页面 | 无需认证 | - |
| 前台预约功能 | Session Cookie | 登录用户 |
| 个人中心 | Session Cookie | 仅限本人 |
| 管理后台 | Session Cookie + canAccessPanel | 管理员角色 (`is_admin = true`) |

### 8.2 敏感数据处理原则

| 数据类型 | 处理方式 |
|---------|----------|
| 用户密码 | bcrypt 哈希存储 |
| 用户邮箱 | 存储时保护，日志脱敏 |
| 手机号码 | 显示时部分隐藏 |
| Session | HttpOnly + Secure Cookie |

### 8.3 日志与审计要求

| 事件 | 记录内容 |
|-----|----------|
| 用户登录 | user_id, IP, 时间, 结果 |
| 预约创建 | user_id, activity_id, 时间 |
| 预约取消 | user_id, booking_id, 时间, 原因 |
| 活动发布 | admin_id, activity_id, 时间 |
| 活动编辑 | admin_id, activity_id, 变更字段 |
| 用户创建（后台） | admin_id, new_user_id, 时间 |
| 用户编辑（后台） | admin_id, user_id, 变更字段 |

### 8.4 第三方依赖信任假设

| 依赖 | 信任级别 | 审核频率 |
|-----|---------|----------|
| Laravel Framework | 高 | 跟随安全更新 |
| Filament | 高 | 跟随安全更新 |
| Composer 依赖 | 中 | composer audit 定期检查 |

### 8.5 合规性考虑

| 法规 | 状态 | 说明 |
|-----|------|------|
| 个人资料保护 | 待确认 | 需确认适用法规（台湾个资法/GDPR） |
| 用户数据删除权 | 待确认 | 需提供账号注销功能 |
| Cookie 同意 | 待确认 | 需评估是否需要 Cookie Banner |

---

## 9. 日志、监控与可观测性 (Observability)

### 9.1 关键业务事件记录

| 事件类型 | 日志级别 | 记录位置 |
|---------|---------|----------|
| 用户注册成功 | INFO | activity.log |
| 用户登录成功/失败 | INFO/WARNING | auth.log |
| 预约创建 | INFO | booking.log |
| 预约取消 | INFO | booking.log |
| 预约失败（名额不足） | WARNING | booking.log |
| 活动发布 | INFO | activity.log |
| 用户管理操作 | INFO | admin.log |
| 系统错误 | ERROR | laravel.log |

### 9.2 成功/失败行为可观测性

| 指标 | 监控方式 |
|-----|----------|
| API 请求成功率 | 应用日志 + 监控面板 |
| 预约转化率 | 业务埋点 |
| 错误率 | 异常监控 (Sentry/Bugsnag) |
| 响应时间分布 | APM 工具 |

### 9.3 不记录内容与原因

| 内容 | 原因 |
|-----|------|
| 用户密码明文 | 安全合规 |
| 完整信用卡号 | 不适用（本期无支付） |
| 调试级别日志（生产） | 性能与存储考虑 |

---

## 10. 验收标准 (Acceptance Criteria)

### 10.1 前台功能

#### AC-001: 活动列表展示
- **Given**: 系统中存在已发布的活动
- **When**: 用户访问活动列表页面
- **Then**: 显示活动卡片列表，包含标题、封面图、时间、地点
- **And**: 支持分页加载
- **And**: 首屏加载时间 < 3s

#### AC-002: 活动详情展示
- **Given**: 存在一个已发布的活动
- **When**: 用户点击活动卡片
- **Then**: 跳转到活动详情页
- **And**: 显示完整活动信息（标题、描述、图片、时间、地点、剩余名额）

#### AC-003: 用户注册
- **Given**: 用户访问注册页面
- **When**: 用户填写有效的邮箱、密码并提交
- **Then**: 账号创建成功
- **And**: 自动登录并跳转到首页

#### AC-004: 用户登录
- **Given**: 用户已注册账号
- **When**: 用户使用正确的邮箱和密码登录
- **Then**: 登录成功
- **And**: 显示登录状态（用户名/头像）

#### AC-005: 活动预约
- **Given**: 用户已登录，活动有剩余名额
- **When**: 用户点击预约按钮并确认
- **Then**: 预约成功
- **And**: 显示预约编号
- **And**: 活动剩余名额减少

#### AC-006: 重复预约拦截
- **Given**: 用户已预约某活动
- **When**: 用户尝试再次预约该活动
- **Then**: 系统拒绝预约
- **And**: 显示"已预约"提示

### 10.2 个人中心功能

#### AC-007: 用户仪表盘
- **Given**: 用户已登录
- **When**: 用户访问个人中心仪表盘
- **Then**: 显示预约统计信息
- **And**: 显示近期预约列表
- **And**: 显示快捷操作链接

#### AC-008: 查看我的预约
- **Given**: 用户已登录且有预约记录
- **When**: 用户访问"我的预约"页面
- **Then**: 显示预约列表（包含活动信息、预约状态）

#### AC-009: 取消预约
- **Given**: 用户有一个未开始活动的预约
- **When**: 用户点击取消并确认
- **Then**: 预约状态变为"已取消"
- **And**: 活动剩余名额增加

#### AC-010: 编辑个人资料
- **Given**: 用户已登录
- **When**: 用户修改姓名/手机号并保存
- **Then**: 资料更新成功
- **And**: 页面显示更新后的信息

### 10.3 管理后台功能

#### AC-011: 创建活动
- **Given**: 管理员已登录后台
- **When**: 管理员填写活动信息、上传图片并保存
- **Then**: 活动创建成功
- **And**: 图片正确存储

#### AC-012: 编辑活动
- **Given**: 存在一个活动
- **When**: 管理员修改活动信息并保存
- **Then**: 活动更新成功

#### AC-013: 查看预约列表
- **Given**: 管理员已登录后台
- **When**: 管理员访问预约管理页面
- **Then**: 显示所有预约记录
- **And**: 支持按活动、用户、状态筛选

#### AC-014: 管理用户（新增）
- **Given**: 管理员已登录后台
- **When**: 管理员访问用户管理页面
- **Then**: 显示所有用户列表
- **And**: 可以创建新用户
- **And**: 可以编辑用户信息
- **And**: 可以设置/取消管理员权限

### 10.4 非功能性验收

| 项目 | 标准 |
|-----|------|
| 性能 | API P95 < 500ms |
| 安全 | 无 OWASP Top 10 漏洞 |
| 并发 | 100 并发预约不超卖 |
| 可用性 | 核心功能 100% 可用 |

---

## 11. 风险、限制与技术决策 (Risks & Trade-offs)

### 11.1 已识别风险

| 风险 | 影响 | 可能性 | 缓解措施 |
|-----|------|--------|----------|
| 高并发预约导致超卖 | 高 | 中 | 悲观锁 + 事务 |
| 图片存储空间不足 | 中 | 低 | 监控 + 迁移至 S3 |
| SQLite 并发限制 | 高 | 高 | 迁移至 MySQL/PostgreSQL |
| 第三方依赖漏洞 | 中 | 低 | 定期安全扫描 |

### 11.2 架构取舍及原因

| 决策 | 选择 | 原因 |
|-----|------|------|
| 前端技术 | Blade + Alpine.js | 快速开发，SEO 友好，符合美观现代要求 |
| 后台框架 | Filament 4.0 | Laravel 生态集成，开发效率高 |
| 认证方式 | Session-based | 传统 Web 应用，简单可靠 |
| 预约锁机制 | 悲观锁 | 简单可靠，适合中小规模并发 |
| 数据库 | 建议迁移至 MySQL | SQLite 不适合生产并发 |
| 用户管理 | 后台独立模块 | 便于管理员统一管理用户账号和权限 |

### 11.3 延后处理的问题与演进方向

| 问题 | 延后原因 | 演进方向 |
|-----|---------|----------|
| 支付集成 | 需求未确认 | 预留接口，后续集成 |
| 消息推送 | 非核心功能 | 添加队列任务支持 |
| 多语言 | 本期范围外 | 使用 Laravel Localization |
| 活动评价 | 非核心功能 | 作为独立模块扩展 |
| 社交登录 | 非核心功能 | Socialite 扩展包 |

---

## 附录

### A. 技术栈确认

| 组件 | 版本 | 说明 |
|-----|------|------|
| PHP | 8.2+ | 运行环境 |
| Laravel | 12.x | Web 框架 |
| Filament | 4.0 | 管理后台 |
| Tailwind CSS | 3.x | 前端样式 |
| Alpine.js | 3.x | 前端交互 |
| MySQL | 8.0+ | 生产数据库（建议） |

### B. Filament 4.0 技术说明

> **重要**：Filament 4.0 相比之前版本有重大变更。

#### B.1 命名空间变更

| 组件类型 | 旧命名空间 | 新命名空间 |
|---------|-----------|-----------|
| 表单输入组件 | `Filament\Forms\Components` | `Filament\Forms\Components` |
| 布局组件 | `Filament\Forms\Components` | `Filament\Schemas\Components` |
| 表格操作 | `Filament\Tables\Actions` | `Filament\Actions` |

#### B.2 类型声明

Resource 类的静态属性需要特定类型：

```php
protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
protected static string | \BackedEnum | null $navigationGroup = '系统管理';
```

#### B.3 表单方法签名

```php
public static function form(Filament\Schemas\Schema $schema): Filament\Schemas\Schema
```

### C. 待确认事项清单

| 编号 | 事项 | 负责人 | 状态 |
|-----|------|--------|------|
| 1 | 是否需要在线支付功能 | PM | 待确认 |
| 2 | 邮件通知需求范围 | PM | 待确认 |
| 3 | 适用的隐私法规 | 法务 | 待确认 |
| 4 | 前台 UI 设计稿 | 设计师 | 待提供 |
| 5 | 生产环境部署方案 | 运维 | 待确认 |

### D. 项目结构

```
app/
├── Filament/
│   └── Resources/
│       ├── ActivityResource.php       # 活动管理
│       ├── ActivityResource/Pages/
│       ├── BookingResource.php         # 预约管理
│       ├── BookingResource/Pages/
│       ├── UserResource.php            # 用户管理（新增）
│       └── UserResource/Pages/
├── Http/
│   └── Controllers/
│       ├── ActivityController.php      # 活动前台控制器
│       ├── BookingController.php       # 预约控制器
│       ├── HomeController.php          # 首页控制器
│       └── UserController.php          # 用户中心控制器
├── Models/
│   ├── Activity.php
│   ├── Booking.php
│   └── User.php
└── Services/
    ├── ActivityService.php
    └── BookingService.php

database/
├── factories/
│   ├── ActivityFactory.php
│   └── BookingFactory.php
└── migrations/
    ├── 2025_12_26_000001_create_activities_table.php
    ├── 2025_12_26_000002_create_bookings_table.php
    └── 2025_12_26_000003_add_fields_to_users_table.php

resources/views/
├── activities/
│   ├── index.blade.php
│   └── show.blade.php
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── components/
│   ├── activity-card.blade.php
│   └── activity-card-compact.blade.php
├── layouts/
│   └── app.blade.php
├── user/
│   ├── bookings.blade.php
│   ├── dashboard.blade.php
│   └── profile.blade.php
└── home.blade.php

tests/
├── Feature/
│   ├── Models/
│   │   ├── ActivityTest.php
│   │   └── BookingTest.php
│   └── Services/
│       ├── ActivityServiceTest.php
│       └── BookingServiceTest.php
└── Unit/
    ├── Models/
    │   ├── ActivityTest.php
    │   └── BookingTest.php
    └── Services/
        ├── ActivityServiceTest.php
        └── BookingServiceTest.php
```

---

*文档结束*
