<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
?>

@extends('layouts.app', ['title' => $activity->title])

@section('content')
    <div class="grid gap-6 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div class="min-w-0">
                        <h1 class="truncate text-2xl font-semibold tracking-tight">{{ $activity->title }}</h1>
                        @if ($activity->summary)
                            <p class="mt-2 text-sm text-slate-300">{{ $activity->summary }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 rounded-xl bg-amber-400/15 px-3 py-2 text-xs text-amber-200 ring-1 ring-amber-300/20">
                        剩余名额：{{ $activity->remainingCapacity() }}
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-white/10 bg-slate-950/30 p-4">
                        <div class="text-xs text-slate-400">开始时间</div>
                        <div class="mt-1 text-sm text-slate-100">{{ optional($activity->starts_at)->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-slate-950/30 p-4">
                        <div class="text-xs text-slate-400">结束时间</div>
                        <div class="mt-1 text-sm text-slate-100">
                            {{ $activity->ends_at ? $activity->ends_at->format('Y-m-d H:i') : '待定' }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-slate-950/30 p-4">
                        <div class="text-xs text-slate-400">时区</div>
                        <div class="mt-1 text-sm text-slate-100">{{ $activity->timezone }}</div>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-slate-950/30 p-4">
                        <div class="text-xs text-slate-400">地点</div>
                        <div class="mt-1 text-sm text-slate-100">{{ $activity->location ?: '待定' }}</div>
                    </div>
                </div>

                @if ($activity->images->count() > 0)
                    <div class="mt-6">
                        <div class="text-sm font-medium text-slate-200">活动图片</div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach ($activity->images as $image)
                                <img src="{{ $image->url() }}"
                                     alt="activity image"
                                     class="h-48 w-full rounded-xl object-cover ring-1 ring-white/10"/>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($activity->description)
                    <div class="mt-6">
                        <div class="text-sm font-medium text-slate-200">详情</div>
                        <div class="prose prose-invert mt-3 max-w-none text-slate-200">
                            {!! nl2br(e($activity->description)) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-4">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                <div class="text-lg font-semibold">预约</div>
                <p class="mt-1 text-sm text-slate-300">
                    预约会占用名额；取消规则：距离活动开始不足 1 小时或活动已开始时不可取消。
                </p>

                <div class="mt-5 space-y-3">
                    @auth
                        @php($disabledReason = $activity->bookingDisabledReason())
                        <form method="POST" action="{{ route('activities.bookings.store', $activity) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-xl bg-amber-400/20 px-4 py-3 font-medium text-amber-100 ring-1 ring-amber-300/25 hover:bg-amber-400/25 disabled:opacity-50"
                                    @disabled($disabledReason !== null)>
                                {{ $disabledReason === null ? '立即预约' : $disabledReason }}
                            </button>
                        </form>
                        @if ($disabledReason !== null)
                            <div class="rounded-xl border border-white/10 bg-slate-950/30 p-4 text-sm text-slate-300">
                                当前不可预约：{{ $disabledReason }}。
                            </div>
                        @endif
                        <a href="{{ route('me.bookings.index') }}"
                           class="block w-full rounded-xl border border-white/10 px-4 py-3 text-center text-sm text-slate-100 hover:bg-white/5">
                            去我的预约查看
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="block w-full rounded-xl bg-amber-400/20 px-4 py-3 text-center font-medium text-amber-100 ring-1 ring-amber-300/25 hover:bg-amber-400/25">
                            登录后预约
                        </a>
                        <a href="{{ route('register') }}"
                           class="block w-full rounded-xl border border-white/10 px-4 py-3 text-center text-sm text-slate-100 hover:bg-white/5">
                            没有账号？去注册
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection


