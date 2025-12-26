# 微型在线活动预约系统 - 技术设计文档 (TSD)

## 1. 背景与目标 (Background & Goals)

### 业务与技术痛点
- **业务痛点**：缺乏统一的活动管理平台，导致活动组织分散、报名流程不统一、数据统计困难
- **技术痛点**：现有系统缺乏前后端分离的架构，无法有效支撑活动管理的可扩展性需求

### 本需求明确解决的问题
- 提供完整的活动生命周期管理：从活动创建、发布到用户预约的闭环流程
- 实现前后端分离架构：Filament后台管理 + Laravel Blade前台展示
- 支持基础的数据统计和用户行为分析

### 架构目标
- **可扩展性**：支持活动类型扩展，支持并发预约处理
- **可维护性**：采用Laravel标准架构模式，代码结构清晰
- **可审计性**：完整的用户操作日志和业务事件记录
- **性能目标**：页面响应时间 < 2秒，预约提交响应 < 1秒
- **可用性目标**：99.5%服务可用性，单实例支持100并发用户

### 非功能性需求目标
- **安全性**：敏感数据加密存储，防止SQL注入和XSS攻击
- **合规性**：支持GDPR数据删除要求（待确认）
- **可观测性**：关键业务指标监控和错误日志记录

### Out of Scope
- 支付集成（本期使用线下支付）
- 第三方日历系统集成
- 高级用户权限管理（仅区分管理员和普通用户）
- 多租户支持
- 移动端APP开发

## 2. 核心接口定义 (API / Service Contracts)

### 2.1 活动管理服务接口 (ActivityManagementService)
**职责**：提供活动CRUD操作和管理功能

**接口定义**：
```php
interface ActivityManagementServiceInterface {
    // 创建活动
    Activity createActivity(CreateActivityRequest $request): Activity

    // 更新活动
    Activity updateActivity(int $activityId, UpdateActivityRequest $request): Activity

    // 删除活动
    void deleteActivity(int $activityId): void

    // 获取活动列表（分页）
    PaginatedResponse<Activity> getActivities(ActivityFilter $filter): PaginatedResponse

    // 获取单个活动详情
    Activity getActivity(int $activityId): Activity
}
```

**输入参数规范**：
- `CreateActivityRequest`: title(string, required, 1-255 chars), description(text, required), price(decimal, optional, >=0), capacity(int, required, >0), start_date(datetime, required), end_date(datetime, required, > start_date)
- `UpdateActivityRequest`: 同创建接口，但所有字段可选
- `ActivityFilter`: status(enum: draft/published/cancelled), date_range(datetime range), search(string)

**输出结构**：
- **成功响应**：Activity实体包含id, title, description, price, capacity, available_slots, status, created_at, updated_at
- **可预期失败**：ValidationException(字段校验失败), DuplicateTitleException(标题重复), CapacityExceededException(超出系统容量限制)
- **不可恢复错误**：DatabaseException(数据库连接失败), StorageException(文件存储失败)

**调用方**：Filament后台管理控制器
**被调用方**：Eloquent模型层 + 缓存层
**幂等性**：更新和删除操作幂等，创建操作非幂等
**副作用**：创建/更新操作触发缓存失效

### 2.2 用户预约服务接口 (BookingService)
**职责**：处理用户活动预约相关操作

**接口定义**：
```php
interface BookingServiceInterface {
    // 创建预约
    Booking createBooking(CreateBookingRequest $request): Booking

    // 取消预约
    void cancelBooking(int $bookingId, int $userId): void

    // 获取用户预约列表
    Collection<Booking> getUserBookings(int $userId, BookingFilter $filter): Collection

    // 获取活动预约列表（管理员）
    PaginatedResponse<Booking> getActivityBookings(int $activityId, BookingFilter $filter): PaginatedResponse

    // 验证预约资格
    BookingEligibility checkBookingEligibility(int $activityId, int $userId): BookingEligibility
}
```

**输入参数规范**：
- `CreateBookingRequest`: activity_id(int, required), user_id(int, required), contact_info(json, required), special_requirements(text, optional)
- `BookingFilter`: status(enum: pending/confirmed/cancelled), date_range(datetime range)

