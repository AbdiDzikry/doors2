<div class="space-y-6">
    <!-- Stats Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-gray-900">My Meeting Stats</h2>
            
            <!-- Filter -->
            <form action="{{ route('meeting.meeting-lists.index') }}" method="GET" class="w-full sm:w-auto mt-4 sm:mt-0">
                <input type="hidden" name="tab" value="my-meetings">
                <div class="flex items-center space-x-2">
                    <label for="my_meetings_filter" class="text-sm font-medium text-gray-500">Period</label>
                    <select name="filter" id="my_meetings_filter" class="block w-full sm:w-40 rounded-lg border-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-50" onchange="this.form.submit()">
                        <option value="day" {{ $filter == 'day' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $filter == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $filter == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="year" {{ $filter == 'year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Meetings</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-white rounded-full shadow-sm">
                    <i class="fas fa-calendar-alt text-gray-400 text-xl"></i>
                </div>
            </div>

            <!-- Scheduled -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Scheduled</p>
                    <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['scheduled'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-white rounded-full shadow-sm">
                    <i class="fas fa-clock text-blue-500 text-xl"></i>
                </div>
            </div>

            <!-- Completed -->
            <div class="bg-green-50 rounded-lg p-4 border border-green-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-green-600 uppercase tracking-wider">Completed</p>
                    <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['completed'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-white rounded-full shadow-sm">
                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                </div>
            </div>

            <!-- Cancelled -->
            <div class="bg-red-50 rounded-lg p-4 border border-red-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wider">Cancelled</p>
                    <p class="text-2xl font-bold text-red-900 mt-1">{{ $stats['cancelled'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-white rounded-full shadow-sm">
                    <i class="fas fa-times-circle text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="text-md font-bold text-gray-900">My Schedule</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Topic</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Room</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="relative px-6 py-4"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($myMeetings as $meeting)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <!-- Topic -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ ucwords(strtolower($meeting->topic)) }}</div>
                            </td>

                            <!-- Room -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                    {{ $meeting->room->name }}
                                </div>
                            </td>

                            <!-- Time -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($meeting->start_time)->format('d-m-Y') }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}
                                    </span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = match($meeting->calculated_status) {
                                        'scheduled' => 'bg-blue-100 text-blue-800',
                                        'ongoing' => 'bg-green-100 text-green-800 ring-2 ring-green-500 ring-opacity-50',
                                        'completed' => 'bg-gray-100 text-gray-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $statusLabel = ucfirst($meeting->calculated_status);
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                    @if($meeting->calculated_status === 'ongoing')
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                    @endif
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <a href="{{ route('meeting.meeting-lists.show', $meeting->id) }}" class="text-gray-400 hover:text-green-600 transition-colors" title="View Details">
                                        <i class="far fa-eye text-lg"></i>
                                    </a>

                                    <a href="{{ route('meeting.meetings.attendance.export', $meeting->id) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Download Absensi">
                                        <i class="far fa-file-excel text-lg"></i>
                                    </a>

                                    @if($meeting->calculated_status !== 'cancelled')
                                        @if(Auth::user()->hasAnyRole(['Admin', 'Super Admin']) || (Auth::id() === $meeting->user_id && !in_array($meeting->calculated_status, ['ongoing', 'completed'])))
                                            <a href="{{ route('meeting.meeting-lists.edit', $meeting->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit Meeting">
                                                <i class="far fa-edit text-lg"></i>
                                            </a>
                                        @endif

                                        @if(Auth::user()->hasAnyRole(['Admin', 'Super Admin']) || Auth::id() === $meeting->user_id)
                                            <form action="{{ route('meeting.meeting-lists.destroy', $meeting->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to cancel this meeting?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Cancel Meeting">
                                                    <i class="far fa-trash-alt text-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                                    <p class="text-base font-medium text-gray-900">No meetings found</p>
                                    <p class="text-sm">You haven't scheduled any meetings for this period.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
