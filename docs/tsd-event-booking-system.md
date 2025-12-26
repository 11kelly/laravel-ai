# 技术设计文档（TSD）- 活动预约系统

**文档版本**: v1.0  
**创建日期**: 2025-12-26  
**目标系统**: Event Booking System  
**技术栈**: Laravel 11.x + Filament 3.x + SQLite/MySQL  
**文档状态**: Draft - 待架构评审

---

## 1. 背景与目标 (Background & Goals)

### 1.1 业务与技术痛点
- **业务痛点**: 缺乏统一的活动发布与预约管理平台，活动信息分散，用户预约流程不透明
- **技术痛点**: 无系统化的活动状态管理、预约冲突处理、以及用户行为审计能力

### 1.2 本需求明确解决的问题
1. 建立活动生命周期管理能力（发布、开放预约、关闭、归档）
2. 实现用户自助预约与预约状态追踪
3. 提供管理员对活动与预约的集中管理能力
4. 支持活动图片素材管理与存储

### 1.3 架构目标
- **可扩展性**: 支持未来扩展至多租户、多语言、第三方日历集成
- **可维护性**: 清晰的领域边界，使用 Laravel Service Container 与 Repository 模式
- **可审计性**: 所有预约操作、状态变更可追溯
- **NFR**: 
  - 活动列表页 P95 响应时间 < 500ms
  - 预约提交成功率 > 99.5%（排除业务拒绝）
  - 系统可用性 > 99.9%

### 1.4 Out of Scope（本期不解决）
- 支付与财务结算能力
- 复杂的活动分类与标签体系（本期仅支持基础分类）
- 活动推荐算法与个性化推送
- 实时聊天或问答功能
- 第三方登录集成（微信、Google SSO）

---

## 2. 核心接口定义 (API / Service Contracts)

### 2.1 前台 API（用户端）

#### 2.1.1 活动列表查询
**接口**: `GET /api/events`  
**职责**: 返回可预约的活动列表（分页、筛选）  
**输入参数**:
- `page` (int, optional, default=1): 页码
- `per_page` (int, optional, default=20, max=100): 每页条数
- `status` (string, optional, enum=[upcoming, ongoing, past]): 活动状态
- `category_id` (int, optional): 分类筛选
- `search` (string, optional, max=100): 关键词搜索（标题、描述）

**输出结构**:
```json
{
  "data": [
    {
      "id": 1,
      "title": "活动标题",
      "description": "活动描述",
      "cover_image_url": "https://...",
      "start_time": "2025-12-28T10:00:00Z",
      "end_time": "2025-12-28T12:00:00Z",
      "location": "地点",
      "capacity": 100,
      "booked_count": 45,
      "is_bookable": true,
      "category": {"id": 1, "name": "分类名"}
    }
  ],
  "meta": {"current_page": 1, "total": 50, "per_page": 20}
}
```

**失败情况**:
- 400: 参数校验失败（per_page 超限）
- 500: 数据库查询异常

**幂等性**: 无副作用，幂等

---

#### 2.1.2 活动详情查询
**接口**: `GET /api/events/{id}`  
**职责**: 返回活动完整信息与预约规则  
**输入参数**:
- `id` (int, required): 活动 ID

**输出结构**:
```json
{
  "id": 1,
  "title": "活动标题",
  "description": "完整描述（富文本）",
  "images": [{"url": "https://...", "order": 1}],
  "start_time": "2025-12-28T10:00:00Z",
  "end_time": "2025-12-28T12:00:00Z",
  "location": "详细地点",
  "capacity": 100,
  "booked_count": 45,
  "booking_deadline": "2025-12-27T23:59:59Z",
  "is_bookable": true,
  "booking_rules": "预约须知文本",
  "organizer": {"name": "主办方", "contact": "联系方式"}
}
```

**失败情况**:
- 404: 活动不存在或已删除
- 500: 数据库异常

**幂等性**: 无副作用，幂等

---

#### 2.1.3 创建预约
**接口**: `POST /api/bookings`  
**职责**: 用户提交活动预约  
**认证要求**: 必须登录（Bearer Token / Session）  
**输入参数**:
```json
{
  "event_id": 1,
  "participants_count": 2,
  "notes": "备注信息（可选）"
}
```

**输出结构**（成功）:
```json
{
  "id": 123,
  "event_id": 1,
  "user_id": 456,
  "status": "confirmed",
  "participants_count": 2,
  "booked_at": "2025-12-26T08:00:00Z"
}
```

**失败情况**:
- 400: 参数校验失败（participants_count < 1 或超限）
- 401: 未登录
- 403: 已预约该活动（重复预约）
- 409: 活动已满员 / 已截止预约
- 422: 活动时间已过 / 活动已取消
- 500: 数据库写入失败

**幂等性**: 
- **非幂等**（业务语义）
- 需实现重复提交拦截（5秒内相同 user_id + event_id 拒绝）
- 返回 409 + 已有预约信息

**责任归属**:
- 调用方: 确保用户已登录、前端防重复点击
- 被调用方: 原子性检查剩余名额、记录预约、扣减库存

---

