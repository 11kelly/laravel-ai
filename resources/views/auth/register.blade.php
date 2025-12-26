@extends('layouts.app')

@section('title', '注册')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">注册</h2>

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            @error('csrf')
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ $message }}</span>
                </div>
            @enderror

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    姓名
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    邮箱地址
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    密码
                </label>
                <input type="password" name="password" id="password" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">至少8个字符，需包含字母和数字</p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    确认密码
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                    注册
                </button>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    已有账号？立即登录
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Handle form submission and CSRF token errors
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const form = this;
        const originalSubmit = form.querySelector('button[type="submit"]');
        
        // Store original button text
        const originalText = originalSubmit.textContent;
        
        // Disable submit button to prevent double submission
        originalSubmit.disabled = true;
        originalSubmit.textContent = '注册中...';
        
        // If form submission fails due to CSRF token, the page will reload
        // and show the error message from the server
    });
    
    // Auto-refresh CSRF token if page has been open for a long time
    // Check every 30 minutes and refresh token if needed
    setInterval(function() {
        // Update the CSRF token in the form
        const csrfInput = document.querySelector('input[name="_token"]');
        if (csrfInput) {
            // Get fresh token from meta tag
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                csrfInput.value = metaToken.getAttribute('content');
            }
        }
    }, 30 * 60 * 1000); // 30 minutes
</script>
@endsection

