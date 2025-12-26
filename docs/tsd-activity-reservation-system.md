<!--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
-->

# 活动预约系统 - 技术设计文档（TSD）

| 版本 | 日期 | 作者 | 说明 |
|------|------|------|------|
| 1.0 | 2025-12-26 | Architecture Team | 初始版本 |

---

## 目录

1. [背景与目标](#1-背景与目标-background--goals)
2. [核心接口定义](#2-核心接口定义-api--service-contracts)
3. [数据库/数据结构设计](#3-数据库数据结构设计-schema-design)
4. [前端与交互入口](#4-前端与交互入口)
5. [核心业务流程](#5-核心业务流程-business-flow)
6. [边缘情况与一致性问题](#6-边缘情况与一致性问题-edge-cases--consistency)
7. [非功能性需求](#7-非功能性需求non-functional-requirements)
8. [信息安全、合规与审计](#8-信息安全合规与审计-security-compliance--audit)
9. [日志、监控与可观测性](#9-日志监控与可观测性-observability)
10. [验收标准](#10-验收标准-acceptance-criteria)
11. [风险、限制与技术决策](#11-风险限制与技术决策-risks--trade-offs)

---

## 1. 背景与目标 (Background & Goals)

### 1.1 业务与技术痛点

| 类型 | 痛点描述 |
|------|----------|
| 业务层面 | 当前缺乏统一的活动发布与预约管理平台，活动信息分散，用户预约流程繁琐 |
| 运营层面 | 无法实时掌握活动预约情况，名额管理依赖人工统计，易出错 |
| 用户体验 | 用户无法在线查看活动详情、实时预约，缺乏个人预约记录管理能力 |
| 技术层面 | 需要建立前后台分离的用户体系，确保管理员与普通用户的权限隔离 |

### 1.2 本需求明确解决的问题

1. **活动信息集中管理**：提供后台管理界面，支持活动的创建、编辑、发布与下架
2. **在线预约能力**：用户可在线浏览活动并完成预约，系统自动管理名额
3. **用户自助服务**：用户可查看个人预约记录、取消预约、管理个人资料
4. **权限隔离**：前台用户与后台管理员使用独立的认证体系

### 1.3 架构目标

| 目标 | 说明 |
|------|------|
| 可扩展性 (Scalability) | 支持活动数量与用户规模的线性增长，预留多租户扩展能力 |
| 可维护性 (Maintainability) | 遵循 Laravel 最佳实践，业务逻辑封装于 Service 层，便于测试与维护 |
| 可审计性 (Auditability) | 关键业务操作（预约、取消、活动发布）均有日志记录，支持追溯 |
| 数据一致性 (Consistency) | 预约操作保证原子性，名额计数准确，防止超卖 |
| 安全性 (Security) | 前后台认证隔离，敏感操作需鉴权，防止越权访问 |

### 1.4 Out of Scope（本期不解决但已识别的问题）

| 项目 | 说明 | 建议后续版本 |
|------|------|--------------|
| 支付功能 | 付费活动的在线支付 | v2.0 |
| 活动签到 | 现场扫码签到功能 | v2.0 |
| 消息通知 | 预约成功/活动提醒的邮件/短信通知 | v1.5 |
| 多语言支持 | 系统界面国际化 | v2.0 |
| API 开放 | 对外提供 RESTful API | v2.0 |
| 活动分类/标签 | 活动分类管理与筛选 | v1.5 |

---

## 2. 核心接口定义 (API / Service Contracts)

### 2.1 系统边界概览

```
┌─────────────────────────────────────────────────────────────────┐
│                         前台系统 (Frontend)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐   │
│  │ 活动浏览页面  │  │ 预约操作页面  │  │ 个人中心页面          │   │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬───────────┘   │
│         │                 │                      │               │
│         ▼                 ▼                      ▼               │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                   Web Routes (Blade/Livewire)            │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       Service Layer                              │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │ ActivityService │  │ReservationService│  │   UserService   │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       Data Layer (Eloquent)                      │
│  ┌────────┐  ┌────────┐  ┌────────────┐  ┌─────────────────┐    │
│  │ Admin  │  │  User  │  │  Activity  │  │   Reservation   │    │
│  └────────┘  └────────┘  └────────────┘  └─────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 前台路由定义

#### 2.2.1 公开路由（无需认证）

| 路由 | 方法 | 说明 | 响应 |
|------|------|------|------|
| `/` | GET | 首页，展示热门活动 | View |
| `/activities` | GET | 活动列表页（分页） | View |
| `/activities/{activity}` | GET | 活动详情页 | View |
| `/register` | GET/POST | 用户注册 | View/Redirect |
| `/login` | GET/POST | 用户登录 | View/Redirect |
| `/logout` | POST | 用户登出 | Redirect |

#### 2.2.2 需认证路由（User Guard）

| 路由 | 方法 | 说明 | 响应 |
|------|------|------|------|
| `/activities/{activity}/reserve` | POST | 预约活动 | Redirect |
| `/my/reservations` | GET | 我的预约列表 | View |
| `/my/reservations/{reservation}/cancel` | POST | 取消预约 | Redirect |
| `/my/profile` | GET | 个人资料页 | View |
| `/my/profile` | PUT | 更新个人资料 | Redirect |
| `/my/password` | PUT | 修改密码 | Redirect |

### 2.3 后台路由定义（Filament Admin Panel）

| 资源 | 路径前缀 | 说明 |
|------|----------|------|
| ActivityResource | `/admin/activities` | 活动 CRUD |
| ReservationResource | `/admin/reservations` | 预约管理（只读+状态变更） |
| UserResource | `/admin/users` | 前台用户管理（只读+禁用） |

### 2.4 Service 层接口契约

#### 2.4.1 ActivityService

```
interface ActivityServiceContract
{
    /**
     * 获取已发布活动列表（分页）
     * 
     * @param int $perPage 每页数量，默认 15
     * @return LengthAwarePaginator<Activity>
     * @throws None
     */
    public function getPublishedActivities(int $perPage = 15): LengthAwarePaginator;

    /**
     * 获取活动详情
     * 
     * @param int $activityId 活动 ID
     * @return Activity
     * @throws ModelNotFoundException 活动不存在
     */
    public function getActivityById(int $activityId): Activity;

    /**
     * 检查活动是否可预约
     * 
     * @param Activity $activity
     * @return bool
     */
    public function isReservable(Activity $activity): bool;

    /**
     * 获取活动剩余名额
     * 
     * @param Activity $activity
     * @return int
     */
    public function getRemainingCapacity(Activity $activity): int;
}
```

#### 2.4.2 ReservationService

```
interface ReservationServiceContract
{
    /**
     * 创建预约
     * 
     * @param User $user 预约用户
     * @param Activity $activity 目标活动
     * @param array $data 附加数据 ['remark' => string|null]
     * @return Reservation
     * @throws ActivityNotReservableException 活动不可预约（已结束/已满/未发布）
     * @throws DuplicateReservationException 用户已预约该活动
     * @throws CapacityExceededException 名额已满（并发竞争时）
     * @sideEffects 
     *   - 创建 Reservation 记录
     *   - 增加 Activity.reserved_count
     *   - 记录审计日志
     */
    public function createReservation(User $user, Activity $activity, array $data = []): Reservation;

    /**
     * 取消预约
     * 
     * @param Reservation $reservation
     * @return Reservation
     * @throws ReservationNotCancellableException 预约不可取消（已过期/已取消）
     * @sideEffects
     *   - 更新 Reservation.status 为 cancelled
     *   - 减少 Activity.reserved_count
     *   - 记录审计日志
     */
    public function cancelReservation(Reservation $reservation): Reservation;

    /**
     * 获取用户的预约列表
     * 
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator<Reservation>
     */
    public function getUserReservations(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * 检查用户是否已预约某活动
     * 
     * @param User $user
     * @param Activity $activity
     * @return bool
     */
    public function hasUserReserved(User $user, Activity $activity): bool;
}
```

### 2.5 失败时责任归属

| 错误场景 | HTTP 状态码 | 责任方 | 处理方式 |
|----------|-------------|--------|----------|
| 活动不存在 | 404 | 调用方 | 返回 404 页面 |
| 未登录访问预约接口 | 401 | 调用方 | 重定向至登录页 |
| 活动已满 | 422 | 业务规则 | 返回错误消息，引导用户 |
| 重复预约 | 422 | 业务规则 | 返回错误消息 |
| 活动已结束 | 422 | 业务规则 | 返回错误消息 |
| 数据库异常 | 500 | 系统 | 记录日志，返回通用错误页 |

### 2.6 幂等性与副作用假设

| 操作 | 幂等性 | 说明 |
|------|--------|------|
| 活动列表查询 | 是 | 无副作用 |
| 活动详情查询 | 是 | 无副作用 |
| 创建预约 | 否 | 存在唯一约束保护，重复请求返回错误 |
| 取消预约 | 是 | 多次取消返回相同结果 |
| 更新个人资料 | 是 | 相同数据多次提交结果一致 |

---

## 3. 数据库/数据结构设计 (Schema Design)

### 3.1 ER 图

```
┌─────────────┐       ┌─────────────────────┐       ┌─────────────────────┐
│   admins    │       │     activities      │       │    reservations     │
├─────────────┤       ├─────────────────────┤       ├─────────────────────┤
│ id (PK)     │──┐    │ id (PK)             │──┐    │ id (PK)             │
│ name        │  │    │ admin_id (FK)       │◄─┘    │ user_id (FK)        │◄─┐
│ email (UK)  │  │    │ title               │       │ activity_id (FK)    │◄─┼─┐
│ password    │  └───►│ description         │       │ status              │  │ │
│ created_at  │       │ cover_image         │       │ remark              │  │ │
│ updated_at  │       │ start_time          │       │ reserved_at         │  │ │
└─────────────┘       │ end_time            │       │ cancelled_at        │  │ │
                      │ capacity            │       │ created_at          │  │ │
┌─────────────┐       │ reserved_count      │       │ updated_at          │  │ │
│   users     │       │ status              │       └─────────────────────┘  │ │
├─────────────┤       │ created_at          │                                │ │
│ id (PK)     │───────┼─updated_at          │◄───────────────────────────────┘ │
│ name        │       └─────────────────────┘                                  │
│ email (UK)  │                                                                │
│ phone       │────────────────────────────────────────────────────────────────┘
│ password    │
│ email_verified_at │
│ created_at  │
│ updated_at  │
└─────────────┘
```

### 3.2 表结构定义

#### 3.2.1 admins 表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 主键 |
| name | VARCHAR(255) | NOT NULL | 管理员名称 |
| email | VARCHAR(255) | NOT NULL, UNIQUE | 登录邮箱 |
| password | VARCHAR(255) | NOT NULL | 密码（bcrypt） |
| remember_token | VARCHAR(100) | NULLABLE | 记住登录 Token |
| created_at | TIMESTAMP | NULLABLE | 创建时间 |
| updated_at | TIMESTAMP | NULLABLE | 更新时间 |

**索引**：
- `PRIMARY KEY (id)`
- `UNIQUE INDEX admins_email_unique (email)`

#### 3.2.2 users 表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 主键 |
| name | VARCHAR(255) | NOT NULL | 用户姓名 |
| email | VARCHAR(255) | NOT NULL, UNIQUE | 登录邮箱 |
| phone | VARCHAR(20) | NULLABLE | 联系电话 |
| password | VARCHAR(255) | NOT NULL | 密码（bcrypt） |
| email_verified_at | TIMESTAMP | NULLABLE | 邮箱验证时间 |
| remember_token | VARCHAR(100) | NULLABLE | 记住登录 Token |
| created_at | TIMESTAMP | NULLABLE | 创建时间 |
| updated_at | TIMESTAMP | NULLABLE | 更新时间 |

**索引**：
- `PRIMARY KEY (id)`
- `UNIQUE INDEX users_email_unique (email)`

#### 3.2.3 activities 表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 主键 |
| admin_id | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | 创建者管理员 ID |
| title | VARCHAR(255) | NOT NULL | 活动标题 |
| description | TEXT | NULLABLE | 活动详细描述 |
| cover_image | VARCHAR(255) | NULLABLE | 封面图路径 |
| start_time | DATETIME | NOT NULL | 活动开始时间 |
| end_time | DATETIME | NOT NULL | 活动结束时间 |
| capacity | INT UNSIGNED | NOT NULL, DEFAULT 0 | 最大容量（0 表示无限制） |
| reserved_count | INT UNSIGNED | NOT NULL, DEFAULT 0 | 已预约人数 |
| status | ENUM('draft','published','cancelled') | NOT NULL, DEFAULT 'draft' | 活动状态 |
| created_at | TIMESTAMP | NULLABLE | 创建时间 |
| updated_at | TIMESTAMP | NULLABLE | 更新时间 |

**索引**：
- `PRIMARY KEY (id)`
- `INDEX activities_admin_id_index (admin_id)`
- `INDEX activities_status_start_time_index (status, start_time)`
- `FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE RESTRICT`

**状态枚举说明**：
- `draft`：草稿，前台不可见
- `published`：已发布，前台可见可预约
- `cancelled`：已取消，前台可见但不可预约

#### 3.2.4 reservations 表

| 字段 | 类型 | 约束 | 说明 |
|------|------|------|------|
| id | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | 主键 |
| user_id | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | 预约用户 ID |
| activity_id | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY | 活动 ID |
| status | ENUM('pending','confirmed','cancelled','expired') | NOT NULL, DEFAULT 'pending' | 预约状态 |
| remark | TEXT | NULLABLE | 用户备注 |
| reserved_at | TIMESTAMP | NOT NULL | 预约时间 |
| cancelled_at | TIMESTAMP | NULLABLE | 取消时间 |
| created_at | TIMESTAMP | NULLABLE | 创建时间 |
| updated_at | TIMESTAMP | NULLABLE | 更新时间 |

**索引**：
- `PRIMARY KEY (id)`
- `UNIQUE INDEX reservations_user_activity_unique (user_id, activity_id)` — 防止重复预约
- `INDEX reservations_activity_id_status_index (activity_id, status)`
- `INDEX reservations_user_id_index (user_id)`
- `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`
- `FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE`

**状态枚举说明**：
- `pending`：待确认（预留，本期可直接使用 confirmed）
- `confirmed`：已确认
- `cancelled`：已取消
- `expired`：已过期（活动结束后自动标记，可通过 Scheduled Task 处理）

### 3.3 并发与一致性假设

#### 3.3.1 预约操作的并发控制

```
-- 预约时使用悲观锁确保名额一致性
BEGIN TRANSACTION;

SELECT id, capacity, reserved_count 
FROM activities 
WHERE id = :activity_id 
FOR UPDATE;

-- 检查 capacity > reserved_count
-- 检查用户未重复预约

INSERT INTO reservations (...);

UPDATE activities 
SET reserved_count = reserved_count + 1 
WHERE id = :activity_id;

COMMIT;
```

#### 3.3.2 取消预约的并发控制

```
BEGIN TRANSACTION;

SELECT id, status FROM reservations WHERE id = :reservation_id FOR UPDATE;

-- 检查 status 允许取消

UPDATE reservations SET status = 'cancelled', cancelled_at = NOW() WHERE id = :reservation_id;

UPDATE activities SET reserved_count = reserved_count - 1 WHERE id = :activity_id;

COMMIT;
```

### 3.4 数据增长与查询模式预期

| 表 | 预期数据量（年） | 主要查询模式 |
|----|------------------|--------------|
| admins | < 100 | 按 email 查询（登录） |
| users | 10,000 - 100,000 | 按 email 查询（登录）；按 id 查询 |
| activities | 500 - 2,000 | 按 status + start_time 范围查询；按 id 查询 |
| reservations | 50,000 - 500,000 | 按 user_id 查询；按 activity_id + status 查询 |

### 3.5 向后兼容性与数据迁移风险

| 风险项 | 说明 | 缓解措施 |
|--------|------|----------|
| 新增 admins 表 | 新表，无兼容性问题 | - |
| 修改 users 表 | 新增 phone 字段 | 使用 NULLABLE，无破坏性 |
| 状态枚举扩展 | 未来可能新增状态值 | 使用 VARCHAR 替代 ENUM，或接受 ALTER TABLE |
| reserved_count 不一致 | 并发操作可能导致计数偏差 | 定期校验任务 + 事务保护 |

---

## 4. 前端与交互入口

### 4.1 页面结构

```
/                           # 首页
├── /activities             # 活动列表
│   └── /activities/{id}    # 活动详情
├── /register               # 用户注册
├── /login                  # 用户登录
└── /my                     # 个人中心
    ├── /my/reservations    # 我的预约
    └── /my/profile         # 个人资料
```

### 4.2 功能入口与触发方式

| 页面 | 入口 | 触发方式 | 认证要求 |
|------|------|----------|----------|
| 首页 | 直接访问 / 导航栏 | URL 访问 | 否 |
| 活动列表 | 导航栏 / 首页链接 | 点击链接 | 否 |
| 活动详情 | 活动列表卡片点击 | 点击链接 | 否 |
| 预约活动 | 活动详情页按钮 | 表单提交 | 是 |
| 我的预约 | 个人中心导航 | 点击链接 | 是 |
| 取消预约 | 预约列表操作按钮 | 确认弹窗后提交 | 是 |
| 个人资料 | 个人中心导航 | 点击链接 | 是 |

### 4.3 与后端契约的边界

| 责任归属 | 前端 | 后端 |
|----------|------|------|
| 数据校验 | 基础格式校验（必填、格式） | 业务规则校验（唯一性、权限） |
| 错误处理 | 展示后端返回的错误消息 | 返回结构化错误响应 |
| 状态管理 | 页面级状态（Livewire/Blade） | Session/Database 持久化 |
| 分页 | 展示分页控件 | 返回分页数据 |

### 4.4 性能与渲染假设

| 指标 | 目标 | 实现方式 |
|------|------|----------|
| 首屏加载 | < 2s (3G 网络) | 服务端渲染 (Blade)；CSS/JS 压缩 |
| 活动列表响应 | < 500ms | 数据库索引；合理分页 (15条/页) |
| 图片加载 | 渐进式 | 懒加载 (loading="lazy")；图片压缩 |
| SEO | 活动详情可被搜索引擎索引 | 服务端渲染；meta 标签 |
| 缓存策略 | 活动列表可短期缓存 | 待确认：是否使用页面缓存或 API 缓存 |

---

## 5. 核心业务流程 (Business Flow)

### 5.1 用户预约活动流程

```
┌─────────────────────────────────────────────────────────────────┐
│                        用户预约流程                              │
└─────────────────────────────────────────────────────────────────┘
        │
        ▼
┌───────────────────┐
│  用户浏览活动详情  │
└─────────┬─────────┘
          │
          ▼
    ┌───────────┐     否
    │ 用户已登录? ├────────────────┐
    └─────┬─────┘                  │
          │ 是                     ▼
          │               ┌───────────────┐
          │               │  跳转登录页面  │
          │               └───────┬───────┘
          │                       │
          │◄──────────────────────┘
          ▼
    ┌───────────────┐    否
    │ 活动可预约?    ├───────────────────────────┐
    │ (已发布且未满) │                            │
    └───────┬───────┘                            ▼
            │ 是                          ┌─────────────┐
            ▼                             │ 显示不可预约 │
    ┌───────────────┐    是               │ 原因提示     │
    │ 用户已预约?    ├───────────────┐     └─────────────┘
    └───────┬───────┘               │
            │ 否                    ▼
            ▼                ┌─────────────┐
    ┌───────────────┐        │ 显示已预约  │
    │ 用户点击预约   │        │ 状态       │
    └───────┬───────┘        └─────────────┘
            │
            ▼
    ┌───────────────┐
    │ 开启数据库事务 │
    └───────┬───────┘
            │
            ▼
    ┌───────────────┐
    │ 锁定活动记录   │
    │ (FOR UPDATE)  │
    └───────┬───────┘
            │
            ▼
    ┌───────────────┐    否
    │ 再次检查名额   ├──────────────────────────┐
    └───────┬───────┘                           │
            │ 是                                ▼
            ▼                           ┌─────────────┐
    ┌───────────────┐                   │ 回滚事务    │
    │ 创建预约记录   │                   │ 返回错误    │
    └───────┬───────┘                   └─────────────┘
            │
            ▼
    ┌───────────────┐
    │ 更新预约计数   │
    │ reserved_count│
    │ += 1          │
    └───────┬───────┘
            │
            ▼
    ┌───────────────┐
    │ 提交事务      │
    │ 记录审计日志  │
    └───────┬───────┘
            │
            ▼
    ┌───────────────┐
    │ 返回预约成功  │
    │ 跳转我的预约  │
    └───────────────┘
```

### 5.2 用户取消预约流程

```
用户访问我的预约列表
        │
        ▼
用户点击取消按钮
        │
        ▼
显示确认弹窗
        │
        ▼
用户确认取消
        │
        ▼
┌───────────────────────┐    否
│ 预约状态允许取消?      ├─────────────────┐
│ (confirmed/pending)   │                  │
└───────────┬───────────┘                  ▼
            │ 是                    ┌─────────────┐
            ▼                       │ 返回错误    │
┌───────────────────────┐           │ 不可取消   │
│ 开启事务，锁定预约记录 │           └─────────────┘
└───────────┬───────────┘
            │
            ▼
┌───────────────────────┐
│ 更新预约状态为cancelled│
│ 设置 cancelled_at     │
└───────────┬───────────┘
            │
            ▼
┌───────────────────────┐
│ 更新活动 reserved_count│
│ -= 1                  │
└───────────┬───────────┘
            │
            ▼
┌───────────────────────┐
│ 提交事务              │
│ 记录审计日志          │
└───────────┬───────────┘
            │
            ▼
┌───────────────────────┐
│ 返回取消成功          │
│ 刷新预约列表          │
└───────────────────────┘
```

### 5.3 活动状态机

```
                    ┌─────────┐
                    │  draft  │ (初始状态)
                    └────┬────┘
                         │
                         │ 发布操作
                         ▼
                    ┌──────────┐
         ┌─────────│ published │─────────┐
         │         └──────────┘         │
         │                              │
         │ 取消操作                     │ 活动结束
         ▼                              ▼
    ┌───────────┐                 ┌──────────┐
    │ cancelled │                 │ (逻辑判断) │
    └───────────┘                 │ 不可预约  │
    (终态，不可逆)                 └──────────┘
```

### 5.4 预约状态机

```
                    ┌───────────┐
                    │  pending  │ (预留，本期可跳过)
                    └─────┬─────┘
                          │
                          │ 自动确认或管理员确认
                          ▼
                    ┌───────────┐
         ┌──────────│ confirmed │──────────┐
         │          └───────────┘          │
         │                                 │
         │ 用户取消                        │ 活动结束
         ▼                                 ▼
    ┌───────────┐                    ┌──────────┐
    │ cancelled │                    │ expired  │
    └───────────┘                    └──────────┘
    (终态)                            (终态)
```

### 5.5 与外部系统交互节点

| 交互点 | 外部系统 | 本期状态 |
|--------|----------|----------|
| 邮件通知 | SMTP 服务 | Out of Scope |
| 短信通知 | SMS Gateway | Out of Scope |
| 支付 | Payment Gateway | Out of Scope |
| 文件存储 | 本地存储 / S3 | 本地存储（可配置） |

---

## 6. 边缘情况与一致性问题 (Edge Cases & Consistency)

### 6.1 并发预约同一活动

| 场景 | 问题描述 | 解决方案 |
|------|----------|----------|
| 最后一个名额竞争 | 多个用户同时预约最后一个名额 | 使用 `SELECT ... FOR UPDATE` 悲观锁，事务内检查名额 |
| 名额计数不一致 | reserved_count 与实际预约数不符 | 定时任务校验；数据库触发器（可选） |

### 6.2 重复预约防护

| 场景 | 问题描述 | 解决方案 |
|------|----------|----------|
| 用户双击提交 | 短时间内发送两次预约请求 | 唯一索引 `(user_id, activity_id)` 保护 |
| 前端重试 | 网络超时后前端自动重试 | 唯一索引保护；幂等响应 |

### 6.3 幂等性与去重策略

| 操作 | 去重策略 |
|------|----------|
| 创建预约 | 数据库唯一索引；捕获唯一约束异常返回友好错误 |
| 取消预约 | 检查当前状态，已取消则直接返回成功 |

### 6.4 数据竞争与唯一性冲突处理

```
try {
    // 在事务中创建预约
    DB::transaction(function () use ($user, $activity) {
        $activity->lockForUpdate();
        
        if ($activity->reserved_count >= $activity->capacity) {
            throw new CapacityExceededException();
        }
        
        Reservation::create([...]);
        $activity->increment('reserved_count');
    });
} catch (UniqueConstraintViolationException $e) {
    // 用户已预约该活动
    throw new DuplicateReservationException();
} catch (CapacityExceededException $e) {
    // 名额已满
    throw $e;
}
```

### 6.5 异常中断后的恢复假设

| 场景 | 影响 | 恢复策略 |
|------|------|----------|
| 事务中途失败 | 无影响，自动回滚 | 数据库事务保证原子性 |
| 预约成功但通知失败 | 用户已预约但未收到通知 | 本期无通知功能；后续版本使用队列异步处理 |
| 活动结束后状态未更新 | expired 状态未标记 | 定时任务扫描并更新 |

---

## 7. 非功能性需求（Non-Functional Requirements）

### 7.1 性能 (Performance)

| 指标 | 目标 | 测量方法 |
|------|------|----------|
| 活动列表页响应时间 | P95 < 500ms | 应用性能监控 |
| 活动详情页响应时间 | P95 < 300ms | 应用性能监控 |
| 预约操作响应时间 | P95 < 1s | 应用性能监控 |
| 并发预约处理能力 | 100 TPS | 压力测试 |

### 7.2 可用性 (Availability)

| 指标 | 目标 | 说明 |
|------|------|------|
| 系统可用性 | 99.5% (月度) | 允许每月约 3.6 小时计划内维护 |
| 计划内维护窗口 | 凌晨 2:00-4:00 | 低峰期执行 |
| 故障恢复时间 (RTO) | < 4 小时 | - |
| 数据恢复点 (RPO) | < 1 小时 | 数据库定时备份 |

### 7.3 可扩展性 (Scalability)

| 维度 | 当前设计 | 扩展路径 |
|------|----------|----------|
| 用户规模 | 支持 10 万用户 | 数据库读写分离 |
| 活动数量 | 支持 1 万活动 | 归档历史活动 |
| 并发请求 | 单机 100 QPS | 水平扩展 + 负载均衡 |
| 文件存储 | 本地存储 | 迁移至 S3/OSS |

### 7.4 可维护性 (Maintainability)

| 方面 | 实践 |
|------|------|
| 代码规范 | 遵循 PSR-12；使用 Laravel Pint 格式化 |
| 分层架构 | Controller -> Service -> Repository (可选) -> Model |
| 配置管理 | 环境变量配置；config 文件集中管理 |
| 依赖管理 | Composer 锁定版本；定期更新安全补丁 |

### 7.5 可测试性 (Testability)

| 层级 | 测试类型 | 覆盖目标 |
|------|----------|----------|
| Unit | Service 方法 | 核心业务逻辑 80% |
| Feature | HTTP 请求 | 关键用户流程 100% |
| Integration | 数据库操作 | 并发场景 |
| E2E | 浏览器测试 | 关键路径（待确认是否本期实施） |

---

## 8. 信息安全、合规与审计 (Security, Compliance & Audit)

### 8.1 认证/授权边界

| 边界 | 认证方式 | Guard | 说明 |
|------|----------|-------|------|
| 前台用户 | Session-based | web | 使用 User 模型 |
| 后台管理员 | Session-based | admin | 使用 Admin 模型；Filament 内置认证 |

**权限隔离**：
- 前台用户无法访问 `/admin/*` 路径
- 后台管理员无法以用户身份预约活动
- 使用 Laravel 中间件强制执行

### 8.2 敏感数据处理原则

| 数据类型 | 处理方式 |
|----------|----------|
| 用户密码 | bcrypt 哈希存储；传输使用 HTTPS |
| 用户邮箱 | 加密存储（待确认）；日志脱敏 |
| 用户电话 | 日志脱敏；展示时部分隐藏 |
| Session 数据 | 加密存储于数据库 |

### 8.3 日志与审计要求

**必须记录的审计事件**：

| 事件 | 记录内容 | 保留期限 |
|------|----------|----------|
| 用户注册 | user_id, email, ip, timestamp | 2 年 |
| 用户登录/登出 | user_id, ip, user_agent, timestamp | 1 年 |
| 预约创建 | user_id, activity_id, timestamp | 2 年 |
| 预约取消 | user_id, reservation_id, timestamp | 2 年 |
| 活动发布 | admin_id, activity_id, timestamp | 2 年 |
| 活动取消 | admin_id, activity_id, reason, timestamp | 2 年 |

### 8.4 第三方依赖信任假设

| 依赖 | 信任级别 | 风险缓解 |
|------|----------|----------|
| Laravel Framework | 高 | 定期更新；订阅安全公告 |
| Filament | 高 | 定期更新；锁定版本 |
| Composer 包 | 中 | 审计依赖；使用 composer audit |

### 8.5 合规性考虑

| 要求 | 状态 | 说明 |
|------|------|------|
| GDPR | 待确认 | 如涉及欧洲用户，需实现数据导出/删除 |
| 用户同意 | 建议实施 | 注册时展示隐私政策并获取同意 |
| 数据删除 | 待确认 | 用户注销后数据处理策略 |
| Cookie 政策 | 建议实施 | Session Cookie 告知 |

---

## 9. 日志、监控与可观测性 (Observability)

### 9.1 关键业务事件记录

| 事件类型 | 日志级别 | Channel | 内容 |
|----------|----------|---------|------|
| 预约成功 | INFO | activity | user_id, activity_id, reservation_id |
| 预约失败（名额已满） | WARNING | activity | user_id, activity_id, reason |
| 预约失败（重复预约） | INFO | activity | user_id, activity_id |
| 取消预约 | INFO | activity | user_id, reservation_id |
| 活动发布 | INFO | admin | admin_id, activity_id |
| 用户登录成功 | INFO | auth | user_id, ip |
| 用户登录失败 | WARNING | auth | email, ip, reason |

### 9.2 成功/失败行为可观测性

**建议监控指标**：

| 指标名称 | 类型 | 说明 |
|----------|------|------|
| reservation_created_total | Counter | 预约创建总数 |
| reservation_failed_total | Counter | 预约失败总数（按原因分组） |
| reservation_cancelled_total | Counter | 预约取消总数 |
| activity_published_total | Counter | 活动发布总数 |
| http_request_duration_seconds | Histogram | HTTP 请求耗时 |

### 9.3 安全审计能力

- 所有审计日志写入独立 channel（`audit`）
- 审计日志不可由应用层修改/删除
- 支持按 user_id、activity_id、时间范围查询
- 建议接入 SIEM 系统（待确认）

### 9.4 不记录内容与原因说明

| 不记录内容 | 原因 |
|------------|------|
| 用户密码（明文或哈希） | 安全风险 |
| 完整手机号 | 隐私保护，仅记录后四位 |
| Session Token | 安全风险 |
| 请求 Body 中的敏感字段 | 隐私保护 |

---

## 10. 验收标准 (Acceptance Criteria)

### 10.1 用户注册与登录

```
Scenario: 用户成功注册
  Given 访问注册页面
  When 填写有效的姓名、邮箱、密码并提交
  Then 创建新用户账号
  And 自动登录并跳转至首页
  And 显示欢迎消息

Scenario: 用户邮箱已存在
  Given 访问注册页面
  When 填写已存在的邮箱并提交
  Then 显示错误消息「该邮箱已被注册」
  And 保留用户输入的其他信息

Scenario: 用户成功登录
  Given 访问登录页面
  When 输入正确的邮箱和密码并提交
  Then 登录成功并跳转至来源页面或首页

Scenario: 用户登录失败
  Given 访问登录页面
  When 输入错误的邮箱或密码并提交
  Then 显示错误消息「邮箱或密码错误」
  And 不泄露具体是邮箱还是密码错误
```

### 10.2 活动浏览

```
Scenario: 查看活动列表
  Given 访问活动列表页面
  Then 显示已发布的活动（按开始时间排序）
  And 每页显示 15 条
  And 显示分页控件

Scenario: 查看活动详情
  Given 点击某个活动
  Then 显示活动标题、描述、封面图
  And 显示活动时间、地点（如有）
  And 显示剩余名额
  And 显示预约按钮（如可预约）
```

### 10.3 活动预约

```
Scenario: 成功预约活动
  Given 用户已登录
  And 活动状态为已发布
  And 活动有剩余名额
  And 用户未预约过该活动
  When 点击预约按钮
  Then 创建预约记录
  And 活动剩余名额减少 1
  And 跳转至我的预约页面
  And 显示预约成功消息

Scenario: 活动名额已满
  Given 用户已登录
  And 活动剩余名额为 0
  When 尝试预约
  Then 显示错误消息「活动名额已满」
  And 不创建预约记录

Scenario: 重复预约
  Given 用户已登录
  And 用户已预约过该活动
  When 尝试再次预约
  Then 显示错误消息「您已预约过该活动」

Scenario: 未登录预约
  Given 用户未登录
  When 点击预约按钮
  Then 跳转至登录页面
  And 登录后返回活动详情页
```

### 10.4 取消预约

```
Scenario: 成功取消预约
  Given 用户已登录
  And 预约状态为已确认
  And 活动尚未开始
  When 点击取消预约并确认
  Then 预约状态变更为已取消
  And 活动剩余名额增加 1
  And 显示取消成功消息

Scenario: 活动已开始不可取消
  Given 用户已登录
  And 活动已开始
  When 尝试取消预约
  Then 显示错误消息「活动已开始，无法取消」
```

### 10.5 后台管理

```
Scenario: 创建活动
  Given 管理员已登录后台
  When 填写活动信息并上传封面图
  And 点击保存
  Then 创建活动记录（状态为草稿）
  And 封面图保存至 storage

Scenario: 发布活动
  Given 活动状态为草稿
  When 管理员点击发布
  Then 活动状态变更为已发布
  And 前台可见该活动

Scenario: 查看预约列表
  Given 管理员已登录后台
  When 访问预约管理
  Then 显示所有预约记录
  And 可按活动、用户、状态筛选
```

### 10.6 数据一致性验收

```
Scenario: 并发预约最后一个名额
  Given 活动剩余名额为 1
  When 两个用户同时提交预约请求
  Then 只有一个用户预约成功
  And 另一个用户收到名额已满错误
  And reserved_count = capacity

Scenario: 预约计数准确性
  Given 活动有 N 条有效预约记录
  Then activity.reserved_count = N
```

### 10.7 性能验收

| 场景 | 指标 |
|------|------|
| 活动列表加载 | P95 < 500ms |
| 预约操作响应 | P95 < 1s |
| 100 并发预约 | 无超卖，无数据库死锁 |

### 10.8 安全验收

| 场景 | 预期 |
|------|------|
| 未登录访问 /my/* | 重定向至登录页 |
| 普通用户访问 /admin/* | 403 Forbidden |
| SQL 注入尝试 | 无效，参数化查询保护 |
| XSS 尝试 | 无效，输出转义 |

---

## 11. 风险、限制与技术决策 (Risks & Trade-offs)

### 11.1 已识别风险

| 风险 | 影响 | 概率 | 缓解措施 |
|------|------|------|----------|
| 高并发预约导致数据库压力 | 响应变慢、超时 | 中 | 限流；队列削峰（后续版本） |
| 名额计数不一致 | 超卖或空余名额 | 低 | 事务保护；定时校验任务 |
| 图片存储空间不足 | 上传失败 | 低 | 监控存储使用；限制图片大小 |
| 前后台用户模型分离增加复杂度 | 维护成本增加 | 低 | 清晰的代码边界；文档说明 |

### 11.2 架构取舍及原因

| 决策 | 选择 | 备选方案 | 理由 |
|------|------|----------|------|
| 用户模型 | 分离 (User/Admin) | 统一模型 + 角色 | 客户明确要求；权限边界清晰 |
| 预约并发控制 | 悲观锁 | 乐观锁/队列 | 实现简单；并发量可控 |
| 前台技术栈 | Blade + Livewire | 纯 SPA (Vue/React) | 与 Filament 统一技术栈；SEO 友好 |
| 文件存储 | 本地 public disk | S3/OSS | 初期简单；已预留迁移路径 |
| 数据库 | MySQL (假设) | PostgreSQL | 待确认客户环境 |

### 11.3 延后处理的问题与演进方向

| 问题 | 延后原因 | 建议时间 |
|------|----------|----------|
| 活动分类/标签 | 非核心功能 | v1.5 |
| 消息通知 (邮件/短信) | 需对接第三方服务 | v1.5 |
| 支付功能 | 需对接支付网关 | v2.0 |
| 活动签到 | 需移动端支持 | v2.0 |
| 多租户支持 | 架构变更大 | v3.0 |
| Redis 缓存 | 当前性能可接受 | 按需引入 |
| 队列异步处理 | 当前流量可同步处理 | 按需引入 |

### 11.4 技术债务预警

| 项目 | 说明 | 建议处理时间 |
|------|------|--------------|
| reserved_count 字段 | 冗余字段，需定期校验 | 持续监控 |
| Enum 类型字段 | 扩展需 ALTER TABLE | 考虑迁移至 VARCHAR |
| 硬编码配置 | 部分配置写死在代码中 | 迁移至 config/env |

---

## 附录

### A. 术语表

| 术语 | 定义 |
|------|------|
| Activity | 活动，用户可预约参加的事件 |
| Reservation | 预约，用户对活动的报名记录 |
| Capacity | 容量，活动最大可容纳人数 |
| Guard | Laravel 认证守卫，用于区分不同用户类型的认证 |

### B. 参考文档

- Laravel 12 官方文档：https://laravel.com/docs/12.x
- Filament 4.0 官方文档：https://filamentphp.com/docs/4.x
- Laravel 认证文档：https://laravel.com/docs/12.x/authentication

### C. 变更日志

| 日期 | 版本 | 变更内容 | 作者 |
|------|------|----------|------|
| 2025-12-26 | 1.0 | 初始版本 | Architecture Team |

