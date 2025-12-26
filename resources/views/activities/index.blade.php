<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
?>

@extends('layouts.app', ['title' => '活动'])

@section('content')
    <div class="flex flex-col gap-6">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">活动列表</h1>
                    <p class="mt-1 text-sm text-slate-300">仅展示已发布、已到发布时间且未开始的活动。</p>
                </div>
                <div class="text-xs text-slate-400">
                    @auth
                        你好，{{ auth()->user()->name }}
                    @else
                        登录后可预约并在个人中心取消
                    @endauth
                </div>
            </div>

            <form method="GET" action="{{ route('activities.index') }}" class="mt-5 grid gap-3 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label class="text-xs text-slate-300">关键词</label>
                    <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="标题 / 摘要"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-300/40 focus:outline-none focus:ring-2 focus:ring-amber-300/20"/>
                </div>
                <div>
                    <label class="text-xs text-slate-300">开始时间（从）</label>
                    <input name="from" value="{{ $filters['from'] ?? '' }}" type="datetime-local"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 focus:border-amber-300/40 focus:outline-none focus:ring-2 focus:ring-amber-300/20"/>
                </div>
                <div>
                    <label class="text-xs text-slate-300">开始时间（到）</label>
                    <input name="to" value="{{ $filters['to'] ?? '' }}" type="datetime-local"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 focus:border-amber-300/40 focus:outline-none focus:ring-2 focus:ring-amber-300/20"/>
                </div>
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit"
                            class="rounded-xl bg-white/10 px-4 py-3 text-sm font-medium hover:bg-white/15">
                        筛选
                    </button>
                    <a href="{{ route('activities.index') }}"
                       class="rounded-xl border border-white/10 px-4 py-3 text-sm hover:bg-white/5">
                        重置
                    </a>
                </div>
            </form>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($activities as $activity)
                <a href="{{ route('activities.show', $activity) }}"
                   class="group rounded-2xl border border-white/10 bg-white/5 p-5 transition hover:bg-white/7 hover:ring-1 hover:ring-amber-300/20">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="truncate text-lg font-semibold">{{ $activity->title }}</div>
                            @if ($activity->summary)
                                <div class="mt-1 line-clamp-2 text-sm text-slate-300">{{ $activity->summary }}</div>
                            @endif
                        </div>
                        <span class="shrink-0 rounded-xl bg-amber-400/15 px-3 py-1 text-xs text-amber-200 ring-1 ring-amber-300/20">
                            剩余 {{ $activity->remainingCapacity() }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-2 text-sm text-slate-300">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">开始</span>
                            <span>{{ optional($activity->starts_at)->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">地点</span>
                            <span class="truncate">{{ $activity->location ?: '待定' }}</span>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between text-xs text-slate-400">
                        <span class="group-hover:text-slate-300">查看详情</span>
                        <span class="transition group-hover:translate-x-0.5">→</span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 text-slate-300 md:col-span-2 lg:col-span-3">
                    暂无符合条件的活动。
                </div>
            @endforelse
        </div>

        <div>
            {{ $activities->links() }}
        </div>
    </div>
@endsection


