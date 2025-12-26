<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Display a listing of events
     * 
     * 显示已发布且未结束的活动（包括进行中的活动）
     */
    public function index(Request $request): View
    {
        try {
            $query = Event::with(['category', 'images'])
                ->where('status', EventStatus::PUBLISHED)
                // 显示未结束的活动：end_time >= 当前时间
                ->where('end_time', '>=', now());
            
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $events = $query->orderBy('start_time', 'asc')->paginate(12);
            $categories = EventCategory::orderBy('name')->get();
            
            return view('frontend.events.index', compact('events', 'categories'));
        } catch (\Throwable $e) {
            Log::error('EventController@index error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // 返回空结果而不是抛出异常
            try {
                $events = Event::query()->whereRaw('1 = 0')->paginate(12);
                $categories = collect();
                
                return view('frontend.events.index', compact('events', 'categories'))->withErrors([
                    'error' => '加载活动列表时发生错误，请稍后重试。',
                ]);
            } catch (\Throwable $viewError) {
                // 如果连视图都无法渲染，返回简单的错误响应
                Log::error('EventController@index view error', [
                    'message' => $viewError->getMessage(),
                ]);
                
                abort(500, '系统错误，请稍后重试');
            }
        }
    }
    
    /**
     * Display the specified event
     */
    public function show(Event $event): View
    {
        if ($event->status !== EventStatus::PUBLISHED) {
            abort(404);
        }
        
        $event->load(['category', 'images', 'creator']);
        
        $has_booking = false;
        $user_booking = null;
        
        if (auth()->check()) {
            $user_booking = $event->bookings()
                ->where('user_id', auth()->id())
                ->whereIn('status', BookingStatus::activeStatuses())
                ->first();
            $has_booking = $user_booking !== null;
        }
        
        return view('frontend.events.show', compact('event', 'has_booking', 'user_booking'));
    }
}

