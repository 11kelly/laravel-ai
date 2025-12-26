@extends('layouts.app')

@section('title', $activity->title . ' - 活动预约系统')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-2">
        <div class="p-8 lg:p-12">
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li><a href="/" class="text-sm text-gray-500 hover:text-indigo-600">首页</a></li>
                    <li class="text-gray-400">/</li>
                    <li class="text-sm font-medium text-indigo-600">{{ $activity->title }}</li>
                </ol>
            </nav>

            <h1 class="text-4xl font-extrabold text-gray-900 mb-6">{{ $activity->title }}</h1>

            <div class="flex items-center space-x-6 mb-8 text-gray-600">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ $activity->start_at->format('Y-m-d H:i') }}</span>
                </div>
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>线上活动 / 线下地址</span>
                </div>
            </div>

            <div class="prose max-w-none text-gray-600 mb-12">
                {!! strip_tags($activity->description, '<b><i><u>
                            <p><br>
                            <ul>
                                <li>
                                    <ol>
                                        <h1>
                                            <h2>
                                                <h3>
                                                    <h4>
                                                        <h5>
                                                            <h6>') !!}
            </div>

            <div class="grid grid-cols-2 gap-4 mb-8">
                @foreach($activity->images ?? [] as $image)
                <img src="{{ str_starts_with($image, 'http') ? $image : Storage::url($image) }}" alt="Activity image" class="rounded-lg shadow-sm hover:opacity-90 transition-opacity">
                @endforeach
            </div>
        </div>

        <div class="bg-gray-50 p-8 lg:p-12 border-l border-gray-200">
            <div class="sticky top-8">
                <div class="bg-white rounded-xl p-8 shadow-sm border border-gray-200">
                    <h2 class="text-2xl font-bold mb-6">立即预约</h2>

                    @if($activity->remaining_spots > 0)
                    @auth
                    <form action="{{ route('activities.book', $activity) }}" method="POST" id="booking-form">
                        @csrf

                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">参与者姓名</label>
                                <input type="text" name="name" required value="{{ old('name', Auth::user()->name) }}" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-3 px-4 transition-all duration-200" placeholder="请输入姓名">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">联系电话</label>
                                <input type="text" name="phone" required value="{{ old('phone') }}" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-3 px-4 transition-all duration-200" placeholder="请输入联系电话">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">预约人数 (1-5)</label>
                                <select name="ticket_quantity" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-3 px-4 transition-all duration-200">
                                    @for($i=1; $i<=min(5, $activity->remaining_spots); $i++)
                                        <option value="{{ $i }}">{{ $i }} 人</option>
                                        @endfor
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 text-white py-4 px-6 rounded-lg font-bold text-lg hover:bg-indigo-700 hover:shadow-lg transform active:scale-[0.98] transition-all duration-200 mt-2">
                                确认预约
                            </button>
                        </div>
                    </form>
                    @else
                    <div class="text-center py-6">
                        <p class="text-gray-500 mb-4">请登录后进行预约。</p>
                        <a href="{{ route('login') }}" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-md font-medium hover:bg-indigo-700">登录 / 注册</a>
                    </div>
                    @endauth
                    @else
                    <div class="text-center py-6">
                        <div class="text-red-500 font-bold text-xl mb-2">名额已满</div>
                        <p class="text-gray-500">该活动已停止预约，欢迎关注其他活动。</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection