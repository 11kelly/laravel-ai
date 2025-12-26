<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {
    }

    /**
     * @throws BookingServiceException
     */
    public function createBooking(
        User $user,
        int $activityId,
        ?string $idempotencyKey = null,
        ?string $requestId = null,
    ): Booking {
        $booking = $this->createBookingCore($user, $activityId, $idempotencyKey);

        $this->auditService->record(
            eventType: 'booking_created',
            actor: $user,
            actorType: 'user',
            targetType: 'booking',
            targetId: (int) $booking->id,
            requestId: $requestId,
            metadata: [
                'activity_id' => (int) $booking->activity_id,
            ],
        );

        return $booking;
    }

    /**
     * @throws BookingServiceException
     */
    public function createBookingOnBehalf(
        Authenticatable $actor,
        User $user,
        int $activityId,
        ?string $idempotencyKey = null,
        ?string $reason = null,
        ?string $requestId = null,
    ): Booking {
        $booking = $this->createBookingCore($user, $activityId, $idempotencyKey);

        $this->auditService->record(
            eventType: 'admin_booking_created_on_behalf',
            actor: $actor,
            actorType: 'admin',
            targetType: 'booking',
            targetId: (int) $booking->id,
            requestId: $requestId,
            metadata: [
                'activity_id' => (int) $booking->activity_id,
                'impersonated_user_id' => (int) $user->id,
                'reason' => $reason,
            ],
        );

        return $booking;
    }

    /**
     * @throws BookingServiceException
     */
    private function createBookingCore(
        User $user,
        int $activityId,
        ?string $idempotencyKey,
    ): Booking {
        return DB::transaction(function () use ($user, $activityId, $idempotencyKey) {
            $activity = Activity::query()
                ->whereKey($activityId)
                ->lockForUpdate()
                ->first();

            if (! $activity) {
                throw new BookingServiceException('ACTIVITY_NOT_FOUND');
            }

            $this->assertActivityBookable($activity);

            $existing = Booking::query()
                ->where('activity_id', '=', $activity->id)
                ->where('user_id', '=', $user->id)
                ->first();

            if ($existing) {
                // 重试幂等：如果客户端重复提交（或同 key 重放），直接返回已有记录
                if ($idempotencyKey !== null && $existing->idempotency_key === $idempotencyKey) {
                    return $existing;
                }

                throw new BookingServiceException('DUPLICATE_BOOKING');
            }

            if ((int) $activity->booked_count >= (int) $activity->capacity) {
                throw new BookingServiceException('CAPACITY_EXHAUSTED');
            }

            try {
                $booking = Booking::query()->create([
                    'activity_id' => $activity->id,
                    'user_id' => $user->id,
                    'status' => 'active',
                    'booked_at' => now(),
                    'idempotency_key' => $idempotencyKey,
                    'activity_snapshot' => [
                        'title' => $activity->title,
                        'starts_at' => $activity->starts_at?->toISOString(),
                        'ends_at' => $activity->ends_at?->toISOString(),
                        'timezone' => $activity->timezone,
                        'location' => $activity->location,
                    ],
                ]);
            } catch (QueryException $e) {
                // 兜底：并发重复提交触发唯一约束
                if ($this->isUniqueConstraintViolation($e)) {
                    throw new BookingServiceException('DUPLICATE_BOOKING');
                }

                throw $e;
            }

            $activity->increment('booked_count');

            return $booking;
        }, 3);
    }

    /**
     * @throws BookingServiceException
     */
    public function cancelBooking(
        Authenticatable $actor,
        int $bookingId,
        ?string $reason = null,
        ?string $requestId = null,
        ?User $impersonatedUser = null,
    ): Booking {
        return DB::transaction(function () use ($actor, $bookingId, $reason, $requestId, $impersonatedUser) {
            $booking = Booking::query()
                ->whereKey($bookingId)
                ->lockForUpdate()
                ->first();

            if (! $booking) {
                throw new BookingServiceException('BOOKING_NOT_FOUND');
            }

            $ownerId = $impersonatedUser?->id ?? (int) $actor->getAuthIdentifier();
            if ((int) $booking->user_id !== (int) $ownerId) {
                throw new BookingServiceException('FORBIDDEN');
            }

            if ($booking->status === 'cancelled') {
                return $booking;
            }

            $activity = Activity::query()
                ->whereKey($booking->activity_id)
                ->lockForUpdate()
                ->first();

            if (! $activity) {
                throw new BookingServiceException('ACTIVITY_NOT_FOUND');
            }

            $this->assertCancelable($activity->starts_at, now());

            $booking->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ])->save();

            $activity->booked_count = max(0, (int) $activity->booked_count - 1);
            $activity->save();

            $actorType = $impersonatedUser ? 'admin' : 'user';
            $eventType = $impersonatedUser ? 'admin_booking_cancelled_on_behalf' : 'booking_cancelled';

            $metadata = [
                'activity_id' => (int) $activity->id,
            ];

            if ($impersonatedUser) {
                $metadata['impersonated_user_id'] = (int) $impersonatedUser->id;
                $metadata['reason'] = $reason;
            }

            $this->auditService->record(
                eventType: $eventType,
                actor: $actor,
                actorType: $actorType,
                targetType: 'booking',
                targetId: (int) $booking->id,
                requestId: $requestId,
                metadata: $metadata,
            );

            return $booking;
        }, 3);
    }

    /**
     * @throws BookingServiceException
     */
    private function assertActivityBookable(Activity $activity): void
    {
        if ($activity->status !== 'published') {
            throw new BookingServiceException('ACTIVITY_NOT_BOOKABLE');
        }

        if (! $activity->published_at) {
            throw new BookingServiceException('ACTIVITY_NOT_BOOKABLE');
        }

        if (now()->lessThan($activity->published_at)) {
            throw new BookingServiceException('ACTIVITY_NOT_BOOKABLE');
        }

        $startsAt = $activity->starts_at instanceof Carbon ? $activity->starts_at : null;
        if (! $startsAt) {
            throw new BookingServiceException('ACTIVITY_NOT_BOOKABLE');
        }

        if (now()->greaterThanOrEqualTo($startsAt)) {
            throw new BookingServiceException('ACTIVITY_NOT_BOOKABLE');
        }
    }

    /**
     * @throws BookingServiceException
     */
    private function assertCancelable(?Carbon $startsAt, Carbon $now): void
    {
        if (! $startsAt) {
            throw new BookingServiceException('CANCEL_DEADLINE_PASSED');
        }

        if ($now->greaterThanOrEqualTo($startsAt)) {
            throw new BookingServiceException('CANCEL_DEADLINE_PASSED');
        }

        if ($now->diffInMinutes($startsAt, false) < 60) {
            throw new BookingServiceException('CANCEL_DEADLINE_PASSED');
        }
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $driverError = $e->errorInfo[1] ?? null;

        // MySQL: SQLSTATE 23000, error 1062
        if ($sqlState === '23000' && $driverError === 1062) {
            return true;
        }

        // SQLite: SQLSTATE 23000, error 19
        if ($sqlState === '23000' && $driverError === 19) {
            return true;
        }

        return false;
    }
}


