<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CreateBookingRequest;
use App\Models\Booking;
use App\Models\Event;
use App\Services\BookingCancellationService;
use App\Services\FrontendBookingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BookingController extends Controller
{
    public function __construct(
        private readonly FrontendBookingService $booking_service,
        private readonly BookingCancellationService $cancellation_service
    ) {}

    /**
     * Display user's bookings
     */
    public function index(): View
    {
        $bookings = auth()->user()
            ->bookings()
            ->with(['event.category', 'event.images'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('frontend.bookings.index', compact('bookings'));
    }
    
    /**
     * Store a new booking
     */
    public function store(CreateBookingRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();
        
        try {
            $this->booking_service->createBooking(
                $event,
                auth()->id(),
                $validated['participants_count'],
                $validated['notes']
            );
            
            return redirect()
                ->route('frontend.bookings.index')
                ->with('success', '预约申请已提交，等待管理员审批');
                
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Cancel a booking
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
        
        if (!$booking->canBeCancelled()) {
            return back()->with('error', '此预约无法取消');
        }
        
        try {
            $this->cancellation_service->cancelBooking($booking, auth()->user());
            
            return back()->with('success', '预约已取消');
            
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

