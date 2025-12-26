<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user is banned
        return !$this->user()->is_banned;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'participants_count' => ['required', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'Please select an event to book.',
            'event_id.exists' => 'The selected event does not exist.',
            'participants_count.required' => 'Please specify the number of participants.',
            'participants_count.min' => 'At least 1 participant is required.',
            'participants_count.max' => 'Maximum 10 participants allowed per booking.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $event = Event::find($this->event_id);

            if (!$event) {
                return;
            }

            // Check if event is bookable
            if (!$event->isBookable()) {
                if ($event->status !== 'published') {
                    $validator->errors()->add('event_id', 'This event is not available for booking.');
                } elseif ($event->start_time->isPast()) {
                    $validator->errors()->add('event_id', 'This event has already started.');
                } elseif ($event->booking_deadline && $event->booking_deadline->isPast()) {
                    $validator->errors()->add('event_id', 'Booking deadline has passed.');
                } elseif ($event->booked_count >= $event->capacity) {
                    $validator->errors()->add('event_id', 'This event is fully booked.');
                }
            }

            // Check remaining capacity
            $remaining = $event->getRemainingCapacity();
            if ($this->participants_count > $remaining) {
                $validator->errors()->add(
                    'participants_count',
                    "Only {$remaining} spots remaining for this event."
                );
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 400));
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Your account has been banned. You cannot create bookings.',
        ], 403));
    }
}