**输出结构**：
- **成功响应**：Booking实体包含id, activity_id, user_id, status, booking_reference, contact_info, created_at
- **可预期失败**：ActivityNotFoundException, ActivityFullException, DuplicateBookingException, ActivityExpiredException
- **不可恢复错误**：DatabaseException, NotificationException(邮件发送失败)

**调用方**：前台控制器 + Filament管理面板
**被调用方**：活动服务 + 通知服务 + 缓存层
**幂等性**：创建预约操作非幂等（需业务去重），取消预约幂等
**副作用**：创建预约触发名额扣减和通知发送

### 2.3 统计服务接口 (StatisticsService)
**职责**：提供活动和预约数据的统计分析

**接口定义**：
```php
interface StatisticsServiceInterface {
    // 获取活动统计概览
    ActivityStats getActivityStats(int $activityId): ActivityStats

    // 获取全局统计数据
    GlobalStats getGlobalStats(DateRange $range): GlobalStats

    // 获取热门活动排行
    Collection<ActivityRanking> getPopularActivities(DateRange $range, int $limit): Collection
}
```

**输入参数规范**：
- `DateRange`: start_date(datetime), end_date(datetime)

**输出结构**：
- **成功响应**：统计数据结构包含bookings_count, revenue, occupancy_rate等指标
- **可预期失败**：DateRangeInvalidException(日期范围无效)
- **不可恢复错误**：DatabaseException

**调用方**：Filament仪表板组件
**被调用方**：只读数据库连接
**幂等性**：所有操作均幂等
**副作用**：无

## 3. 数据库 / 数据结构设计 (Schema Design)

### 核心数据表结构

#### activities 表
```sql
CREATE TABLE activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) DEFAULT 0,
    capacity INT UNSIGNED NOT NULL,
    available_slots INT UNSIGNED NOT NULL DEFAULT capacity,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('draft', 'published', 'cancelled', 'completed') DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_start_date (start_date),
    INDEX idx_slug (slug),
    CHECK (end_date > start_date),
    CHECK (available_slots <= capacity)
);
```

#### bookings 表
```sql
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    activity_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    contact_info JSON NOT NULL,
    special_requirements TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'attended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_activity_user (activity_id, user_id),
    INDEX idx_status (status),
    INDEX idx_booking_reference (booking_reference),
    INDEX idx_created_at (created_at)
);
```

#### activity_logs 表（审计日志）
```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id BIGINT UNSIGNED,
    booking_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
    action VARCHAR(50) NOT NULL,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE SET NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity (activity_id),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);
```

### 并发与一致性假设
- **乐观锁**：使用version字段或timestamp进行并发控制
- **行级锁**：预约操作使用SELECT FOR UPDATE确保名额扣减原子性
- **隔离级别**：使用READ COMMITTED隔离级别平衡性能和一致性
- **分布式锁**：单实例部署，无需分布式锁机制

### 数据增长与查询模式预期
- **activities表**：预计每月100个活动，年增长约1200条记录，主要查询模式为按状态和日期范围筛选
- **bookings表**：预计每月1000个预约，年增长约12000条记录，主要查询模式为用户预约历史和活动预约列表
- **索引策略**：复合索引覆盖主要查询模式，避免全表扫描

### 向后兼容性与数据迁移风险
- **版本控制**：使用Laravel迁移系统管理schema变更
- **数据迁移**：涉及数据重构的操作需提前备份，预留回滚脚本
- **API兼容性**：接口变更遵循语义化版本控制，重大变更需版本升级

## 4. 前端与交互入口（如适用）

### 功能入口定义
- **前台活动列表页**：`/activities` - 展示所有发布活动的分页列表
- **前台活动详情页**：`/activities/{slug}` - 展示活动详细信息和预约表单
- **前台预约确认页**：`/booking/confirm/{reference}` - 预约成功确认页面
- **后台管理面板**：`/admin` - Filament管理界面

### 与后端契约的边界
- **数据获取**：前端通过Laravel路由调用控制器方法，控制器返回Blade视图或JSON响应
- **表单提交**：使用Laravel Form Request进行数据验证，成功后重定向或返回JSON响应
- **状态管理**：使用Laravel Session存储临时状态，页面刷新时保持状态