#### 2.1.4 取消预约
**接口**: `DELETE /api/bookings/{id}`  
**职责**: 用户取消自己的预约  
**认证要求**: 必须登录且为预约所有者  
**输入参数**:
- `id` (int, required): 预约 ID

**输出结构**:
```json
{
  "message": "预约已取消",
  "refunded_slots": 2
}
```

**失败情况**:
- 401: 未登录
- 403: 非预约所有者
- 404: 预约不存在
- 409: 活动已开始，无法取消（业务规则）
- 500: 数据库事务失败

**幂等性**: 
- **幂等**（重复调用返回 404 或成功）
- 需记录取消时间与操作人

---

#### 2.1.5 我的预约列表
**接口**: `GET /api/my-bookings`  
**职责**: 查询当前用户的预约记录  
**认证要求**: 必须登录  
**输入参数**:
- `status` (string, optional, enum=[confirmed, cancelled, completed]): 状态筛选
- `page`, `per_page`: 同活动列表

**输出结构**:
```json
{
  "data": [
    {
      "id": 123,
      "event": {
        "id": 1,
        "title": "活动标题",
        "start_time": "2025-12-28T10:00:00Z"
      },
      "status": "confirmed",
      "participants_count": 2,
      "booked_at": "2025-12-26T08:00:00Z"
    }
  ],
  "meta": {...}
}
```

**幂等性**: 无副作用，幂等

---

### 2.2 后台 API（管理员端 - Filament Admin Panel）

#### 2.2.1 活动管理 CRUD
**实现方式**: Filament Resource (`EventResource`)  
**职责**: 管理员创建、编辑、删除、发布活动  
**关键操作**:
- 创建活动: 必填字段校验（标题、时间、地点、容量）
- 上传活动图片: 存储至 `storage/app/public/events/{event_id}/`
- 发布/下架: 状态切换（draft → published → archived）
- 删除: 软删除（`deleted_at`），保留预约历史

**权限要求**: 
- 角色: `event_manager` 或 `admin`
- 操作日志: 记录所有 CUD 操作

---

#### 2.2.2 预约管理
**实现方式**: Filament Resource (`BookingResource`)  
**职责**: 查看、筛选、导出预约记录；管理员手动取消预约  
**关键功能**:
- 筛选: 按活动、用户、状态、时间范围
- 批量导出: CSV 格式，包含用户信息、预约时间
- 手动取消: 管理员可代用户取消，需填写原因

**权限要求**: 
- 角色: `event_manager` 或 `admin`
- 操作日志: 记录管理员取消预约的操作

---

#### 2.2.3 用户管理
**实现方式**: Filament Resource (`UserResource`)  
**职责**: 查看用户列表、封禁/解禁用户、查看用户预约历史  
**关键功能**:
- 封禁用户: 禁止登录与新建预约
- 查看用户预约: 关联展示该用户的所有预约

**权限要求**: 
- 角色: `admin`

---

## 3. 数据库 / 数据结构设计 (Schema Design)

### 3.1 表设计

#### 3.1.1 `users` 表（用户）
| 字段名 | 类型 | 约束 | 说明 |
|--------|------|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | 用户 ID |
| name | VARCHAR(255) | NOT NULL | 用户姓名 |
| email | VARCHAR(255) | NOT NULL, UNIQUE | 邮箱（登录账号） |
| email_verified_at | TIMESTAMP | NULLABLE | 邮箱验证时间 |
| password | VARCHAR(255) | NOT NULL | 密码哈希 |
| phone | VARCHAR(20) | NULLABLE | 手机号（待确认：是否必填） |
| avatar | VARCHAR(500) | NULLABLE | 头像 URL |
| is_banned | BOOLEAN | DEFAULT FALSE | 是否被封禁 |
| created_at | TIMESTAMP | NOT NULL | 创建时间 |
| updated_at | TIMESTAMP | NOT NULL | 更新时间 |

**索引**:
- PRIMARY KEY: `id`
- UNIQUE INDEX: `email`
- INDEX: `is_banned`

**并发假设**: 注册时邮箱唯一性由数据库 UNIQUE 约束保证

---

#### 3.1.2 `event_categories` 表（活动分类）
| 字段名 | 类型 | 约束 | 说明 |
|--------|------|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | 分类 ID |
| name | VARCHAR(100) | NOT NULL, UNIQUE | 分类名称 |
| slug | VARCHAR(100) | NOT NULL, UNIQUE | URL 友好标识 |
| description | TEXT | NULLABLE | 分类描述 |
| display_order | INT | DEFAULT 0 | 显示顺序 |
| is_active | BOOLEAN | DEFAULT TRUE | 是否启用 |
| created_at | TIMESTAMP | NOT NULL | |
| updated_at | TIMESTAMP | NOT NULL | |

**索引**:
- PRIMARY KEY: `id`
- UNIQUE INDEX: `name`, `slug`

---

