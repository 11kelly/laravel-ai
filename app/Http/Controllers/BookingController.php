<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\BookingServiceException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
    ) {
    }

    public function store(Request $request, Activity $activity): RedirectResponse
    {
        $requestId = $request->header('X-Request-Id');

        try {
            $this->bookingService->createBooking(
                user: Auth::user(),
                activityId: (int) $activity->id,
                idempotencyKey: $request->input('idempotencyKey') ?: null,
                requestId: $requestId,
            );

            return redirect()
                ->route('me.bookings.index')
                ->with('success', '预约成功！你可以在“我的预约”中管理。');
        } catch (BookingServiceException $e) {
            return back()->withErrors([
                'booking' => $this->mapBookingError($e->errorCode()),
            ])->withInput();
        }
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $requestId = $request->header('X-Request-Id');

        try {
            $this->bookingService->cancelBooking(
                actor: Auth::user(),
                bookingId: (int) $booking->id,
                reason: $validated['reason'] ?? null,
                requestId: $requestId,
            );

            return back()->with('success', '已取消预约，名额已释放。');
        } catch (BookingServiceException $e) {
            return back()->withErrors([
                'booking' => $this->mapBookingError($e->errorCode()),
            ])->withInput();
        }
    }

    private function mapBookingError(string $code): string
    {
        return match ($code) {
            'ACTIVITY_NOT_FOUND' => '活动不存在或不可见。',
            'ACTIVITY_NOT_BOOKABLE' => '该活动当前不可预约（未发布/已开始/已结束）。',
            'CAPACITY_EXHAUSTED' => '名额已满，请稍后再试。',
            'DUPLICATE_BOOKING' => '你已预约过该活动（不可重复预约）。',
            'BOOKING_NOT_FOUND' => '预约记录不存在。',
            'FORBIDDEN' => '无权限操作该预约。',
            'CANCEL_DEADLINE_PASSED' => '距离活动开始不足 1 小时或已开始，无法取消。',
            default => '操作失败，请稍后重试。',
        };
    }
}


