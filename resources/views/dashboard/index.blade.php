<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>个人中心 - 微型在线活动预约系统</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        secondary: '#64748B',
                        accent: '#F59E0B',
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom styles if needed */
    </style>
</head>
<body class="bg-gray-50">
    <!-- 导航栏 -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">微型在线活动预约系统</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('activities.index') }}" class="text-gray-700 hover:text-primary px-3 py-2 rounded-md text-sm font-medium">活动列表</a>
                    <span class="text-gray-700 text-sm">欢迎，{{ auth()->user()->name }}</span>
                    <a href="{{ route('dashboard.index') }}" class="bg-primary text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">个人中心</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-700 hover:text-primary px-3 py-2 rounded-md text-sm font-medium">退出</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- 主要内容 -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
            <div class="bg-white border-b border-gray-200 px-6 py-6">
                <h1 class="text-2xl font-bold text-gray-900">个人中心</h1>
                <p class="text-gray-500 mt-1">管理您的个人资料和活动预约</p>
            </div>

            <div class="flex">
                <!-- Sidebar -->
                <div class="w-64 bg-gray-50 border-r border-gray-200 min-h-screen">
                    <nav class="mt-8">
                        <div class="px-4 space-y-2">
                            <a href="{{ route('dashboard.index') }}" id="nav-bookings"
                               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard.index') ? 'bg-primary-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-100' }} transition-all">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                我的预约
                            </a>

                            <a href="{{ route('dashboard.profile') }}" id="nav-profile"
                               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard.profile') ? 'bg-primary-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-100' }} transition-all">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                个人资料
                            </a>
                        </div>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="flex-1 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-3xl font-bold text-gray-900">我的预约</h2>
                        <a href="{{ route('activities.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 shadow-sm transition-all">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            浏览活动
                        </a>
                    </div>

            <!-- 统计卡片 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-primary-100 rounded-md flex items-center justify-center text-primary-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">总预约数</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $totalBookings }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center text-green-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">已确认</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $confirmedBookings }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center text-yellow-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">待确认</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $pendingBookings }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center text-red-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">已取消</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $cancelledBookings }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 预约列表 -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">预约记录</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">查看和管理您的所有活动预约</p>
                </div>

                @if($bookings->count() > 0)
                    <ul role="list" class="divide-y divide-gray-200">
                        @foreach($bookings as $booking)
                            <li class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center text-primary-600">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h6a2 2 0 012 2v4m-6 4v10m-4-4h8m0 0l4-4m-4 4l4 4"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="flex items-center">
                                                <h4 class="text-sm font-medium text-gray-900">{{ $booking->activity->title }}</h4>
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                                    @elseif($booking->status === 'attended') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    @if($booking->status === 'confirmed') 已确认
                                                    @elseif($booking->status === 'pending') 待确认
                                                    @elseif($booking->status === 'cancelled') 已取消
                                                    @elseif($booking->status === 'attended') 已出席
                                                    @else {{ $booking->status }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                <p>预约编号: <span class="font-mono">{{ $booking->booking_reference }}</span></p>
                                                <p>活动时间: {{ $booking->activity->start_date->format('Y-m-d H:i') }} - {{ $booking->activity->end_date->format('Y-m-d H:i') }}</p>
                                                @if($booking->contact_info && is_array($booking->contact_info))
                                                    <p>联系人: {{ $booking->contact_info['name'] ?? '未设置' }}</p>
                                                @endif
                                            </div>
                                            @if($booking->special_requirements)
                                                <div class="mt-2 text-sm text-gray-500">
                                                    <p><strong>特殊需求:</strong> {{ $booking->special_requirements }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-500">{{ $booking->created_at->format('Y-m-d H:i') }}</span>
                                        @if($booking->status === 'confirmed' || $booking->status === 'pending')
                                            <button onclick="cancelBooking({{ $booking->id }})"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                                                    id="cancel-btn-{{ $booking->id }}">
                                                取消预约
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">暂无预约记录</h3>
                        <p class="text-gray-500 mb-4">您还没有预约任何活动，快去浏览活动列表吧！</p>
                        <a href="{{ route('activities.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-blue-700">
                            浏览活动
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                © 2026 eBrook Group. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- 取消预约确认对话框 -->
    <div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 1000;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-5" id="modalTitle">确认取消预约</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="modalMessage">
                        您确定要取消这个预约吗？取消后无法恢复。
                    </p>
                </div>
                <div class="flex items-center px-4 py-3">
                    <button id="cancelConfirmBtn" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        确认取消
                    </button>
                    <button onclick="closeCancelModal()" class="ml-3 px-4 py-2 bg-gray-300 text-gray-900 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        取消
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let bookingToCancel = null;

        // 取消预约
        function cancelBooking(bookingId) {
            bookingToCancel = bookingId;
            document.getElementById('cancelModal').classList.remove('hidden');
        }

        // 关闭取消确认对话框
        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
            bookingToCancel = null;
        }

        // 确认取消预约
        document.getElementById('cancelConfirmBtn').addEventListener('click', async function() {
            if (!bookingToCancel) return;

            const button = this;
            const originalText = button.textContent;

            // 禁用按钮并显示加载状态
            button.disabled = true;
            button.textContent = '取消中...';

            try {
                const response = await fetch(`/dashboard/bookings/${bookingToCancel}/cancel`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    // 成功取消，刷新页面
                    location.reload();
                } else {
                    alert('取消失败：' + (result.error || '未知错误'));
                }
            } catch (error) {
                console.error('取消预约失败:', error);
                alert('网络错误，请稍后再试');
            } finally {
                button.disabled = false;
                button.textContent = originalText;
                closeCancelModal();
            }
        });

        // 点击对话框外部关闭
        document.getElementById('cancelModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCancelModal();
            }
        });
    </script>

                </div> <!-- End Main Content -->
            </div> <!-- End Flex Container -->
        </div> <!-- End White Card -->
    </main> <!-- End Main -->
</body>
</html>