#### 3.1.3 `events` 表（活动）
| 字段名 | 类型 | 约束 | 说明 |
|--------|------|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | 活动 ID |
| category_id | BIGINT UNSIGNED | NULLABLE, FK | 分类 ID |
| title | VARCHAR(255) | NOT NULL | 活动标题 |
| slug | VARCHAR(255) | NOT NULL, UNIQUE | URL 友好标识 |
| description | TEXT | NOT NULL | 活动详情（富文本） |
| cover_image | VARCHAR(500) | NULLABLE | 封面图 URL |
| start_time | TIMESTAMP | NOT NULL | 开始时间 |
| end_time | TIMESTAMP | NOT NULL | 结束时间 |
| booking_deadline | TIMESTAMP | NULLABLE | 预约截止时间 |
| location | VARCHAR(255) | NOT NULL | 地点 |
| capacity | INT UNSIGNED | NOT NULL | 总容量 |
| booked_count | INT UNSIGNED | DEFAULT 0 | 已预约数量 |
| status | ENUM | NOT NULL, DEFAULT 'draft' | 状态: draft, published, cancelled, completed, archived |
| booking_rules | TEXT | NULLABLE | 预约须知 |
| organizer_name | VARCHAR(255) | NULLABLE | 主办方名称 |
| organizer_contact | VARCHAR(255) | NULLABLE | 主办方联系方式 |
| created_by | BIGINT UNSIGNED | NULLABLE, FK | 创建人（管理员 ID） |
| created_at | TIMESTAMP | NOT NULL | |
| updated_at | TIMESTAMP | NOT NULL | |
| deleted_at | TIMESTAMP | NULLABLE | 软删除时间 |

**索引**:
- PRIMARY KEY: `id`
- UNIQUE INDEX: `slug`
- INDEX: `category_id`, `status`, `start_time`, `created_by`
- INDEX: `deleted_at` (软删除查询优化)

**约束**:
- FOREIGN KEY: `category_id` REFERENCES `event_categories(id)` ON DELETE SET NULL
- FOREIGN KEY: `created_by` REFERENCES `users(id)` ON DELETE SET NULL
- CHECK: `end_time > start_time`
- CHECK: `capacity >= booked_count`
- CHECK: `booking_deadline IS NULL OR booking_deadline <= start_time`

**并发假设**: 
- `booked_count` 更新使用乐观锁或数据库事务 + 行锁
- 预约时读取 `capacity - booked_count` 需在事务内完成

---

#### 3.1.4 `event_images` 表（活动图片）
| 字段名 | 类型 | 约束 | 说明 |
|--------|------|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | 图片 ID |
| event_id | BIGINT UNSIGNED | NOT NULL, FK | 活动 ID |
| image_url | VARCHAR(500) | NOT NULL | 图片 URL |
| display_order | INT | DEFAULT 0 | 显示顺序 |
| created_at | TIMESTAMP | NOT NULL | |

**索引**:
- PRIMARY KEY: `id`
- INDEX: `event_id, display_order`

**约束**:
- FOREIGN KEY: `event_id` REFERENCES `events(id)` ON DELETE CASCADE

**存储策略**: 
- 使用 Laravel Storage Facade
- 路径: `public/events/{event_id}/{filename}`
- 图片限制: 单张 < 5MB, 总数 < 10 张/活动

---

#### 3.1.5 `bookings` 表（预约记录）
| 字段名 | 类型 | 约束 | 说明 |
|--------|------|------|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | 预约 ID |
| event_id | BIGINT UNSIGNED | NOT NULL, FK | 活动 ID |
| user_id | BIGINT UNSIGNED | NOT NULL, FK | 用户 ID |
| participants_count | INT UNSIGNED | NOT NULL, DEFAULT 1 | 参与人数 |
| status | ENUM | NOT NULL, DEFAULT 'confirmed' | 状态: confirmed, cancelled, completed |
| notes | TEXT | NULLABLE | 用户备注 |
| cancelled_at | TIMESTAMP | NULLABLE | 取消时间 |
| cancelled_by | ENUM | NULLABLE | 取消发起方: user, admin |
| cancellation_reason | TEXT | NULLABLE | 取消原因（管理员取消时必填） |
| created_at | TIMESTAMP | NOT NULL | 预约时间 |
| updated_at | TIMESTAMP | NOT NULL | |

**索引**:
- PRIMARY KEY: `id`
- UNIQUE INDEX: `event_id, user_id, status` (WHERE `status` = 'confirmed') - 防止重复预约
- INDEX: `user_id, status`
- INDEX: `event_id, status`
- INDEX: `created_at` (按时间查询优化)

**约束**:
- FOREIGN KEY: `event_id` REFERENCES `events(id)` ON DELETE CASCADE
- FOREIGN KEY: `user_id` REFERENCES `users(id)` ON DELETE CASCADE
- CHECK: `participants_count > 0`
- CHECK: `status = 'cancelled' => cancelled_at IS NOT NULL`

**并发假设**: 
- 创建预约时需先行锁 `events` 表对应行（`SELECT ... FOR UPDATE`）
- 使用数据库事务保证 `booked_count` 增减与 `bookings` 插入/更新的原子性

---

### 3.2 数据增长与查询模式预期

| 表名 | 预期增长速率 | 主要查询模式 | 归档策略 |
|------|-------------|-------------|---------|
| users | 1000/月 | 按 email/id 单点查询 | 不归档 |
| events | 50/月 | 按状态、时间范围、分类联合查询 | 1年后归档至 `events_archive` |
| bookings | 5000/月 | 按用户、活动、状态、时间范围查询 | 1年后归档至 `bookings_archive` |
| event_images | 500/月 | 按 event_id 批量加载 | 随活动归档 |

