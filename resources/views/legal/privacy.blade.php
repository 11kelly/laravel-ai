<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>隐私政策 - 微型在线活动预约系统</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#3B82F6',
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <!-- 导航栏 -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('activities.index') }}" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-800">活动预约</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('activities.index') }}" class="text-gray-600 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">活动中心</a>
                    @if(auth()->check())
                        <div class="h-6 w-px bg-gray-200 mx-2"></div>
                        <a href="{{ route('dashboard.index') }}" class="flex items-center space-x-2 text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            <i class="fas fa-user-circle text-lg"></i>
                            <span>个人中心</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-sign-out-alt mr-1"></i>退出
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 shadow-sm transition-all">
                            登入账号
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- 主要内容 -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-8 sm:px-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">隐私政策</h1>
                    <p class="text-gray-600">最后更新时间：{{ date('Y年m月d日') }}</p>
                </div>

                <div class="prose prose-gray max-w-none">
                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">1. 信息收集</h2>
                    <p class="text-gray-700 mb-4">
                        我们致力于保护您的隐私。本隐私政策说明了微型在线活动预约系统（以下简称"本系统"）如何收集、使用和保护您的个人信息。
                    </p>

                    <h3 class="text-xl font-medium text-gray-900 mt-6 mb-3">1.1 我们收集的信息</h3>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li><strong>账户信息：</strong>邮箱地址、密码（加密存储）</li>
                        <li><strong>个人资料：</strong>姓名、联系地址（可选）</li>
                        <li><strong>使用数据：</strong>活动浏览记录、预约历史</li>
                        <li><strong>技术信息：</strong>IP地址、浏览器类型、设备信息</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">2. 信息使用</h2>
                    <p class="text-gray-700 mb-4">我们收集的信息将用于以下目的：</p>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li>提供和维护活动预约服务</li>
                        <li>处理您的预约申请和确认</li>
                        <li>发送服务相关通知</li>
                        <li>改进我们的服务质量</li>
                        <li>确保平台安全和防止欺诈</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">3. 信息共享</h2>
                    <p class="text-gray-700 mb-4">
                        我们承诺不会出售、出租或以其他方式披露您的个人信息，除非：
                    </p>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li>获得您的明确同意</li>
                        <li>法律要求或政府部门要求</li>
                        <li>保护我们的合法权益</li>
                        <li>与可信赖的第三方服务提供商合作（已签署保密协议）</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">4. 数据安全</h2>
                    <p class="text-gray-700 mb-4">
                        我们采用行业标准的安全措施来保护您的个人信息：
                    </p>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li>数据传输使用HTTPS加密</li>
                        <li>密码采用bcrypt算法加密存储</li>
                        <li>定期安全审计和漏洞扫描</li>
                        <li>限制内部人员访问权限</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">5. Cookie使用</h2>
                    <p class="text-gray-700 mb-4">
                        我们使用Cookie来改善您的浏览体验，包括：
                    </p>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li>保持登录状态</li>
                        <li>记住您的偏好设置</li>
                        <li>分析网站使用情况</li>
                    </ul>
                    <p class="text-gray-700 mb-4">
                        您可以通过浏览器设置管理Cookie偏好。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">6. 您的权利</h2>
                    <p class="text-gray-700 mb-4">根据适用的数据保护法，您享有以下权利：</p>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li><strong>访问权：</strong>查看我们存储的您的个人信息</li>
                        <li><strong>更正权：</strong>要求更正不准确的信息</li>
                        <li><strong>删除权：</strong>要求删除您的个人信息</li>
                        <li><strong>反对权：</strong>反对我们处理您的个人信息</li>
                        <li><strong>数据 portability：</strong>以结构化格式获取您的信息</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">7. 儿童隐私</h2>
                    <p class="text-gray-700 mb-4">
                        本系统不面向13岁以下儿童。我们不会故意收集13岁以下儿童的个人信息。如果我们发现收集了此类信息，我们将立即删除。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">8. 隐私政策更新</h2>
                    <p class="text-gray-700 mb-4">
                        我们可能会不定期更新本隐私政策。重大变更时，我们会通过系统通知或邮件方式告知您。请定期查看本政策以了解最新内容。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">9. 联系我们</h2>
                    <p class="text-gray-700 mb-4">
                        如果您对本隐私政策有任何疑问或需要行使您的隐私权利，请通过以下方式联系我们：
                    </p>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700"><strong>邮箱：</strong>privacy@ebrook.com.tw</p>
                        <p class="text-gray-700"><strong>公司：</strong>eBrook Group</p>
                        <p class="text-gray-700"><strong>地址：</strong>台湾</p>
                    </div>

                    <div class="mt-8 p-4 bg-blue-50 border-l-4 border-blue-400">
                        <p class="text-blue-800">
                            <strong>重要提示：</strong>使用本系统即表示您同意本隐私政策。如果您不同意本政策，请停止使用我们的服务。
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 返回顶部按钮 -->
        <button id="backToTop" class="fixed bottom-8 right-8 bg-primary-600 text-white p-3 rounded-full shadow-lg hover:bg-primary-700 transition-colors opacity-0 invisible">
            <i class="fas fa-arrow-up"></i>
        </button>
    </main>

    <!-- 页脚 -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white"></i>
                        </div>
                        <span class="text-xl font-bold">活动预约</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        专业的在线活动预约平台，为您提供便捷的活动报名服务。
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">快速链接</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('activities.index') }}" class="text-gray-400 hover:text-white transition-colors">活动中心</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="text-gray-400 hover:text-white transition-colors">隐私政策</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="text-gray-400 hover:text-white transition-colors">服务条款</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">联系我们</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i>support@ebrook.com.tw</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>台湾</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} eBrook Group. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // 返回顶部按钮
        const backToTopBtn = document.getElementById('backToTop');

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.remove('opacity-0', 'invisible');
            } else {
                backToTopBtn.classList.add('opacity-0', 'invisible');
            }
        });

        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
