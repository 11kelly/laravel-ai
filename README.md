<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 关于 Laravel AI Kelly

Laravel AI Kelly 是一个基于 Laravel 框架和 Filament 管理面板构建的 AI 助手应用。本项目结合了 Laravel 的优雅语法和 Filament 的强大管理功能，为用户提供现代化的 Web 应用体验。

### 主要特性

- 🚀 基于 Laravel 11.x 框架
- 🎨 使用 Filament 4.x 管理面板
- 🤖 集成 AI 功能
- 📱 响应式设计
- 🔒 安全的用户认证系统

## 安装和设置

### 环境要求

- PHP 8.2 或更高版本
- Composer
- Node.js 和 npm
- SQLite / MySQL / PostgreSQL

### 安装步骤

1. **克隆项目**
   ```bash
   git clone <repository-url>
   cd laravel-ai-kelly
   ```

2. **安装 PHP 依赖**
   ```bash
   composer install
   ```

3. **环境配置**
   ```bash
   cp .env.example .env
   ```

4. **生成应用密钥**
   ```bash
   php artisan key:generate
   ```

5. **发布 Filament 资源**
   ```bash
   php artisan vendor:publish --provider="Filament\FilamentServiceProvider"
   ```

6. **运行数据库迁移**
   ```bash
   php artisan migrate
   ```

7. **创建 Filament 用户**
   ```bash
   php artisan make:filament-user
   ```

8. **安装前端依赖**
   ```bash
   npm install
   npm run build
   ```

9. **启动开发服务器**
   ```bash
   php artisan serve
   ```

访问 `http://localhost:8000` 查看应用，访问 `http://localhost:8000/admin` 进入管理面板。

## 常用命令

### 清理缓存

```bash
# 清理应用缓存
php artisan cache:clear

# 清理配置缓存
php artisan config:clear

# 清理路由缓存
php artisan route:clear

# 清理视图缓存
php artisan view:clear

# 清理所有缓存
php artisan optimize:clear

# 重新生成 Composer 自动加载
composer dump-autoload
```

### 数据库相关

```bash
# 运行迁移
php artisan migrate

# 回滚迁移
php artisan migrate:rollback

# 创建迁移文件
php artisan make:migration create_example_table

# 运行数据填充
php artisan db:seed
```

### Filament 相关

```bash
# 创建 Filament 资源
php artisan make:filament-resource User

# 创建 Filament 页面
php artisan make:filament-page Dashboard

# 创建 Filament 组件
php artisan make:filament-widget StatsOverview
```

## 学习资源

### Laravel 文档

- [Laravel 官方文档](https://laravel.com/docs) - 最全面和深入的 Laravel 文档
- [Laravel Learn](https://laravel.com/learn) - 引导您构建现代 Laravel 应用
- [Filament 文档](https://filamentphp.com/docs) - Filament 管理面板官方文档

### 视频教程

如果您不喜欢阅读，[Laracasts](https://laracasts.com) 可以帮助您。Laracasts 包含数千个视频教程，涵盖 Laravel、现代 PHP、单元测试和 JavaScript 等主题。通过深入研究我们的综合视频库来提升您的技能。

### 社区资源

- [Laravel 中文文档](https://learnku.com/docs/laravel) - Laravel 中文社区文档
- [Filament 中文社区](https://filamentphp.cn/) - Filament 中文社区支持

## 开发说明

### 本地开发环境

本项目使用以下技术栈：

- **后端**: Laravel 11.x + PHP 8.2+
- **前端**: Blade 模板 + Tailwind CSS
- **管理面板**: Filament 4.x
- **数据库**: SQLite (开发环境) / MySQL (生产环境)
- **构建工具**: Vite

### 项目结构

```
laravel-ai-kelly/
├── app/                 # 应用核心代码
├── resources/           # 视图和资源文件
├── routes/              # 路由定义
├── database/            # 数据库迁移和种子
├── public/              # 公共资源
├── storage/             # 文件存储
├── tests/               # 测试文件
└── config/              # 配置文件
```

## 贡献指南

感谢您考虑为 Laravel AI Kelly 项目做出贡献！

### 开发流程

1. Fork 本项目
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建 Pull Request

### 代码规范

- 遵循 PSR-12 编码标准
- 使用英文编写提交信息
- 为新功能编写测试
- 确保所有测试通过

## 安全漏洞

如果您在本项目中发现安全漏洞，请发送电子邮件至 [security@ebrook.com.tw](mailto:security@ebrook.com.tw)。所有安全漏洞将被及时处理。

## 许可证

本项目基于 MIT 许可证开源 - 查看 [LICENSE](LICENSE) 文件了解详情。

---

**Developed by eBrook Group.**  
Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