**索引维护**: 
- 每季度分析慢查询日志
- 对 `events.start_time` 与 `bookings.created_at` 考虑分区表（待确认）

---

### 3.3 向后兼容性与数据迁移风险

**Schema 变更原则**:
- 新增字段必须 NULLABLE 或带 DEFAULT
- 禁止删除已有字段（标记为 deprecated）
- 枚举值扩展需兼容旧版本逻辑

**迁移测试**:
- 所有迁移在 Staging 环境验证 3 天
- 大表（> 10 万行）迁移需提前演练并准备回滚脚本

---

## 4. 前端与交互入口（如适用）

### 4.1 前台用户端（待确认：技术选型）

**选项 A**: Laravel Blade + Alpine.js + Livewire  
**选项 B**: Vue.js 3 + Inertia.js  
**选项 C**: 独立前端（Next.js / Nuxt.js）通过 API 调用  

**推荐**: 选项 A（快速开发，与 Laravel 紧耦合）

**功能入口**:

1. **首页 / 活动列表页** (`/events`)
   - 显示可预约活动卡片
   - 筛选器（分类、状态、搜索）
   - 分页加载

2. **活动详情页** (`/events/{slug}`)
   - 活动完整信息、图片轮播
   - 预约按钮（未登录则跳转登录页）
   - 实时显示剩余名额

3. **用户登录/注册页** (`/login`, `/register`)
   - 邮箱 + 密码登录
   - 邮箱验证（可选：待确认）
   - 记住我功能

4. **个人中心** (`/profile`)
   - 个人信息编辑（姓名、手机、头像）
   - 我的预约列表（Tab: 进行中、已完成、已取消）
   - 取消预约操作

**与后端契约边界**:
- 前端仅负责渲染与用户交互，不做复杂业务逻辑
- 所有数据校验前端做基础检查，后端必须重新校验
- 错误处理：后端返回标准化错误码与消息，前端统一 Toast 提示

**性能假设**:
- 活动列表页首屏渲染 < 1s（含 API 请求）
- 使用 Laravel Blade 片段缓存（`@cache` 指令）
- 图片使用 CDN 加速（待确认）

---

### 4.2 后台管理端（Filament Admin Panel）

**功能入口**:

1. **活动管理** (`/admin/events`)
   - 列表页：搜索、筛选、批量操作
   - 创建/编辑表单：TinyMCE 富文本编辑器、图片上传拖拽
   - 详情页：关联显示该活动的预约列表

2. **预约管理** (`/admin/bookings`)
   - 列表页：筛选（活动、用户、状态）、导出 CSV
   - 批量取消预约（需二次确认）

3. **用户管理** (`/admin/users`)
   - 列表页：搜索、封禁/解禁
   - 详情页：用户预约历史

4. **分类管理** (`/admin/event-categories`)
   - CRUD 操作
   - 拖拽排序（display_order）

**权限控制**:
- 使用 Filament 的 Policy 机制
- 角色定义：`admin` (全部权限), `event_manager` (活动与预约管理)

---

## 5. 核心业务流程 (Business Flow)

### 5.1 用户预约活动（正常流程）

```
1. 用户访问活动详情页 (/events/{slug})
   ├─> 前端调用 GET /api/events/{id}
   └─> 显示活动信息、剩余名额

2. 用户点击「立即预约」按钮
   ├─> 检查登录状态
   │   ├─> 未登录: 跳转 /login (带返回 URL)
   │   └─> 已登录: 继续
   ├─> 弹出确认弹窗（填写参与人数、备注）
   └─> 前端调用 POST /api/bookings
       {
         "event_id": 1,
         "participants_count": 2,
         "notes": "..."
       }

3. 后端处理预约请求 (EventBookingService)
   ├─> 开启数据库事务
   ├─> 校验参数（participants_count > 0, <= 剩余名额）
   ├─> 检查重复预约
   │   ├─> 查询 bookings 表 (WHERE user_id = X AND event_id = Y AND status = 'confirmed')
   │   └─> 若存在: 返回 409 "已预约该活动"
   ├─> 行锁活动记录 (SELECT * FROM events WHERE id = X FOR UPDATE)
   ├─> 再次验证剩余名额 (capacity - booked_count >= participants_count)
   │   └─> 不足: 返回 409 "活动已满员"
   ├─> 插入预约记录 (bookings 表)
   ├─> 更新活动已预约数 (booked_count += participants_count)
   ├─> 记录审计日志 (audit_logs 表)
   ├─> 提交事务
   └─> 返回 200 + 预约信息

4. 前端显示预约成功提示
   ├─> Toast: "预约成功！"
   └─> 跳转至 /profile (我的预约)

5. (可选) 发送预约确认邮件
   └─> 异步队列任务 (Laravel Queue)
```

---

### 5.2 用户取消预约（正常流程）

