<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
?>

@extends('layouts.app', ['title' => '我的预约'])

@section('content')
    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">我的预约</h1>
                <p class="mt-1 text-sm text-slate-300">你可以在活动开始前 >= 1 小时取消预约。</p>
            </div>
            <a href="{{ route('activities.index') }}"
               class="rounded-xl border border-white/10 px-4 py-2 text-sm hover:bg-white/5">去看活动</a>
        </div>

        <div class="mt-6 space-y-3">
            @forelse ($bookings as $booking)
                <div class="rounded-2xl border border-white/10 bg-slate-950/30 p-5">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0">
                            <div class="truncate text-lg font-semibold">
                                {{ $booking->activity?->title ?? '活动已不可用' }}
                            </div>
                            <div class="mt-1 text-sm text-slate-300">
                                预约时间：{{ optional($booking->booked_at)->format('Y-m-d H:i') }}
                            </div>
                            @if ($booking->activity?->starts_at)
                                <div class="mt-1 text-sm text-slate-300">
                                    开始时间：{{ $booking->activity->starts_at->format('Y-m-d H:i') }}
                                </div>
                            @endif
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            @if ($booking->status === 'active')
                                <span class="rounded-xl bg-emerald-400/15 px-3 py-1 text-xs text-emerald-200 ring-1 ring-emerald-300/25">
                                    已预约
                                </span>
                            @else
                                <span class="rounded-xl bg-slate-400/10 px-3 py-1 text-xs text-slate-200 ring-1 ring-white/10">
                                    已取消
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xs text-slate-400">
                            @if ($booking->status === 'cancelled' && $booking->cancelled_at)
                                取消时间：{{ $booking->cancelled_at->format('Y-m-d H:i') }}
                            @endif
                        </div>

                        <div class="flex gap-2">
                            @if ($booking->activity)
                                <a href="{{ route('activities.show', $booking->activity) }}"
                                   class="rounded-xl border border-white/10 px-4 py-2 text-sm hover:bg-white/5">
                                    查看活动
                                </a>
                            @endif

                            @if ($booking->status === 'active')
                                <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="flex gap-2">
                                    @csrf
                                    <input type="hidden" name="reason" value="用户主动取消"/>
                                    <button type="submit"
                                            data-confirm="确认取消该预约？（活动开始前 1 小时内不可取消）"
                                            class="rounded-xl bg-rose-400/15 px-4 py-2 text-sm text-rose-100 ring-1 ring-rose-300/25 hover:bg-rose-400/20">
                                        取消预约
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-white/10 bg-slate-950/30 p-6 text-slate-300">
                    你还没有任何预约。
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    </div>
@endsection


