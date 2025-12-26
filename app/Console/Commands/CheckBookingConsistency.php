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

class CheckBookingConsistency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:check-consistency
                            {--fix : Automatically fix inconsistencies}
                            {--dry-run : Show what would be fixed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and optionally fix booking count consistency between events and bookings tables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking booking consistency...');
        $this->newLine();

        $inconsistencies = $this->findInconsistencies();

        if ($inconsistencies->isEmpty()) {
            $this->info('✓ All booking counts are consistent.');
            return Command::SUCCESS;
        }

        $this->warn("Found {$inconsistencies->count()} inconsistencies:");
        $this->newLine();

        $headers = ['Event ID', 'Title', 'Recorded Count', 'Actual Count', 'Difference'];
        $rows = $inconsistencies->map(function ($item) {
            return [
                $item->id,
                mb_substr($item->title, 0, 40),
                $item->recorded_count,
                $item->actual_count,
                $item->actual_count - $item->recorded_count,
            ];
        })->toArray();

        $this->table($headers, $rows);

        if ($this->option('fix') && !$this->option('dry-run')) {
            $this->fixInconsistencies($inconsistencies);
            $this->info('✓ All inconsistencies have been fixed.');

            Log::warning('Booking consistency fix applied', [
                'fixed_count' => $inconsistencies->count(),
                'events' => $inconsistencies->pluck('id')->toArray(),
            ]);

            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info('Dry run mode: No changes were made.');
            $this->info('Run with --fix to apply corrections.');
        }

        return Command::FAILURE;
    }

    /**
     * Find events with inconsistent booked_count.
     */
    protected function findInconsistencies()
    {
        return DB::table('events as e')
            ->leftJoin('bookings as b', function ($join) {
                $join->on('e.id', '=', 'b.event_id')
                    ->where('b.status', '=', 'confirmed');
            })
            ->select(
                'e.id',
                'e.title',
                'e.booked_count as recorded_count',
                DB::raw('COALESCE(SUM(b.participants_count), 0) as actual_count')
            )
            ->whereNull('e.deleted_at')
            ->groupBy('e.id', 'e.title', 'e.booked_count')
            ->havingRaw('e.booked_count != COALESCE(SUM(b.participants_count), 0)')
            ->get();
    }

    /**
     * Fix the inconsistencies by updating booked_count.
     */
    protected function fixInconsistencies($inconsistencies): void
    {
        DB::transaction(function () use ($inconsistencies) {
            foreach ($inconsistencies as $item) {
                Event::withoutTimestamps(function () use ($item) {
                    Event::where('id', $item->id)
                        ->update(['booked_count' => $item->actual_count]);
                });

                $this->line("Fixed event #{$item->id}: {$item->recorded_count} → {$item->actual_count}");
            }
        });
    }
}

