<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_id' => 'required|exists:rooms,id',
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:1',
            'meeting_type' => 'required|in:one-time,recurring',
            'priority_guest_id' => 'nullable|exists:priority_guests,id',
            'participants' => 'nullable|array',
            'participants.*.user_id' => 'nullable|exists:users,id',
            'participants.*.external_participant_id' => 'nullable|exists:external_participants,id',
            'pantry_orders' => 'nullable|array',
            'pantry_orders.*.pantry_item_id' => 'required|exists:pantry_items,id',
            'pantry_orders.*.quantity' => 'required|integer|min:1',
            'recurring_pattern' => 'required_if:meeting_type,recurring|in:daily,weekly,monthly',
            'recurring_ends_at' => 'required_if:meeting_type,recurring|date|after:start_time',
        ];
    }
}
