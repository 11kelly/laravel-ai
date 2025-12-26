<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompletePassedEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:complete-passed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark passed events and their bookings as completed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing passed events...');

        // Find published events that have ended
        $passedEvents = Event::where('status', 'published')
            ->where('end_time', '<', now())
            ->get();

        if ($passedEvents->isEmpty()) {
            $this->info('No events to process.');
            return Command::SUCCESS;
        }

        $eventsCompleted = 0;
        $bookingsCompleted = 0;

        DB::transaction(function () use ($passedEvents, &$eventsCompleted, &$bookingsCompleted) {
            foreach ($passedEvents as $event) {
                // Update event status
                $event->update(['status' => 'completed']);
                $eventsCompleted++;

                // Update confirmed bookings to completed
                $updatedBookings = Booking::where('event_id', $event->id)
                    ->where('status', 'confirmed')
                    ->update(['status' => 'completed']);

                $bookingsCompleted += $updatedBookings;

                $this->line("Completed event: {$event->title} ({$updatedBookings} bookings)");
            }
        });

        $this->newLine();
        $this->info("✓ Completed {$eventsCompleted} events and {$bookingsCompleted} bookings.");

        Log::info('Completed passed events', [
            'events_count' => $eventsCompleted,
            'bookings_count' => $bookingsCompleted,
        ]);

        return Command::SUCCESS;
    }
}