```
1. 用户在个人中心 (/profile) 点击「取消预约」
   ├─> 弹出二次确认弹窗
   └─> 前端调用 DELETE /api/bookings/{id}

2. 后端处理取消请求 (BookingCancellationService)
   ├─> 开启数据库事务
   ├─> 验证预约存在且属于当前用户
   │   └─> 否: 返回 403/404
   ├─> 检查活动是否已开始
   │   ├─> 若 event.start_time <= NOW(): 返回 409 "活动已开始，无法取消"
   │   └─> (待确认: 是否允许活动开始前 X 小时内取消)
   ├─> 更新预约状态为 'cancelled'
   ├─> 记录取消时间与发起方 (cancelled_at, cancelled_by = 'user')
   ├─> 行锁活动记录并减少 booked_count
   ├─> 记录审计日志
   ├─> 提交事务
   └─> 返回 200 "预约已取消"

3. 前端更新 UI，移除该预约项或标记为已取消
```

---

### 5.3 管理员发布活动（正常流程）

```
1. 管理员登录 Filament 后台 (/admin)
2. 进入活动管理页 (/admin/events) 点击「新建活动」
3. 填写表单
   ├─> 基本信息（标题、描述、分类）
   ├─> 时间设置（开始时间、结束时间、预约截止时间）
   ├─> 地点与容量
   ├─> 上传封面与多张活动图片
   └─> 填写预约须知

4. 点击「保存」
   ├─> Filament 调用 EventResource 的 create 方法
   ├─> 后端校验
   │   ├─> 时间逻辑检查 (end_time > start_time)
   │   ├─> 容量 > 0
   │   ├─> 图片格式与大小检查
   │   └─> 自动生成 slug (基于标题)
   ├─> 存储图片至 storage/app/public/events/{event_id}/
   ├─> 插入 events 表 (status = 'draft')
   ├─> 插入 event_images 表（关联图片）
   ├─> 记录审计日志 (操作人: 当前管理员)
   └─> 返回成功，跳转至活动列表

5. 管理员在列表页将活动状态改为 'published'
   └─> 活动对前台用户可见
```

---

### 5.4 异常 / 中断流程

#### 5.4.1 预约时活动突然满员（并发场景）
```
用户 A 和用户 B 同时预约最后 1 个名额:
├─> 两个请求同时到达后端
├─> A 先获得行锁 (SELECT ... FOR UPDATE)
│   ├─> 检查剩余名额 = 1, 允许预约
│   ├─> booked_count += 1
│   └─> 提交事务
├─> B 等待行锁释放
│   ├─> 获得锁后检查剩余名额 = 0
│   └─> 返回 409 "活动已满员"
└─> 前端向 B 显示: "抱歉，活动已满员，请关注其他活动"
```

#### 5.4.2 用户取消预约时网络中断
```
├─> 前端发送 DELETE 请求
├─> 后端已执行事务（预约已取消），但响应丢失
├─> 用户刷新页面，发现预约已消失（状态为 'cancelled'）
└─> 问题: 用户可能认为操作失败，重复点击
    └─> 解决: 后端幂等性设计，重复调用返回 404 或成功消息
```

#### 5.4.3 管理员删除活动时有未完成预约
```
├─> 管理员尝试删除活动 (软删除)
├─> 后端检查是否有 status = 'confirmed' 的预约
│   ├─> 有: 返回 409 "该活动存在有效预约，无法删除"
│   └─> 无: 执行软删除 (deleted_at = NOW())
└─> (待确认: 是否允许管理员强制删除并批量取消预约)
```

---

## 6. 边缘情况与一致性问题 (Edge Cases & Consistency)

### 6.1 并发控制

#### 6.1.1 问题：活动超售（booked_count > capacity）
**场景**: 高并发预约导致竞态条件  
**解决方案**:
- 使用数据库行锁 (`SELECT ... FOR UPDATE`)
- 事务内完成 "检查剩余名额 → 插入预约 → 更新 booked_count"
- 数据库 CHECK 约束：`capacity >= booked_count`

**监控**: 
- 记录 `capacity` 与 `booked_count` 不一致的日志
- 定期运行数据一致性校验脚本

---

#### 6.1.2 问题：重复预约（用户双击提交按钮）
**场景**: 用户在 5 秒内多次点击预约按钮  
**解决方案**:
1. 前端防抖：按钮点击后 disable 3 秒
2. 后端幂等性：
   - 在事务内查询 `bookings` 表（UNIQUE INDEX: `event_id, user_id, status='confirmed'`）
   - 若已存在，返回 409 + 已有预约信息
3. 使用 Redis 分布式锁（可选）：
   - Key: `booking:lock:{user_id}:{event_id}`
   - TTL: 10 秒

---

### 6.2 数据一致性

#### 6.2.1 问题：取消预约后 booked_count 未正确减少
**场景**: 事务中断或代码 Bug 导致 `booked_count` 与实际预约数不一致  
**检测方案**:
```sql
-- 定期运行一致性检查
SELECT 
  e.id,
  e.title,
  e.booked_count AS recorded_count,
  COALESCE(SUM(b.participants_count), 0) AS actual_count
FROM events e
LEFT JOIN bookings b ON e.id = b.event_id AND b.status = 'confirmed'
GROUP BY e.id
HAVING recorded_count != actual_count;
```

