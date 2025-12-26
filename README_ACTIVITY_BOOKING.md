# 微型在线活动预约系统 - 使用指南

## 概述

本系统基于 TSD（技术设计文档）实现了完整的活动预约功能，包括活动管理、用户预约和统计分析三大核心模块。

## 核心服务

### 1. ActivityManagementService - 活动管理服务

负责活动CRUD操作和管理功能。

```php
use App\Contracts\ActivityManagementServiceInterface;
use App\DTOs\CreateActivityRequest;
use App\DTOs\UpdateActivityRequest;
use App\DTOs\ActivityFilter;
use Carbon\Carbon;

$activityService = app(ActivityManagementServiceInterface::class);

// 创建活动
$request = new CreateActivityRequest(
    title: 'Laravel 开发工作坊',
    description: '学习 Laravel 框架的最佳实践',
    price: 99.00,
    capacity: 50,
    startDate: Carbon::now()->addDays(7),
    endDate: Carbon::now()->addDays(8)
);
$activity = $activityService->createActivity($request);

// 更新活动
$updateRequest = new UpdateActivityRequest(
    title: 'Laravel 高级开发工作坊',
    price: 129.00
);
$updatedActivity = $activityService->updateActivity($activity->id, $updateRequest);

// 获取活动列表
$filter = new ActivityFilter(
    status: 'published',
    search: 'Laravel'
);
$activities = $activityService->getActivities($filter);

// 获取单个活动
$activity = $activityService->getActivity(1);
```

### 2. BookingService - 预约服务

处理用户活动预约相关操作。

```php
use App\Contracts\BookingServiceInterface;
use App\DTOs\CreateBookingRequest;
use App\DTOs\BookingFilter;

$bookingService = app(BookingServiceInterface::class);

// 创建预约
$request = new CreateBookingRequest(
    activityId: 1,
    userId: 1,
    contactInfo: [
        'name' => '张三',
        'email' => 'zhangsan@example.com',
        'phone' => '13800138000'
    ],
    specialRequirements: '需要无障碍设施'
);
$booking = $bookingService->createBooking($request);

// 取消预约
$bookingService->cancelBooking($bookingId: 1, $userId: 1);

// 获取用户预约列表
$bookings = $bookingService->getUserBookings($userId: 1, new BookingFilter());

// 检查预约资格
$eligibility = $bookingService->checkBookingEligibility($activityId: 1, $userId: 1);
if ($eligibility->isEligible) {
    // 可以预约
} else {
    echo $eligibility->reason; // 原因
}
```

### 3. StatisticsService - 统计服务

提供活动和预约数据的统计分析。

```php
use App\Contracts\StatisticsServiceInterface;
use App\DTOs\DateRange;
use Carbon\Carbon;

$statisticsService = app(StatisticsServiceInterface::class);

// 获取活动统计
$stats = $statisticsService->getActivityStats($activityId: 1);
echo "总预约数: {$stats->totalBookings}\n";
echo "确认预约数: {$stats->confirmedBookings}\n";
echo "占用率: {$stats->occupancyRate}%\n";
echo "收入: {$stats->revenue}\n";

// 获取全局统计
$range = new DateRange(
    startDate: Carbon::now()->subDays(30),
    endDate: Carbon::now()
);
$globalStats = $statisticsService->getGlobalStats($range);

// 获取热门活动排行
$popularActivities = $statisticsService->getPopularActivities($range, $limit: 10);
foreach ($popularActivities as $activity) {
    echo "活动: {$activity->activityTitle}, 预约数: {$activity->bookingCount}\n";
}
```

## API 接口

### RESTful API 示例

系统提供了完整的 REST API 接口：

```php
// 获取活动列表
GET /api/activities?status=published&search=laravel

// 获取活动详情
GET /api/activities/{id}

// 创建活动
POST /api/activities
{
    "title": "Laravel 开发工作坊",
    "description": "学习 Laravel 框架的最佳实践",
    "price": 99.00,
    "capacity": 50,
    "start_date": "2025-01-15 09:00:00",
    "end_date": "2025-01-16 17:00:00"
}

// 创建预约
POST /api/activities/{activityId}/book
{
    "contact_info": {
        "name": "张三",
        "email": "zhangsan@example.com",
        "phone": "13800138000"
    },
    "special_requirements": "需要无障碍设施"
}

// 获取统计数据
GET /api/stats?activity_id=1
GET /api/stats?start_date=2025-01-01&end_date=2025-12-31
```