### 性能与渲染假设
- **首屏渲染**：活动列表页使用数据库查询 + Blade模板渲染，目标响应时间 < 500ms
- **缓存策略**：活动详情页使用Redis缓存（如果配置），缓存时间30分钟
- **SEO考虑**：活动详情页包含meta标签和结构化数据，支持搜索引擎索引
- **移动端适配**：使用Tailwind CSS响应式设计，确保移动端可用性

## 5. 核心业务流程 (Business Flow)

### 正常活动预约流程
```
1. 用户访问前台活动列表页
2. 用户点击活动进入详情页
3. 系统验证活动状态和可用名额
4. 用户填写预约表单并提交
5. 系统执行预约创建：
   5.1 验证用户身份和预约资格
   5.2 检查活动名额可用性
   5.3 创建预约记录（事务）
   5.4 扣减活动可用名额
   5.5 发送确认邮件
   5.6 返回预约确认页面
6. 用户收到预约确认邮件
```

### 异常 / 中断流程
- **活动已满**：显示"名额已满"提示，引导用户选择其他活动
- **预约冲突**：检测到用户已预约同一活动，返回错误提示
- **系统错误**：记录错误日志，返回通用错误页面，提示用户稍后重试
- **邮件发送失败**：预约仍成功创建，仅记录警告日志，不影响用户体验

### 关键状态变更点
- **活动发布**：status从draft变为published，触发缓存更新
- **预约创建**：available_slots递减，booking status设为confirmed
- **预约取消**：available_slots递增，booking status变为cancelled
- **活动完成**：status变为completed，锁定进一步预约

## 6. 边缘情况与一致性问题 (Edge Cases & Consistency)

### 并发预约场景
- **竞态条件**：多个用户同时预约最后一个名额，使用数据库行锁确保只有一个用户成功
- **重复提交**：前端使用防重复提交机制，后端通过唯一约束防止重复预约
- **网络超时**：用户提交后页面未响应，通过booking_reference查询预约状态

### 数据一致性保证
- **事务边界**：预约创建使用数据库事务，确保名额扣减和预约记录创建的原子性
- **约束完整性**：外键约束保证关联数据一致性，级联删除处理数据清理
- **业务规则**：应用层验证补充数据库约束，保证业务逻辑一致性

### 异常中断恢复
- **部分失败**：预约创建失败后自动回滚事务，保证数据一致性
- **系统重启**：使用Laravel队列确保邮件发送的可靠性，重启后继续处理积压任务
- **数据修复**：提供管理命令用于数据一致性检查和修复

## 7. 非功能性需求（Non-Functional Requirements）

### 性能 (Performance)
- **响应时间**：API响应时间 < 200ms，页面渲染时间 < 1秒
- **并发处理**：单实例支持100并发用户，峰值QPS 50
- **数据库性能**：查询响应时间 < 100ms，索引覆盖率 > 95%
- **缓存策略**：热点活动数据缓存30分钟，减少数据库压力

### 可用性 (Availability)
- **服务等级**：99.5%可用性，允许计划内维护窗口
- **故障恢复**：自动重试机制，降级服务策略
- **监控告警**：关键指标监控，异常时自动告警

### 可扩展性 (Scalability)
- **水平扩展**：支持多实例部署，通过负载均衡分发请求
- **数据扩展**：数据库分表策略支持数据量增长到百万级别
- **功能扩展**：插件化架构支持新功能模块添加

### 可维护性 (Maintainability)
- **代码质量**：遵循PSR标准，单元测试覆盖率 > 80%
- **文档完整性**：API文档和代码注释覆盖所有公共接口
- **部署自动化**：支持一键部署和回滚操作

### 可测试性 (Testability)
- **单元测试**：核心业务逻辑100%单元测试覆盖
- **集成测试**：关键用户流程的端到端测试
- **性能测试**：并发场景和压力测试用例

## 8. 信息安全、合规与审计 (Security, Compliance & Audit)

### 认证 / 授权边界
- **用户认证**：使用Laravel Sanctum或Breeze进行身份认证
- **权限控制**：基于角色的访问控制，管理员可管理所有活动，普通用户仅可管理自己的预约
- **会话管理**：安全的session管理，自动过期和清理机制

