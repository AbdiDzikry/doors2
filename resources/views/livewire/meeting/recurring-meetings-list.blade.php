<div>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">My Recurring Meetings</h1>

        @if ($recurringMeetings->isEmpty())
            <p class="text-gray-600">You have no recurring meetings.</p>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <ul class="divide-y divide-gray-200">
                    @foreach ($recurringMeetings as $parentMeeting)
                        <li class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800">{{ $parentMeeting->topic }}</h2>
                                    <p class="text-sm text-gray-600">
                                        {{ $parentMeeting->recurring_type === 'weekly' ? 'Weekly' : 'Monthly' }} on {{ $parentMeeting->start_time->format('l') }}
                                        at {{ $parentMeeting->start_time->format('H:i') }}
                                        in {{ $parentMeeting->room->name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        From {{ $parentMeeting->start_time->format('d M Y') }} to {{ $parentMeeting->recurring_end_date->format('d M Y') }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">Upcoming Meetings</h3>
                                <ul class="space-y-3">
                                    @foreach ($parentMeeting->children as $childMeeting)
                                        <li class="flex flex-col sm:flex-row sm:items-center justify-between bg-gray-50 p-3 rounded-md gap-4 sm:gap-0">
                                            <div>
                                                <p class="font-medium text-gray-700">{{ $childMeeting->start_time->format('d M Y, H:i') }} - {{ $childMeeting->end_time->format('H:i') }}</p>
                                                <p class="text-sm">
                                                    @if ($childMeeting->status === 'cancelled')
                                                        <span class="text-red-600">Cancelled</span>
                                                    @elseif($childMeeting->confirmation_status === 'confirmed')
                                                        <span class="text-green-600">Confirmed</span>
                                                    @else
                                                        <span class="text-yellow-600">Pending Confirmation</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex items-center space-x-2 self-end sm:self-auto">
                                                @if ($childMeeting->status !== 'cancelled' && $childMeeting->confirmation_status === 'pending_confirmation')
                                                    <button wire:click="confirmMeeting({{ $childMeeting->id }})" class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-md hover:bg-green-600">Confirm</button>
                                                    <button wire:click="cancelMeeting({{ $childMeeting->id }})" class="px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-md hover:bg-red-600">Cancel</button>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>