### 控制器使用

```php
use App\Http\Controllers\ActivityController;

$controller = new ActivityController();

// 在路由中注册
Route::apiResource('activities', ActivityController::class);
Route::post('activities/{activity}/book', [ActivityController::class, 'book']);
Route::get('stats', [ActivityController::class, 'stats']);
Route::get('demo', [ActivityController::class, 'demo']); // 演示完整流程
```

## 业务流程

### 正常预约流程

1. **创建活动**
   ```php
   $activity = $activityService->createActivity($createRequest);
   ```

2. **发布活动**
   ```php
   $activity = $activityService->updateActivity($activity->id,
       new UpdateActivityRequest(status: 'published'));
   ```

3. **用户预约**
   ```php
   $booking = $bookingService->createBooking($bookingRequest);
   ```

4. **确认预约成功**
   - 系统自动扣减活动可用名额
   - 生成唯一预约参考号
   - 预约状态设为 confirmed

### 异常处理

系统内置了完善的异常处理机制：

```php
try {
    $booking = $bookingService->createBooking($request);
} catch (ActivityNotFoundException $e) {
    // 活动不存在
} catch (ActivityFullException $e) {
    // 活动已满
} catch (DuplicateBookingException $e) {
    // 用户已预约
} catch (ActivityExpiredException $e) {
    // 活动已过期
}
```

## 数据模型

### Activity 活动模型

```php
$activity = new Activity([
    'title' => '活动标题',
    'slug' => '活动别名',
    'description' => '活动描述',
    'price' => 99.00,
    'capacity' => 50,
    'available_slots' => 50,
    'start_date' => Carbon::now(),
    'end_date' => Carbon::now()->addDay(),
    'status' => 'draft', // draft, published, cancelled, completed
    'created_by' => 1
]);
```

### Booking 预约模型

```php
$booking = new Booking([
    'booking_reference' => 'BK20250101123456',
    'activity_id' => 1,
    'user_id' => 1,
    'contact_info' => ['name' => '张三', 'email' => '...'],
    'special_requirements' => '特殊要求',
    'status' => 'confirmed' // pending, confirmed, cancelled, attended
]);
```

## 数据库表结构

- **activities**: 活动信息表
- **bookings**: 预约信息表
- **activity_logs**: 操作审计日志表

## 性能特性

- **并发控制**: 使用数据库行锁确保预约操作的原子性
- **缓存策略**: 支持活动详情缓存（可扩展）
- **查询优化**: 关键查询字段建立索引
- **分页查询**: 大数据量场景的分页支持

## 扩展指南

### 添加新功能

1. **定义接口**: 在 `Contracts/` 目录下添加新的服务接口
2. **实现服务**: 在 `Services/` 目录下实现具体业务逻辑
3. **创建DTO**: 在 `DTOs/` 目录下定义请求和响应数据结构
4. **添加异常**: 在 `Exceptions/` 目录下定义业务异常
5. **注册服务**: 在 `ActivityBookingServiceProvider` 中注册新服务

### 自定义验证规则

```php
// 在 DTO 中添加验证逻辑
public function __construct(
    public readonly string $title,
    public readonly int $capacity
) {
    if (strlen($title) < 1 || strlen($title) > 255) {
        throw new \InvalidArgumentException('标题长度无效');
    }
    if ($capacity < 1) {
        throw new \InvalidArgumentException('容量必须大于0');
    }
}
```

## 测试

系统采用单元测试驱动开发，所有核心业务逻辑都有对应的测试覆盖。

```bash
# 运行所有测试
vendor/bin/phpunit

# 运行特定测试类
vendor/bin/phpunit tests/Unit/Services/ActivityManagementServiceTest.php
```

## 部署

1. **安装依赖**
   ```bash
   composer install
   ```

2. **运行迁移**
   ```bash
   php artisan migrate
   ```

3. **启动服务**
   ```bash
   php artisan serve
   ```

## 许可证

Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
