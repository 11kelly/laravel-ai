<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>活动列表 - 微型在线活动预约系统</title>
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
        .activity-card:hover .activity-image { transform: scale(1.05); }
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            line-clamp: 1;
            overflow: hidden;
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-clamp: 2;
            overflow: hidden;
        }
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

    <!-- 英雄区 -->
    <header class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
                    探索精彩活动
                </h1>
                <p class="mt-5 max-w-xl mx-auto text-xl text-gray-500">
                    发现、预约并参与您感兴趣的所有线上线下活动。
                </p>
            </div>
        </div>
    </header>

    <!-- 过滤和搜索 -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <div class="bg-white p-4 rounded-xl shadow-xl border border-gray-100 flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="搜索您感兴趣的活动标题或描述..."
                       class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all outline-none">
            </div>
            <div class="md:w-48 relative">
                <i class="fas fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <select id="statusFilter" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg appearance-none focus:ring-2 focus:ring-primary-500 transition-all outline-none cursor-pointer">
                    <option value="">全部状态</option>
                    <option value="published">进行中</option>
                    <option value="completed">已结束</option>
                </select>
            </div>
        </div>
    </section>

    <!-- 主要内容 -->
    <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- 加载状态 -->
        <div id="loading" class="text-center py-20">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary-100 border-t-primary-600"></div>
            <p class="mt-4 text-gray-500 font-medium">正在为您获取活动...</p>
        </div>

        <!-- 活动列表 -->
        <div id="activitiesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- 动态加载内容 -->
        </div>

        <!-- 无数据提示 -->
        <div id="noData" class="text-center py-24 hidden">
            <div class="bg-gray-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-calendar-times text-4xl text-gray-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">暂无符合条件的活动</h3>
            <p class="text-gray-500 max-w-sm mx-auto">尝试调整您的搜索关键词或过滤条件。</p>
            <button onclick="resetFilters()" class="mt-6 text-primary-600 font-semibold hover:text-primary-700">
                清除所有过滤条件
            </button>
        </div>
    </main>


    <!-- 活动卡片模板 -->
    <template id="activityCardTemplate">
        <div class="activity-card group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden transition-all duration-300 flex flex-col h-full">
            <!-- 图片区域 -->
            <div class="relative h-48 overflow-hidden">
                <img src="" alt="" class="activity-image w-full h-full object-cover transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="absolute top-4 left-4">
                    <span class="status-badge px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm"></span>
                </div>
                <div class="absolute bottom-4 right-4 text-white font-bold text-lg drop-shadow-md activity-price-tag">
                </div>
            </div>

            <!-- 内容区域 -->
            <div class="p-6 flex-1 flex flex-col">
                <div class="flex items-center text-xs text-gray-400 mb-2 uppercase tracking-widest font-bold">
                    <i class="far fa-clock mr-1"></i>
                    <span class="created-at"></span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition-colors line-clamp-1 activity-title"></h3>
                <p class="text-gray-500 text-sm mb-6 line-clamp-2 activity-description flex-1"></p>

                <!-- 详情列表 -->
                <div class="grid grid-cols-2 gap-4 mb-6 pt-4 border-t border-gray-50">
                    <div class="flex items-center text-sm text-gray-600">
                        <div class="w-8 h-8 rounded-lg bg-primary-50 flex items-center justify-center mr-3 text-primary-600">
                            <i class="far fa-calendar-check"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-400 font-bold uppercase">活动日期</span>
                            <span class="activity-date font-medium truncate"></span>
                        </div>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center mr-3 text-orange-600">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-400 font-bold uppercase">剩余名额</span>
                            <span class="activity-slots font-medium"></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-auto">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs font-bold mr-2 border-2 border-white shadow-sm creator-avatar">
                            ?
                        </div>
                        <span class="text-xs font-semibold text-gray-600 creator-name"></span>
                    </div>
                    <a href="#" class="view-activity-btn inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-primary-600 transition-all shadow-sm active:scale-95">
                        立即预约 <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </template>

    <script>
        let activities = [];
        let filteredActivities = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadActivities();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', debounce(() => filterActivities(), 300));
            document.getElementById('statusFilter').addEventListener('change', () => filterActivities());
        }

        async function loadActivities() {
            const loading = document.getElementById('loading');
            const grid = document.getElementById('activitiesGrid');
            const noData = document.getElementById('noData');

            loading.classList.remove('hidden');
            grid.innerHTML = '';
            noData.classList.add('hidden');

            try {
                const response = await fetch('/api/activities');
                const data = await response.json();

                activities = data.data || [];
                filteredActivities = [...activities];

                await renderActivities(filteredActivities);
            } catch (error) {
                console.error('加载活动失败:', error);
                alert('系统繁忙，请稍后再试');
            } finally {
                loading.classList.add('hidden');
            }
        }

        async function renderActivities(activitiesToRender) {
            const grid = document.getElementById('activitiesGrid');
            const noData = document.getElementById('noData');

            grid.innerHTML = '';

            if (activitiesToRender.length === 0) {
                noData.classList.remove('hidden');
                return;
            }

            noData.classList.add('hidden');

            for (const activity of activitiesToRender) {
                const card = await createActivityCard(activity);
                grid.appendChild(card);
            }
        }

        async function createActivityCard(activity) {
            const template = document.getElementById('activityCardTemplate');
            const card = template.content.cloneNode(true);

            // 图片处理：如果上传了就用上传的，否则用默认图
            const img = card.querySelector('.activity-image');
            const defaultImage = 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&w=800&q=80';

            if (activity.image) {
                try {
                    // 尝试获取图片的base64数据
                    const imageResponse = await fetch('/storage/' + activity.image);
                    if (imageResponse.ok) {
                        const blob = await imageResponse.blob();
                        const reader = new FileReader();
                        await new Promise(resolve => {
                            reader.onload = () => {
                                img.src = reader.result;
                                resolve();
                            };
                            reader.readAsDataURL(blob);
                        });
                    } else {
                        img.src = defaultImage;
                    }
                } catch (error) {
                    console.warn('Failed to load image:', activity.image, error);
                    img.src = defaultImage;
                }
            } else {
                img.src = defaultImage;
            }

            // 容错处理：如果图片加载失败，显示默认图
            img.onerror = function() {
                this.src = defaultImage;
                this.onerror = null; // 防止死循环
            };
            
            img.alt = activity.title;

            // 状态徽章
            const statusBadge = card.querySelector('.status-badge');
            statusBadge.textContent = getStatusText(activity.status);
            statusBadge.classList.add(...getStatusClasses(activity.status));

            // 内容
            card.querySelector('.activity-title').textContent = activity.title;
            card.querySelector('.activity-description').textContent = activity.description;
            card.querySelector('.created-at').textContent = formatDate(activity.created_at);
            card.querySelector('.activity-date').textContent = formatDate(activity.start_date);
            card.querySelector('.activity-slots').textContent = `${activity.available_slots} / ${activity.capacity}`;
            
            // 价格
            const priceTag = card.querySelector('.activity-price-tag');
            priceTag.innerHTML = activity.price > 0 ? `<span class="text-sm font-normal">NT$</span>${activity.price}` : '免费';
            if (activity.price <= 0) priceTag.classList.add('text-green-400');

            // 创建者
            const creatorName = activity.creator?.name || '系统';
            card.querySelector('.creator-name').textContent = creatorName;
            card.querySelector('.creator-avatar').textContent = creatorName.charAt(0).toUpperCase();

            // 链接
            card.querySelector('.view-activity-btn').href = `/activities/${activity.id}`;

            return card;
        }

        async function filterActivities() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;

            filteredActivities = activities.filter(activity => {
                const matchesSearch = activity.title.toLowerCase().includes(searchTerm) ||
                                    activity.description.toLowerCase().includes(searchTerm);
                const matchesStatus = !statusFilter || activity.status === statusFilter;
                return matchesSearch && matchesStatus;
            });

            await renderActivities(filteredActivities);
        }

        async function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            await filterActivities();
        }

        function getStatusText(status) {
            return { 'draft': '草稿', 'published': '进行中', 'cancelled': '已取消', 'completed': '已结束' }[status] || status;
        }

        function getStatusClasses(status) {
            const map = {
                'draft': ['bg-gray-100', 'text-gray-600'],
                'published': ['bg-green-100', 'text-green-700'],
                'cancelled': ['bg-red-100', 'text-red-700'],
                'completed': ['bg-blue-100', 'text-blue-700']
            };
            return map[status] || ['bg-gray-100', 'text-gray-600'];
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('zh-CN', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    </script>

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
</body>
</html>
