@extends('layouts.app')

@section('title', '我的预约 - 活动预约系统')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">我的预约</h1>
    <p class="mt-2 text-gray-600">您可以在此查看所有已预约的活动及其状态。</p>
</div>

<div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">活动名称</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">预约码</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">人数</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($bookings as $booking)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $booking->activity->title }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->activity->start_at->format('Y-m-d H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($booking->status === 'confirmed') bg-blue-100 text-blue-800 
                            @elseif($booking->status === 'attended') bg-green-100 text-green-800 
                            @elseif($booking->status === 'cancelled') bg-red-100 text-red-800 
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ match($booking->status) {
                                'pending' => '待确认',
                                'confirmed' => '已确认',
                                'attended' => '已签到',
                                'cancelled' => '已取消',
                                default => '未知'
                            } }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                        {{ $booking->booking_code }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $booking->ticket_quantity }} 人
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                        <a href="{{ route('activities.show', $booking->activity_id) }}" class="text-indigo-600 hover:text-indigo-900">详情</a>
                        
                        @if(in_array($booking->status, ['pending', 'confirmed']))
                            <form action="{{ route('bookings.cancel', $booking) }}" method="POST" class="inline" onsubmit="return confirm('确定要取消这次预约吗？');">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-900">取消</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        您还没有任何预约。
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