**修复方案**:
- 自动修复脚本（每日凌晨运行）
- 记录修复日志并告警

---

### 6.3 唯一性冲突

#### 6.3.1 问题：Slug 冲突（活动标题相同）
**场景**: 两个活动标题相同，生成的 slug 重复  
**解决方案**:
- Slug 生成规则：`title-{timestamp}` 或 `title-{id}`
- 数据库 UNIQUE 约束捕获冲突，重试生成

---

### 6.4 时间相关边缘情况

#### 6.4.1 问题：活动时间跨时区
**假设**: 本期不考虑多时区，所有时间使用服务器时区（UTC+8）  
**待确认**: 未来是否支持多地区活动

#### 6.4.2 问题：预约截止时间到达但仍有请求在处理
**解决方案**:
- 后端在事务内二次检查 `booking_deadline`
- 超时返回 422 "预约已截止"

---

## 7. 非功能性需求（Non-Functional Requirements）

### 7.1 性能（Performance）

| 指标 | 目标 | 测量方式 |
|------|------|---------|
| 活动列表页 API 响应时间（P95） | < 500ms | Laravel Telescope / APM |
| 活动详情页 API 响应时间（P95） | < 300ms | 同上 |
| 预约提交响应时间（P95） | < 1s | 同上 |
| 并发预约 TPS | > 100 (单活动) | 压力测试 (Locust / JMeter) |
| 数据库查询慢查询阈值 | > 200ms 告警 | MySQL Slow Query Log |

**优化策略**:
- 使用 Laravel Query Builder 的 Eager Loading（避免 N+1 查询）
- 活动列表页使用 Redis 缓存（TTL: 5 分钟）
- 图片使用 CDN（待确认）
- 数据库索引优化（见第 3 节）

---

### 7.2 可用性（Availability）

| 指标 | 目标 | 保障措施 |
|------|------|---------|
| 系统可用性 | > 99.9% (月停机 < 43 分钟) | 负载均衡 + 健康检查 |
| 数据库可用性 | > 99.95% | 主从复制 + 自动故障转移 |
| 部署停机时间 | < 5 分钟 | 蓝绿部署 / 滚动更新 |

**故障恢复**:
- 数据库每日全量备份 + 每小时增量备份
- 应用日志保留 30 天
- RTO（恢复时间目标）: < 1 小时
- RPO（恢复点目标）: < 1 小时

---

### 7.3 可扩展性（Scalability）

**水平扩展能力**:
- 应用层无状态设计（Session 存储于 Redis）
- 数据库读写分离（主库写，从库读）
- 文件存储使用对象存储（待确认：阿里云 OSS / AWS S3）

**容量规划**（1 年内）:
- 用户数: 1 万
- 活动数: 600
- 预约记录: 6 万
- 预计数据库大小: < 1 GB

---

### 7.4 可维护性（Maintainability）

**代码组织**:
- 使用 Service Layer（`App\Services\EventBookingService`）
- Repository 模式（`App\Repositories\EventRepository`）
- 遵循 SOLID 原则
- PHPStan Level 6 静态分析通过

**文档要求**:
- API 文档使用 Swagger / OpenAPI 生成
- 核心业务逻辑有详细注释
- 数据库 Schema 变更有迁移文档

---

### 7.5 可测试性（Testability）

**测试覆盖率目标**:
- 单元测试覆盖率 > 80%（核心业务逻辑）
- 集成测试覆盖所有 API 端点
- 功能测试覆盖关键用户流程（预约、取消）

**测试策略**:
- PHPUnit + Laravel Dusk（端到端测试）
- 使用 Factory 和 Seeder 生成测试数据
- CI/CD 流水线自动运行测试

---

## 8. 信息安全、合规与审计 (Security, Compliance & Audit)

### 8.1 认证与授权

**认证机制**:
- 前台用户: Laravel Sanctum (SPA Token Authentication)
- 后台管理: Laravel Breeze / Jetstream (Session-based)
- 密码策略: 最小 8 位，包含字母 + 数字（待确认：是否强制特殊字符）

**授权边界**:
- 用户只能查看/取消自己的预约
- 管理员角色分离：`admin` (全部权限), `event_manager` (仅活动管理)
- 使用 Laravel Policy 实现细粒度权限控制

---

### 8.2 敏感数据保护

**数据分类**:
- **高敏感**: 用户密码（bcrypt 哈希）
- **中敏感**: 邮箱、手机号（明文存储，但 API 响应需脱敏）
- **低敏感**: 用户姓名、头像、预约记录

**加密要求**:
- 数据库连接使用 TLS（生产环境）
- API 传输强制 HTTPS
- 图片上传需校验文件类型（防止上传可执行文件）

**脱敏规则**:
- 邮箱: `u***@example.com`
- 手机: `138****5678`

---

### 8.3 输入校验与注入防护

**SQL 注入**: 
- 使用 Eloquent ORM 和参数化查询
- 禁止拼接原始 SQL

**XSS 防护**: 
- Blade 模板自动转义
- 富文本内容使用 HTML Purifier 清理

