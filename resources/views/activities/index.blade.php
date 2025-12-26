@extends('layouts.app')

@section('title', '活动列表 - 活动预约系统')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">近期活动</h1>
    <p class="mt-2 text-gray-600">选择您感兴趣的活动进行预约。</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @forelse($activities as $activity)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
            <div class="h-48 bg-gray-200 overflow-hidden">
                @if($activity->images && count($activity->images) > 0)
                    @php $firstImage = $activity->images[0]; @endphp
                    <img src="{{ str_starts_with($firstImage, 'http') ? $firstImage : Storage::url($firstImage) }}" alt="{{ $activity->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                @endif
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activity->remaining_spots > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $activity->remaining_spots > 0 ? "剩余 {$activity->remaining_spots} 名额" : '名额已满' }}
                    </span>
                    <span class="text-xs text-gray-500">{{ $activity->start_at->format('Y-m-d') }}</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $activity->title }}</h3>
                <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                    {{ Str::limit(strip_tags($activity->description), 100) }}
                </p>
                <a href="{{ route('activities.show', $activity) }}" class="block w-full text-center px-4 py-3 border border-transparent rounded-lg shadow-sm text-base font-bold text-white bg-indigo-600 hover:bg-indigo-700 hover:shadow-md transform active:scale-[0.98] transition-all duration-200">
                    查看详情
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500">暂无活动。</p>
        </div>
    @endforelse
</div>
@endsection

