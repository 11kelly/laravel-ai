<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>服务条款 - 微型在线活动预约系统</title>
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
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">服务条款</h1>
                    <p class="text-gray-600">最后更新时间：{{ date('Y年m月d日') }}</p>
                </div>

                <div class="prose prose-gray max-w-none">
                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">1. 接受条款</h2>
                    <p class="text-gray-700 mb-4">
                        欢迎使用微型在线活动预约系统（以下简称"本系统"）。本服务条款（以下简称"条款"）是您与eBrook Group（以下简称"我们"或"本公司"）之间的法律协议。
                        使用本系统即表示您同意接受本条款的所有规定。如果您不同意本条款的任何部分，请勿使用本系统。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">2. 服务描述</h2>
                    <p class="text-gray-700 mb-4">
                        本系统提供在线活动预约服务，包括但不限于：
                    </p>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li>活动信息浏览和搜索</li>
                        <li>在线活动预约和报名</li>
                        <li>预约确认和通知</li>
                        <li>个人预约记录管理</li>
                        <li>用户个人资料管理</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">3. 用户资格</h2>
                    <p class="text-gray-700 mb-4">
                        要使用本系统服务，您必须：
                    </p>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li>年满13周岁</li>
                        <li>具备完全民事行为能力</li>
                        <li>同意遵守本条款</li>
                        <li>提供真实、准确、完整的注册信息</li>
                    </ul>
                    <p class="text-gray-700 mb-4">
                        如果您不符合上述条件，请勿使用本系统。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">4. 用户账户</h2>
                    <h3 class="text-xl font-medium text-gray-900 mt-6 mb-3">4.1 账户创建</h3>
                    <p class="text-gray-700 mb-4">
                        您需要创建账户才能使用某些功能。创建账户时，您必须提供有效的邮箱地址作为登录凭证。
                    </p>

                    <h3 class="text-xl font-medium text-gray-900 mt-6 mb-3">4.2 账户安全</h3>
                    <p class="text-gray-700 mb-4">
                        您对账户和密码的安全负全部责任。任何使用您的账户进行的活动均视为您的行为。
                        如果发现账户安全问题，请立即通知我们。
                    </p>

                    <h3 class="text-xl font-medium text-gray-900 mt-6 mb-3">4.3 账户终止</h3>
                    <p class="text-gray-700 mb-4">
                        我们保留随时终止或暂停您账户的权利，无需事先通知。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">5. 使用规则</h2>
                    <p class="text-gray-700 mb-4">使用本系统时，您同意：</p>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li>不违反任何适用法律法规</li>
                        <li>不侵犯他人知识产权</li>
                        <li>不发布虚假、误导性或有害内容</li>
                        <li>不进行任何破坏系统安全的行为</li>
                        <li>不使用自动化工具进行数据抓取</li>
                        <li>不进行任何可能损害系统正常运行的行为</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">6. 预约和取消</h2>
                    <h3 class="text-xl font-medium text-gray-900 mt-6 mb-3">6.1 预约确认</h3>
                    <p class="text-gray-700 mb-4">
                        活动预约成功后，您将收到预约确认通知。预约确认后即产生法律效力。
                    </p>

                    <h3 class="text-xl font-medium text-gray-900 mt-6 mb-3">6.2 取消政策</h3>
                    <p class="text-gray-700 mb-4">
                        每个活动可能有不同的取消政策。请在预约前仔细阅读活动详情中的取消条款。
                        一般情况下，活动开始前24小时可以免费取消，之后可能产生费用。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">7. 知识产权</h2>
                    <p class="text-gray-700 mb-4">
                        本系统及其所有内容（包括但不限于文字、图形、徽标、图标、图像、音频剪辑、数字下载、数据编译和软件）均为本公司或其内容提供者的财产，受知识产权法保护。
                    </p>
                    <p class="text-gray-700 mb-4">
                        未经书面许可，您不得复制、修改、分发或创建衍生作品。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">8. 免责声明</h2>
                    <p class="text-gray-700 mb-4">
                        本系统按"现状"提供，不提供任何明示或默示保证。我们不保证：
                    </p>
                    <ul class="list-disc list-inside text-gray-700 mb-4 space-y-2">
                        <li>服务不会中断或无错误</li>
                        <li>缺陷将被纠正</li>
                        <li>服务不含病毒或其他有害组件</li>
                        <li>活动信息完全准确和及时</li>
                    </ul>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">9. 责任限制</h2>
                    <p class="text-gray-700 mb-4">
                        在任何情况下，本公司对您使用本系统所产生的任何间接、附带、特殊、后果性或惩罚性损害不承担责任，
                        无论此类损害是基于合同、侵权（包括过失）还是其他原因。
                    </p>
                    <p class="text-gray-700 mb-4">
                        我们的总责任不超过您为使用相关服务支付的金额。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">10. 赔偿</h2>
                    <p class="text-gray-700 mb-4">
                        您同意赔偿并使本公司免受因您违反本条款、使用本系统或侵犯他人权利而引起的任何索赔、损害、损失、责任和费用。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">11. 终止服务</h2>
                    <p class="text-gray-700 mb-4">
                        我们可以随时终止或暂停您对本系统的访问，无需事先通知或承担责任。
                        终止后，本条款中继续有效的条款将继续有效。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">12. 适用法律</h2>
                    <p class="text-gray-700 mb-4">
                        本条款受中华民国（台湾）法律管辖，并按其解释。任何因本条款引起的争议应提交台北地方法院管辖。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">13. 条款修改</h2>
                    <p class="text-gray-700 mb-4">
                        我们保留随时修改本条款的权利。修改后的条款将在本系统上公布。继续使用本系统即表示您接受修改后的条款。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">14. 完整协议</h2>
                    <p class="text-gray-700 mb-4">
                        本条款构成您与我们之间的完整协议，取代所有先前协议。隐私政策是本条款的组成部分。
                    </p>

                    <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">15. 联系我们</h2>
                    <p class="text-gray-700 mb-4">
                        如果您对本条款有任何疑问，请通过以下方式联系我们：
                    </p>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-700"><strong>邮箱：</strong>legal@ebrook.com.tw</p>
                        <p class="text-gray-700"><strong>公司：</strong>eBrook Group</p>
                        <p class="text-gray-700"><strong>地址：</strong>台湾</p>
                    </div>

                    <div class="mt-8 p-4 bg-amber-50 border-l-4 border-amber-400">
                        <p class="text-amber-800">
                            <strong>重要提示：</strong>请仔细阅读并理解本条款。如果您有任何疑问，请在开始使用服务前联系我们。
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
