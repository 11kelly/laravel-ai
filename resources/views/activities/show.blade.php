<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>活动详情 - 微型在线活动预约系统</title>
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
    <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- 加载状态 -->
        <div id="loading" class="text-center py-20">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary-100 border-t-primary-600"></div>
            <p class="mt-4 text-gray-500 font-medium">正在为您获取活动详情...</p>
        </div>

        <!-- 活动详情内容 -->
        <div id="activityDetail" class="hidden animate-fade-in">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- 左侧：活动主图和描述 -->
                <div class="lg:col-span-3 space-y-8">
                    <!-- 封面图 -->
                    <div class="relative h-[400px] rounded-3xl overflow-hidden shadow-2xl">
                        <img id="activityImage" src="" alt="" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                        <div class="absolute bottom-8 left-8 right-8 text-white">
                            <div class="flex items-center space-x-3 mb-4">
                                <span id="statusBadge" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/20 backdrop-blur-md"></span>
                                <span id="createdAt" class="text-sm text-white/80"></span>
                            </div>
                            <h1 id="activityTitle" class="text-4xl font-extrabold drop-shadow-lg"></h1>
                        </div>
                    </div>

                    <!-- 描述区 -->
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                            <span class="w-1.5 h-8 bg-primary-600 rounded-full mr-4"></span>
                            活动描述
                        </h2>
                        <div id="activityDescription" class="text-gray-600 text-lg leading-relaxed whitespace-pre-wrap"></div>
                    </div>

                    <!-- 统计条 -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center mx-auto mb-3 text-primary-600 text-lg">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase mb-1">开始日期</div>
                            <div id="startDateDisplay" class="font-bold text-gray-900"></div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center mx-auto mb-3 text-orange-600 text-lg">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase mb-1">剩余名额</div>
                            <div id="slotsDisplay" class="font-bold text-gray-900"></div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mx-auto mb-3 text-green-600 text-lg">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase mb-1">费用</div>
                            <div id="priceDisplay" class="font-bold text-gray-900"></div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
                            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mx-auto mb-3 text-blue-600 text-lg">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase mb-1">创建者</div>
                            <div id="creatorNameDisplay" class="font-bold text-gray-900"></div>
                        </div>
                    </div>
                </div>

                <!-- 右侧：预约表单 -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 sticky top-24">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-bold text-gray-900">立即预约</h2>
                            <div class="flex items-center text-primary-600 font-bold" id="formPrice"></div>
                        </div>

                        <!-- 预约区域 -->
                        <div id="bookingSection">
                            <!-- 未完善个人资料 -->
                            <div id="profileWarning" class="hidden space-y-4 mb-6">
                                <div class="bg-orange-50 p-6 rounded-2xl border border-orange-100 text-center">
                                    <i class="fas fa-exclamation-triangle text-3xl text-orange-500 mb-4"></i>
                                    <h3 class="font-bold text-orange-900">请先完善您的资料</h3>
                                    <p class="text-sm text-orange-700 mt-2 mb-6">为了确保能够联系到您，预约前需先完善个人联系方式。</p>
                                    <a href="{{ route('dashboard.profile') }}" class="block w-full py-3 bg-orange-500 text-white font-bold rounded-xl hover:bg-orange-600 transition-colors shadow-md shadow-orange-200">
                                        立即完善资料
                                    </a>
                                </div>
                            </div>

                            <!-- 已完善资料，显示联系信息 -->
                            <div id="contactInfo" class="hidden mb-8">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xs font-bold text-gray-400 uppercase">您的联系信息</span>
                                    <a href="{{ route('dashboard.profile') }}" class="text-xs text-primary-600 font-bold hover:underline">修改</a>
                                </div>
                                <div id="contactDetails" class="bg-gray-50 p-5 rounded-2xl space-y-3 text-sm border border-gray-100">
                                    <!-- 动态内容 -->
                                </div>
                            </div>

                            <!-- 预约表单 -->
                            <form id="bookingForm" class="hidden space-y-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">特殊需求</label>
                                    <textarea name="special_requirements" rows="4" class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary-500 outline-none transition-all placeholder-gray-400 text-sm" placeholder="如过敏史、座位要求等，如有请告诉我们..."></textarea>
                                </div>

                                <button type="submit" id="bookingSubmitBtn" class="w-full py-4 bg-gray-900 text-white font-bold rounded-2xl hover:bg-primary-600 transition-all shadow-lg hover:shadow-primary-200 active:scale-95 flex items-center justify-center space-x-2">
                                    <i class="fas fa-check-circle"></i>
                                    <span>确认预约报名</span>
                                </button>
                                <p class="text-center text-[10px] text-gray-400 font-medium">点击预约即代表您同意我们的活动条款</p>
                            </form>
                        </div>

                        <!-- 状态不可用消息 -->
                        <div id="statusMessage" class="hidden text-center py-6">
                            <div id="statusIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"></div>
                            <h3 id="statusHeading" class="font-bold text-lg"></h3>
                            <p id="statusDescription" class="text-sm text-gray-500 mt-2"></p>
                        </div>

                        <!-- 未登录提示 -->
                        @guest
                        <div class="text-center py-6">
                            <div class="bg-primary-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-primary-600">
                                <i class="fas fa-user-lock text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-lg text-gray-900">请先登入账号</h3>
                            <p class="text-sm text-gray-500 mt-2 mb-8">您需要登入后才能进行活动预约。</p>
                            <a href="{{ route('login') }}" class="block w-full py-4 bg-primary-600 text-white font-bold rounded-2xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-200">
                                立即登入
                            </a>
                        </div>
                        @endguest
                    </div>
                </div>
            </div>
        </div>

        <!-- 错误提示 -->
        <div id="errorMessage" class="text-center py-24 hidden">
            <div class="bg-red-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-exclamation-circle text-4xl text-red-500"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">抱歉，找不到该活动</h3>
            <p class="text-gray-500 mb-8">该活动可能已下架或链接失效。</p>
            <a href="{{ route('activities.index') }}" class="inline-flex items-center px-8 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-primary-600 transition-all shadow-md">
                返回活动中心
            </a>
        </div>
    </main>


    <script>
        const activityId = Number("{{ $activityId }}");
        const isUserAuthenticated = "{{ auth()->check() }}" === "1";
        let currentActivity = null;

        // XSS防护：HTML转义辅助函数
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadActivityDetail();
            setupEventListeners();
        });

        function setupEventListeners() {
            const form = document.getElementById('bookingForm');
            if (form) form.addEventListener('submit', handleBookingSubmit);
        }

        async function loadActivityDetail() {
            const loading = document.getElementById('loading');
            const detail = document.getElementById('activityDetail');
            const error = document.getElementById('errorMessage');

            try {
                const response = await fetch(`/api/activities/${activityId}`);
                if (!response.ok) throw new Error();

                const activity = await response.json();
                currentActivity = activity;

                renderActivityDetail(activity);
                detail.classList.remove('hidden');
            } catch (err) {
                error.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
            }
        }

        function renderActivityDetail(activity) {
            // 基本内容
            document.getElementById('activityTitle').textContent = activity.title;
            document.getElementById('activityDescription').textContent = activity.description;
            document.getElementById('createdAt').textContent = '发布于 ' + formatDate(activity.created_at);
            
            // 图片处理：如果上传了就用上传的，否则用默认图
            const img = document.getElementById('activityImage');
            const defaultImage = 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&w=1200&q=80';

            if (activity.image) {
                // 使用异步方式加载图片
                (async () => {
                    try {
                        const imageResponse = await fetch('/storage/' + activity.image);
                        if (imageResponse.ok) {
                            const blob = await imageResponse.blob();
                            const reader = new FileReader();
                            reader.onload = () => {
                                img.src = reader.result;
                            };
                            reader.readAsDataURL(blob);
                        } else {
                            img.src = defaultImage;
                        }
                    } catch (error) {
                        console.warn('Failed to load activity image:', activity.image, error);
                        img.src = defaultImage;
                    }
                })();
            } else {
                img.src = defaultImage;
            }

            // 容错处理
            img.onerror = function() {
                this.src = defaultImage;
                this.onerror = null;
            };
            
            // 状态
            const badge = document.getElementById('statusBadge');
            badge.textContent = getStatusText(activity.status);
            
            // 数据卡片
            document.getElementById('startDateDisplay').textContent = formatDate(activity.start_date);
            document.getElementById('slotsDisplay').textContent = `${activity.available_slots} / ${activity.capacity}`;
            document.getElementById('priceDisplay').textContent = activity.price > 0 ? `NT$ ${activity.price}` : '免费';
            document.getElementById('creatorNameDisplay').textContent = activity.creator?.name || '系统';
            document.getElementById('formPrice').textContent = activity.price > 0 ? `NT$ ${activity.price}` : '免费';

            // 权限和状态判断
            updateBookingStatus(activity);
        }

        async function updateBookingStatus(activity) {
            const section = document.getElementById('bookingSection');
            const msgArea = document.getElementById('statusMessage');
            
            if (!isUserAuthenticated) {
                section.classList.add('hidden');
                return;
            }

            if (activity.status !== 'published') {
                showStatusMessage('bg-gray-100 text-gray-400', 'fas fa-stop-circle', '活动未开放', '此活动目前不接受预约报名。');
                section.classList.add('hidden');
            } else if (activity.available_slots <= 0) {
                showStatusMessage('bg-orange-50 text-orange-400', 'fas fa-users-slash', '名额已满', '抱歉，该活动名额已预约完毕。');
                section.classList.add('hidden');
            } else {
                // 检查个人资料
                await checkUserProfile();
            }
        }

        async function checkUserProfile() {
            try {
                const response = await fetch('/api/user/profile');
                if (!response.ok) return;

                const profile = await response.json();
                const warning = document.getElementById('profileWarning');
                const info = document.getElementById('contactInfo');
                const form = document.getElementById('bookingForm');

                if (profile.profile_completed) {
                    warning.classList.add('hidden');
                    info.classList.remove('hidden');
                    form.classList.remove('hidden');

                    const contact = profile.contact_info;
                    const contactDetails = document.getElementById('contactDetails');
                    contactDetails.innerHTML = '';

                    // 安全地创建DOM元素
                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'flex justify-between';
                    nameDiv.innerHTML = `
                        <span class="text-gray-400">姓名</span>
                        <span class="font-bold text-gray-700">${escapeHtml(contact.name || '')}</span>
                    `;
                    contactDetails.appendChild(nameDiv);

                    const emailDiv = document.createElement('div');
                    emailDiv.className = 'flex justify-between';
                    emailDiv.innerHTML = `
                        <span class="text-gray-400">邮箱</span>
                        <span class="font-bold text-gray-700">${escapeHtml(contact.email || '')}</span>
                    `;
                    contactDetails.appendChild(emailDiv);

                    const addressDiv = document.createElement('div');
                    addressDiv.className = 'flex justify-between';
                    addressDiv.innerHTML = `
                        <span class="text-gray-400">地址</span>
                        <span class="font-bold text-gray-700">${escapeHtml(contact.address || '-')}</span>
                    `;
                    contactDetails.appendChild(addressDiv);
                } else {
                    warning.classList.remove('hidden');
                    info.classList.add('hidden');
                    form.classList.add('hidden');
                }
            } catch (err) {}
        }

        function showStatusMessage(colors, icon, heading, desc) {
            const area = document.getElementById('statusMessage');
            const iconDiv = document.getElementById('statusIcon');
            area.classList.remove('hidden');
            iconDiv.className = `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ${colors}`;
            iconDiv.innerHTML = `<i class="${icon} text-2xl"></i>`;
            document.getElementById('statusHeading').textContent = heading;
            document.getElementById('statusDescription').textContent = desc;
        }

        async function handleBookingSubmit(e) {
            e.preventDefault();
            const btn = document.getElementById('bookingSubmitBtn');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = `<i class="fas fa-spinner animate-spin"></i> <span>正在处理预约...</span>`;

            try {
                const response = await fetch(`/api/activities/${activityId}/book`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        special_requirements: e.target.special_requirements.value
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('预约成功！您的预约编号为：' + result.booking.booking_reference);
                    window.location.href = '{{ route("dashboard.index") }}';
                } else {
                    alert(result.error || '预约失败，请重试');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                alert('网络连接错误');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        function getStatusText(status) {
            return { 'draft': '草稿', 'published': '进行中', 'cancelled': '已取消', 'completed': '已结束' }[status] || status;
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('zh-CN', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
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