### 敏感数据处理原则
- **数据加密**：用户联系信息使用AES加密存储
- **传输安全**：所有API通信使用HTTPS协议
- **数据脱敏**：日志中敏感信息进行脱敏处理

### 日志与审计要求
- **操作日志**：所有管理操作记录到activity_logs表
- **安全事件**：登录失败、权限拒绝等安全事件记录
- **审计追踪**：支持按用户、时间、操作类型查询审计日志

### 第三方依赖信任假设
- **Laravel框架**：信任Laravel官方安全更新和补丁
- **Filament**：信任Filament官方维护的安全性
- **数据库驱动**：使用PDO进行SQL注入防护

### 合规性考虑（待确认）
- **数据保留期**：用户数据保留期遵循GDPR要求（待确认具体期限）
- **数据删除**：支持用户数据完全删除，包括关联的预约记录
- **隐私政策**：前台展示隐私政策链接和数据使用说明

## 9. 日志、监控与可观测性 (Observability)

### 关键业务事件记录
- **活动操作**：创建、更新、删除、发布活动的事件
- **预约操作**：创建预约、取消预约、确认出席的事件
- **系统事件**：用户登录、权限检查失败的事件

### 成功 / 失败行为可观测性
- **成功指标**：预约成功率 > 99%，活动发布成功率 100%
- **失败监控**：预约失败原因分类统计，系统错误率 < 1%
- **性能监控**：页面响应时间分布，数据库查询性能

### 安全审计或风控分析能力
- **异常检测**：异常登录尝试、批量操作检测
- **行为分析**：用户预约模式分析，识别潜在风险行为
- **合规报告**：生成GDPR合规性报告和数据处理统计

### 不记录内容与原因说明
- **不记录**：用户密码、完整信用卡信息（本期无支付功能）
- **原因**：避免安全风险，减少数据泄露面
- **替代方案**：使用哈希值或token进行关联

## 10. 验收标准 (Acceptance Criteria)

### 功能正确性
- **活动管理**：管理员可创建、编辑、删除、发布活动
- **预约流程**：用户可浏览活动、提交预约、接收确认邮件
- **状态管理**：活动和预约状态正确变更，名额管理准确
- **数据验证**：所有输入数据正确验证，错误提示清晰

### 数据一致性
- **并发控制**：高并发下名额扣减正确，无超卖现象
- **事务完整性**：预约失败时数据回滚，保持一致性
- **关联完整性**：删除活动时正确清理关联预约记录

### 性能 / 安全底线
- **性能基准**：页面加载时间 < 2秒，预约提交 < 1秒
- **安全要求**：无SQL注入、XSS漏洞，敏感数据加密存储
- **可用性标准**：服务可用性 > 99.5%，错误恢复时间 < 5分钟

### 可测试性声明
- **单元测试**：核心服务层测试覆盖率 > 80%
- **集成测试**：主要用户流程可自动化测试
- **端到端测试**：完整预约流程的手动测试通过

## 11. 风险、限制与技术决策 (Risks & Trade-offs)

### 已识别风险
- **高并发风险**：活动发布时可能出现瞬时高并发，需监控数据库连接池
- **数据增长风险**：活动和预约数据快速增长，需定期归档历史数据
- **第三方依赖风险**：Filament版本升级可能引入不兼容变更

### 架构取舍及原因
- **选择SQLite**：简化部署和维护，适合中小规模应用；权衡点是并发处理能力有限
- **单体架构**：降低复杂性，适合当前业务规模；未来可演进为微服务架构
- **无消息队列**：简化架构，预约邮件使用同步发送；权衡点是用户体验略有影响

### 延后处理的问题与演进方向
- **支付集成**：本期不支持在线支付，可在后续版本集成Stripe或支付宝
- **多租户支持**：当前单租户设计，后续可扩展为多租户架构
- **高级统计**：当前仅基础统计，后续可集成数据仓库进行深度分析
- **API接口**：当前仅支持Web界面，后续可提供REST API支持第三方集成

---

**文档版本**：1.0
**最后更新**：2025-12-24
**审核状态**：待架构委员会评审
**技术栈**：Laravel 12.x + Filament 4.0 + SQLite + Tailwind CSS
