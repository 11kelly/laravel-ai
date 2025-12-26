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
    <script>
        // 在页面加载完成后设置表单验证
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('profileForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // 清理所有输入字段，移除控制字符
                    const inputs = form.querySelectorAll('input[type="text"], textarea');
                    inputs.forEach(function(input) {
                        if (input.value) {
                            // 移除控制字符 (0x00-0x1F 和 0x7F-0x9F)
                            input.value = input.value.replace(/[\x00-\x1F\x7F-\x9F]/g, '');
                        }
                    });
                });
            }
        });
    </script>
</head>
<body class="bg-gray-100 font-sans antialiased">
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
                    <div class="max-w-4xl mx-auto">
                <div class="p-6">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('dashboard.profile.update') }}" method="POST" class="space-y-6">
                        @csrf


                        <!-- 联系信息 -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">预约联系信息</h3>
                            <p class="text-sm text-gray-600 mb-4">这些信息将在您预约活动时自动使用</p>

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        联系人姓名
                                        <span class="text-red-500 ml-1">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="contact_name" id="contact_name"
                                               value="{{ old('contact_name', $user->contact_info['name'] ?? '') }}"
                                               class="block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm
                                                      focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none
                                                      transition-colors duration-200 ease-in-out
                                                      placeholder-gray-400 text-gray-900 text-sm"
                                               placeholder="请输入联系人姓名"
                                               required>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @error('contact_name')
                                        <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        联系邮箱
                                    </label>
                                    <div class="relative">
                                        <input type="email" name="contact_email" id="contact_email"
                                               value="{{ $user->email }}"
                                               class="block w-full px-3 py-2.5 border border-gray-300 rounded-md
                                                      bg-gray-50 text-gray-500 shadow-sm cursor-not-allowed text-sm"
                                               readonly>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="mt-1.5 text-xs text-gray-500 flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        使用您的登录邮箱地址
                                    </p>
                                </div>

                                <div>
                                    <label for="contact_address" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        联系地址
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="contact_address" id="contact_address"
                                               value="{{ old('contact_address', $user->contact_info['address'] ?? '') }}"
                                               class="block w-full px-3 py-2.5 pl-10 border border-gray-300 rounded-md shadow-sm
                                                      focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none
                                                      transition-colors duration-200 ease-in-out
                                                      placeholder-gray-400 text-gray-900 text-sm"
                                               placeholder="请输入联系地址">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-2.5">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @error('contact_address')
                                        <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="emergency_contact" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        紧急联系人
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="emergency_contact" id="emergency_contact"
                                               value="{{ old('emergency_contact', $user->contact_info['emergency_contact'] ?? '') }}"
                                               class="block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm
                                                      focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none
                                                      transition-colors duration-200 ease-in-out
                                                      placeholder-gray-400 text-gray-900 text-sm"
                                               placeholder="例如：张三 138-0000-0000">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">可选：紧急联系人姓名和电话</p>
                                    @error('emergency_contact')
                                        <p class="mt-1.5 text-xs text-red-600 flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 提交按钮 -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <a href="{{ route('dashboard.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-md
                                      hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-500
                                      transition-colors duration-200 ease-in-out text-sm font-medium">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                返回
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-md
                                           hover:bg-primary-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
                                           transition-all duration-200 ease-in-out text-sm font-medium active:scale-95">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                保存资料
                            </button>
                        </div>
                    </form>

                    <!-- 资料完整性提示 -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center">
                            @if($user->hasCompleteProfile())
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">个人资料已完整</p>
                                    <p class="text-xs text-gray-600 mt-0.5">可以正常预约活动</p>
                                </div>
                            @else
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">个人资料不完整</p>
                                    <p class="text-xs text-gray-600 mt-0.5">请完善联系人姓名以便预约</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                </div> <!-- End Main Content -->
            </div> <!-- End Flex Container -->
        </div> <!-- End White Card -->
    </main> <!-- End Main -->
</body>
</html>
