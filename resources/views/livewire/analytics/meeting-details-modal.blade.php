<div x-data="{ show: @entangle('showModal') }" x-show="show" class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto" style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75 backdrop-blur-sm" @click="show = false; $wire.close()"></div>
    </div>

    <!-- Modal Panel -->
    <div class="bg-white rounded-xl overflow-hidden shadow-2xl transform transition-all sm:max-w-4xl sm:w-full m-4 flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="text-lg leading-6 font-bold text-gray-900">Reports Detail</h3>
                <p class="text-xs text-gray-500 mt-1">
                    List of meetings based on current analytics filter.
                </p>
            </div>
            <button @click="show = false; $wire.close()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-4 overflow-y-auto flex-grow bg-white">
            @if($meetings && count($meetings) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic / Time</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organizer</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($meetings as $meeting)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-gray-900">{{ Str::limit($meeting->topic, 30) }}</span>
                                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($meeting->start_time)->format('D, d M Y H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-gray-900">{{ $meeting->user->name ?? 'Unknown' }}</span>
                                            <span class="text-xs text-gray-500">{{ $meeting->user->department ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $meeting->room->name ?? 'Deleted Room' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $statusClasses = match($meeting->calculated_status) {
                                                'scheduled' => 'bg-indigo-100 text-indigo-800',
                                                'ongoing' => 'bg-green-100 text-green-800 animate-pulse',
                                                'completed' => 'bg-gray-100 text-gray-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses }}">
                                            {{ ucfirst($meeting->calculated_status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('meeting.meeting-lists.show', $meeting->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-full transition-colors" title="View Details">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $meetings->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-gray-300 text-5xl mb-4">
                        <i class="far fa-folder-open"></i>
                    </div>
                    <p class="text-gray-500 text-lg">No meetings found for this period.</p>
                </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-3 bg-gray-50/50 border-t border-gray-100 flex justify-end">
            <button @click="show = false; $wire.close()" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Close
            </button>
        </div>
    </div>
</div>