**CSRF 防护**: 
- Laravel 默认启用 CSRF 保护
- API 端点使用 Sanctum Token

**文件上传安全**:
- 限制类型：仅允许 jpg, png, gif
- 限制大小：单张 < 5 MB
- 存储路径不可直接执行（`storage/app/public/`）

---

### 8.4 审计日志

**记录范围**:
- 用户登录/登出
- 预约创建/取消（含操作人、时间、IP）
- 管理员操作（创建/修改/删除活动、批量取消预约）
- 权限变更

**日志结构**:
```json
{
  "timestamp": "2025-12-26T08:00:00Z",
  "event": "booking.created",
  "actor": {"id": 123, "type": "user"},
  "target": {"type": "booking", "id": 456},
  "context": {
    "event_id": 1,
    "participants_count": 2,
    "ip": "192.168.1.100"
  }
}
```

**存储**: 
- 使用 `audit_logs` 表或 Elasticsearch（可选）
- 保留 1 年，之后归档

---

### 8.5 第三方依赖信任假设

**当前依赖**:
- Laravel Framework (官方支持，安全更新及时)
- Filament (社区广泛使用，定期审查更新)
- 图片处理库: Intervention Image (需定期检查 CVE)

**依赖管理**:
- 每月运行 `composer audit` 检查漏洞
- 锁定次要版本号（`^` 版本约束）

---

### 8.6 合规性考虑（待确认）

**数据保护法规**:
- GDPR（如面向欧盟用户）: 需实现「被遗忘权」（用户账号删除 → 匿名化预约记录）
- 个人信息保护法（中国）: 需获得用户明确同意收集手机号

**隐私政策**:
- 需在注册页提供隐私政策链接
- 用户数据导出功能（待确认）

---

## 9. 日志、监控与可观测性 (Observability)

### 9.1 关键业务事件记录

**事件类型**:
- `booking.created`: 预约创建成功
- `booking.cancelled`: 预约被取消
- `event.published`: 活动发布
- `event.capacity_exceeded`: 尝试预约时容量不足（用于分析）

**日志级别**:
- INFO: 正常业务操作
- WARNING: 容量不足、重复预约尝试
- ERROR: 数据库事务失败、外部服务调用失败
- CRITICAL: 数据一致性错误

---

### 9.2 监控指标

**应用指标**（使用 Laravel Telescope / Prometheus）:
- API 请求成功率
- 平均响应时间
- 4xx / 5xx 错误率
- 队列任务堆积数量

**业务指标**（自定义 Dashboard）:
- 每日新增预约数
- 活动预约率（已预约 / 总容量）
- 用户取消预约率
- 活动满员时间分布

**基础设施指标**:
- CPU / 内存 / 磁盘使用率
- 数据库连接池状态
- Redis 命中率

---

### 9.3 告警规则

| 告警项 | 阈值 | 级别 | 处理方式 |
|--------|------|------|---------|
| API 5xx 错误率 > 1% | 5 分钟内 | P1 | 立即电话通知 |
| 数据库连接数 > 80% | 持续 10 分钟 | P2 | 邮件 + 钉钉群 |
| 磁盘使用率 > 85% | 持续 30 分钟 | P3 | 邮件通知 |
| 队列任务堆积 > 1000 | 持续 15 分钟 | P2 | 邮件通知 |

---

### 9.4 不记录内容与原因

**不记录**:
- 用户密码明文（安全原因）
- 完整的邮箱和手机号（隐私原因，仅记录哈希或脱敏值）
- 用户备注字段的完整内容（除非涉及违规审计）

---

## 10. 验收标准 (Acceptance Criteria)

### 10.1 功能正确性

#### 前台用户端
- [ ] **Given** 用户未登录，**When** 访问活动列表页，**Then** 应显示所有 published 状态的活动
- [ ] **Given** 用户已登录，**When** 预约一个有剩余名额的活动，**Then** 预约成功并显示确认信息
- [ ] **Given** 用户预约时活动已满员，**When** 提交预约，**Then** 返回错误提示「活动已满员」
- [ ] **Given** 用户已有该活动的 confirmed 预约，**When** 再次尝试预约，**Then** 返回错误提示「已预约该活动」
- [ ] **Given** 用户有一个 confirmed 预约，**When** 取消预约且活动未开始，**Then** 取消成功并释放名额
- [ ] **Given** 活动已开始，**When** 用户尝试取消预约，**Then** 返回错误提示「活动已开始，无法取消」

#### 后台管理端
- [ ] **Given** 管理员登录，**When** 创建活动并上传图片，**Then** 活动创建成功且图片正确显示
- [ ] **Given** 活动有 confirmed 预约，**When** 管理员尝试删除活动，**Then** 返回错误提示「存在有效预约，无法删除」
- [ ] **Given** 管理员在预约列表页，**When** 导出 CSV，**Then** 导出文件包含所有筛选条件下的预约记录

---

### 10.2 数据一致性

- [ ] **并发测试**: 100 个用户同时预约最后 10 个名额，仅 10 个成功，`booked_count` 准确无超售
- [ ] **一致性校验**: 运行一致性检查脚本，所有活动的 `booked_count` 与实际 confirmed 预约数一致
- [ ] **取消预约后**: `booked_count` 正确减少，对应 `booking` 记录状态为 `cancelled`

