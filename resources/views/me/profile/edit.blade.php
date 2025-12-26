<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
?>

@extends('layouts.app', ['title' => '个人资料'])

@section('content')
    <div class="mx-auto max-w-xl rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
        <h1 class="text-2xl font-semibold tracking-tight">个人资料</h1>
        <p class="mt-1 text-sm text-slate-300">本期仅支持更新昵称。</p>

        <form method="POST" action="{{ route('me.profile.update') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="text-sm text-slate-200">邮箱</label>
                <input disabled value="{{ $user->email }}"
                       class="mt-2 w-full cursor-not-allowed rounded-xl border border-white/10 bg-slate-950/30 px-4 py-3 text-sm text-slate-300"/>
            </div>

            <div>
                <label class="text-sm text-slate-200">昵称</label>
                <input name="name" value="{{ old('name', $user->name) }}" required
                       class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-300/40 focus:outline-none focus:ring-2 focus:ring-amber-300/20"/>
            </div>

            <button type="submit"
                    class="rounded-xl bg-amber-400/20 px-5 py-3 text-sm font-medium text-amber-100 ring-1 ring-amber-300/25 hover:bg-amber-400/25">
                保存
            </button>
        </form>
    </div>
@endsection


