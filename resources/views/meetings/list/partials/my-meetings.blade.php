<div class="space-y-6">
    <!-- Stats Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-gray-900">My Meeting Stats</h2>
            
            <!-- Filter -->
            <form action="{{ route('meeting.meeting-lists.index') }}" method="GET" class="w-full sm:w-auto mt-4 sm:mt-0" 
                  x-data="{ 
                      filter: '{{ request('filter', 'day') }}',
                      updateFilter() {
                          if (this.filter !== 'custom') {
                              document.getElementById('start_date_my').value = '';
                              document.getElementById('end_date_my').value = '';
                          }
                      }
                  }">
                <input type="hidden" name="tab" value="my-meetings">

                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <!-- Date Range Inputs (Visible only when custom) -->
                    <div class="grid grid-cols-2 gap-2" x-show="filter === 'custom'" x-cloak x-transition>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Start</label>
                            <input type="date" id="start_date_my" name="start_date" value="{{ request('start_date') }}" 
                                class="block w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">End</label>
                            <input type="date" id="end_date_my" name="end_date" value="{{ request('end_date') }}" 
                                class="block w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <label for="my_meetings_filter" class="text-sm font-medium text-gray-500 sm:hidden">Period</label>
                        <div class="relative w-full sm:w-auto" x-data="{ 
                            open: false, 
                            options: {
                                'custom': 'Custom Range',
                                'day': 'Today', 
                                'week': 'This Week', 
                                'month': 'This Month', 
                                'year': 'This Year',
                                'all': 'All Time'
                            },
                            get activeLabel() { return this.options[this.filter] } 
                        }" @click.away="open = false">
                            <!-- Hidden Input -->
                            <input type="hidden" name="filter" x-model="filter">
    
                            <!-- Trigger -->
                            <button type="button" @click="open = !open" 
                                class="relative w-full sm:w-40 bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-3 pr-8 py-1.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                <span class="block truncate" x-text="activeLabel"></span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                </span>
                            </button>
    
                            <!-- Options -->
                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute z-10 mt-1 w-full sm:w-40 bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                style="display: none;">
                                <template x-for="(label, value) in options" :key="value">
                                    <div @click="filter = value; updateFilter(); open = false; if(value !== 'custom') $nextTick(() => { $el.closest('form').submit() });"
                                         class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                         :class="{ 'text-green-900 bg-green-50': filter == value, 'text-gray-900 hover:bg-green-50 hover:text-green-700': filter != value }">
                                        <span class="block truncate font-medium" :class="{ 'font-semibold': filter == value, 'font-normal': filter != value }" x-text="label"></span>
                                        <span x-show="filter == value" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                            <i class="fas fa-check text-xs"></i>
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Filter Button (Visible only when custom) -->
                        <button type="submit" x-show="filter === 'custom'" class="bg-green-600 hover:bg-green-700 text-white p-2 rounded-lg shadow-sm transition-colors" title="Apply Filter">
                            <i class="fas fa-filter text-xs"></i>
                        </button>
                    </div>
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
                                        'scheduled' => 'bg-indigo-50 text-indigo-700',
                                        'ongoing' => 'bg-blue-100 text-blue-800 ring-2 ring-blue-500 ring-opacity-50',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $statusLabel = ucfirst($meeting->calculated_status);
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                    @if($meeting->calculated_status === 'ongoing')
                                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5 animate-pulse"></span>
                                    @elseif($meeting->calculated_status === 'completed')
                                        <i class="fas fa-check-circle mr-1.5 text-xs"></i>
                                    @elseif($meeting->calculated_status === 'scheduled')
                                        <i class="fas fa-clock mr-1.5 text-xs"></i>
                                    @elseif($meeting->calculated_status === 'cancelled')
                                        <i class="fas fa-times-circle mr-1.5 text-xs"></i>
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

                                    @if($meeting->calculated_status !== 'cancelled')
                                        <a href="{{ route('meeting.meetings.attendance.export', $meeting->id) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Download Absensi">
                                            <i class="far fa-file-excel text-lg"></i>
                                        </a>
                                    @endif

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