---

### 10.3 性能

- [ ] **负载测试**: 活动列表页在 50 并发下，P95 响应时间 < 500ms
- [ ] **预约提交**: 单活动 100 TPS 并发预约，成功率 > 99%（排除业务拒绝）

---

### 10.4 安全

- [ ] **未授权访问**: 未登录用户访问 `/api/bookings` 返回 401
- [ ] **越权操作**: 用户 A 尝试取消用户 B 的预约，返回 403
- [ ] **SQL 注入**: 对所有输入点进行 SQL 注入测试，无漏洞
- [ ] **文件上传**: 尝试上传 `.php` 文件，被拦截并返回错误

---

### 10.5 可测试性

- [ ] 所有 API 端点有对应的集成测试（PHPUnit Feature Test）
- [ ] 核心业务逻辑（EventBookingService）有单元测试覆盖
- [ ] CI/CD 流水线自动运行测试，失败则阻止部署

---

## 11. 风险、限制与技术决策 (Risks & Trade-offs)

### 11.1 已识别风险

| 风险项 | 可能性 | 影响 | 缓解措施 |
|--------|--------|------|---------|
| 高并发预约导致超售 | 中 | 高 | 使用行锁 + 数据库约束 + 监控告警 |
| 图片存储成本增长 | 高 | 中 | 设置单活动图片数量上限（10 张），压缩图片 |
| 用户恶意刷预约 | 低 | 中 | 实现 IP 频率限制（Laravel Throttle） |
| 数据库主库故障 | 低 | 高 | 主从复制 + 自动故障转移 |
| 第三方依赖漏洞 | 中 | 中 | 每月运行 `composer audit`，订阅安全公告 |

---

### 11.2 架构取舍及原因

#### 决策 1: 使用 Filament 而非自建后台
**原因**:
- 快速开发，减少重复代码
- 社区活跃，有丰富的扩展
- 与 Laravel 深度集成

**Trade-off**:
- 定制化能力受限（复杂 UI 需自行扩展）
- 学习曲线（团队需熟悉 Filament）

---

#### 决策 2: 单库架构（非读写分离）
**原因**:
- 初期数据量小（< 10 万行）
- 简化部署与运维

**Trade-off**:
- 未来需扩展时需改造
- 演进路径：1 年后根据负载评估是否引入读写分离

---

#### 决策 3: 使用乐观锁（booked_count）而非悲观锁
**修正**: 经讨论，改为**悲观锁**（`SELECT ... FOR UPDATE`）以保证强一致性

**原因**:
- 预约场景对一致性要求高，不容忍超售
- 冲突率可控（单活动预约频率低）

---

### 11.3 延后处理的问题与演进方向

#### 短期（3 个月内）
- [ ] 实现邮件通知（预约成功、活动提醒）
- [ ] 活动评论与评分功能
- [ ] 手机号验证码登录

#### 中期（6-12 个月）
- [ ] 活动推荐算法（基于用户历史预约）
- [ ] 多语言支持（i18n）
- [ ] 日历视图与 iCal 导出

#### 长期（1 年以上）
- [ ] 多租户架构（支持多个组织独立管理）
- [ ] 第三方登录（微信、支付宝）
- [ ] 支付与票务系统集成

---

### 11.4 待确认问题（需产品 / 业务方决策）

1. **邮箱验证**: 注册时是否强制邮箱验证？
2. **取消时间限制**: 活动开始前多久禁止取消预约？（建议：2 小时）
3. **管理员强制删除**: 是否允许管理员删除有预约的活动（并批量取消）？
4. **图片 CDN**: 是否使用 CDN 加速（成本考虑）？
5. **多时区支持**: 是否需要多时区（本期假设仅 UTC+8）？
6. **GDPR 合规**: 是否面向欧盟用户（需实现数据导出与删除）？
7. **预约人数限制**: 单次预约是否限制参与人数上限（建议：1-10 人）？

---

## 附录

### A. 术语表

| 术语 | 定义 |
|------|------|
| **活动（Event）** | 具有时间、地点、容量的可预约事项 |
| **预约（Booking）** | 用户对活动的参与申请记录 |
| **容量（Capacity）** | 活动可接纳的最大参与人数 |
| **已预约数（Booked Count）** | 当前 confirmed 状态的预约占用的总名额 |
| **软删除（Soft Delete）** | 逻辑删除，数据标记 `deleted_at` 但不物理删除 |

---

### B. 参考资料

- [Laravel 11.x Documentation](https://laravel.com/docs/11.x)
- [Filament 3.x Documentation](https://filamentphp.com/docs)
- [Optimistic vs Pessimistic Locking](https://en.wikipedia.org/wiki/Optimistic_concurrency_control)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

---

### C. 文档变更记录

| 版本 | 日期 | 作者 | 变更内容 |
|------|------|------|---------|
| v1.0 | 2025-12-26 | 架构师 | 初始版本，待评审 |

---

**文档状态**: 🟡 Draft - 待架构委员会与产品团队评审  
**下一步**: 召开架构评审会议，确认待定事项，评估风险与成本

