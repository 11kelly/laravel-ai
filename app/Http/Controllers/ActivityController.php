<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\UniqueConstraintViolationException;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::published()
            ->where('end_at', '>', now())
            ->orderBy('start_at')
            ->get();

        return view('activities.index', compact('activities'));
    }

    public function show(Activity $activity)
    {
        if ($activity->status !== 'published') {
            abort(404);
        }

        return view('activities.show', compact('activity'));
    }

    public function book(Request $request, Activity $activity, BookingService $bookingService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'ticket_quantity' => 'required|integer|min:1|max:5',
        ]);

        // 1. 检查是否已预约
        $exists = Booking::where('user_id', Auth::id())
            ->where('activity_id', $activity->id)
            ->exists();

        if ($exists) {
            return back()->with('error', '您已经预约过此活动了。');
        }

        try {
            $booking = $bookingService->createBooking(Auth::user(), $activity, [
                'ticket_quantity' => (int) $request->input('ticket_quantity'),
                'participant_info' => [
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                ],
            ]);

            return redirect()->route('bookings.index')->with('success', "预约成功！预约码：{$booking->booking_code}");
        } catch (UniqueConstraintViolationException $e) {
            return back()->with('error', '您已经预约过此活动了，请勿重复操作。');
        } catch (\Exception $e) {
            if ($e->getMessage() === 'RESOURCE_EXHAUSTED') {
                return back()->with('error', '名额已满，预约失败。');
            }
            return back()->with('error', '系统繁忙，请稍后再试。');
        }
    }

    public function myBookings()
    {
        $bookings = Auth::user()->bookings()
            ->with('activity')
            ->orderByDesc('created_at')
            ->get();

        return view('bookings.index', compact('bookings'));
    }

    public function cancelBooking(Booking $booking, BookingService $bookingService)
    {
        // 鉴权：只能取消自己的预约
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $bookingService->cancelBooking($booking);
            return back()->with('success', '预约已成功取消。');
        } catch (\Exception $e) {
            return back()->with('error', '取消失败：' . $e->getMessage());
        }
    }
}

