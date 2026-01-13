<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Timeline Controls -->
    <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/50">
        <div class="flex items-center space-x-2">
            <h3 class="font-bold text-gray-700"><i class="fas fa-stream mr-2 text-green-600"></i>Room Schedule</h3>
            <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</span>
        </div>
        
        <!-- Legend -->
        <div class="flex items-center gap-3 text-xs" id="tour-timeline-legend">
            <div class="flex items-center"><span class="w-3 h-3 bg-white border border-gray-300 mr-1.5 rounded-sm"></span> Available</div>
            <div class="flex items-center"><span class="w-3 h-3 bg-yellow-100 border border-yellow-300 mr-1.5 rounded-sm"></span> Scheduled</div>
            <div class="flex items-center"><span class="w-3 h-3 bg-blue-100 border border-blue-500 mr-1.5 rounded-sm"></span> Ongoing</div>
            <div class="flex items-center"><span class="w-3 h-3 bg-green-100 border border-green-200 mr-1.5 rounded-sm"></span> Completed</div>
        </div>
    </div>

    <!-- Timeline Container -->
    <div class="overflow-x-auto custom-scrollbar">
        <div class="min-w-[1000px] relative">
            
            <!-- Time Header -->
            <div class="flex border-b border-gray-200 bg-gray-50 sticky top-0 z-[90]" id="tour-timeline-header">
                <div class="w-48 flex-shrink-0 p-3 text-xs font-bold text-gray-500 uppercase tracking-wider border-r border-gray-200 bg-gray-50 sticky left-0 z-[100] shadow-[4px_0_8px_-4px_rgba(0,0,0,0.1)]">
                    Room Name
                </div>
                <div class="flex-grow relative h-10">
                    @php
                        $startHour = 7; // Start at 07:00
                        $endHour = 19;  // End at 19:00
                        $totalHours = $endHour - $startHour;
                    @endphp
                    
                    @for ($i = 0; $i <= $totalHours; $i++)
                        @php $hour = $startHour + $i; @endphp
                        <div class="absolute bottom-0 text-xs text-gray-400 font-medium border-l border-gray-200 pl-1 pb-1" 
                             style="left: {{ ($i / $totalHours) * 100 }}%; height: 50%;">
                            {{ sprintf('%02d:00', $hour) }}
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Room Rows -->
            @forelse($rooms as $room)
                <div class="flex border-b border-gray-100 hover:bg-gray-50 transition-colors group">
                    <!-- Room Name (Sticky Column) -->
                    <div id="tour-timeline-rooms" class="w-48 flex-shrink-0 p-3 border-r border-gray-200 bg-white sticky left-0 z-[70] group-hover:bg-gray-50 transition-colors shadow-[4px_0_8px_-4px_rgba(0,0,0,0.1)]">
                        <div class="flex flex-col h-full justify-center">
                            <span class="text-sm font-bold text-gray-800">{{ $room->name }}</span>
                            <span class="text-xs text-gray-500">{{ $room->capacity }} Pax</span>
                        </div>
                    </div>

                    <!-- Timeline Grid -->
                    <div id="tour-timeline-grid" class="flex-grow relative h-16 bg-white/50 pattern-grid"
                         data-meetings="{{ json_encode($room->meetings->map(fn($m) => [
                             'start' => \Carbon\Carbon::parse($m->start_time)->toIso8601String(),
                             'end' => \Carbon\Carbon::parse($m->end_time)->toIso8601String()
                         ])) }}">
                        <!-- Hour Guidelines -->
                        @for ($i = 0; $i <= $totalHours; $i++)
                            <div class="absolute inset-y-0 border-l border-gray-100/60 pointer-events-none" 
                                 style="left: {{ ($i / $totalHours) * 100 }}%"></div>
                        @endfor

                        <!-- Past Time Shading -->
                        @php
                            $viewDate = \Carbon\Carbon::parse($date);
                            $shadingWidth = 0;
                            
                            if ($viewDate->isPast() && !$viewDate->isToday()) {
                                $shadingWidth = 100;
                            } elseif ($viewDate->isToday()) {
                                $now = now();
                                $graphStart = $viewDate->copy()->setHour($startHour)->setMinute(0)->setSecond(0);
                                $graphEnd = $viewDate->copy()->setHour($endHour)->setMinute(0)->setSecond(0);
                                
                                if ($now->gt($graphStart)) {
                                    $minutesElapsed = $graphStart->diffInMinutes($now);
                                    $totalMinutes = $totalHours * 60;
                                    $shadingWidth = ($minutesElapsed / $totalMinutes) * 100;
                                    
                                    // Cap at 100%
                                    if ($shadingWidth > 100) $shadingWidth = 100;
                                }
                            }
                        @endphp
                        
                        @if($shadingWidth > 0)
                            <!-- Past Time Background Shading (Behind Meetings) -->
                            <div class="absolute inset-y-0 left-0 bg-gray-200/50 z-[20] cursor-not-allowed" 
                                 style="width: {{ $shadingWidth }}%;"
                                 title="Waktu ini tidak dapat dipesan (Sudah berlalu)">
                            </div>
                            <!-- Current Time Line (On Top of Base Meetings, Below Hover) -->
                            <div class="absolute inset-y-0 border-r border-red-500 z-[25] pointer-events-none" 
                                 style="left: {{ $shadingWidth }}%; width: 1px;">
                            </div>
                        @endif

                        <!-- Meeting Blocks -->
                        @foreach($room->meetings as $meeting)
                            @php
                                $meetingStart = \Carbon\Carbon::parse($meeting->start_time);
                                $meetingEnd = \Carbon\Carbon::parse($meeting->end_time);
                                
                                // Calculate Graph Boundaries (Today 07:00 - 19:00)
                                $graphStart = \Carbon\Carbon::parse($date)->setHour($startHour)->setMinute(0)->setSecond(0);
                                $totalMinutes = $totalHours * 60;

                                // Clip meetings that start before graph range
                                $effectiveStart = $meetingStart->lessThan($graphStart) ? $graphStart : $meetingStart;
                                
                                // Calculate Position & Width
                                $startDiff = $graphStart->diffInMinutes($effectiveStart);
                                $duration = $effectiveStart->diffInMinutes($meetingEnd);
                                
                                $leftPercent = ($startDiff / $totalMinutes) * 100;
                                $widthPercent = ($duration / $totalMinutes) * 100;
                                
                                // Determine Color based on calculated status
                                // Note: calculated_status relies on now(), which is correct for real-time status.
                                $status = $meeting->calculated_status;
                                
                                $colorClass = 'bg-yellow-50 text-yellow-800 border-yellow-200 hover:bg-yellow-100'; // Default Scheduled
                                
                                if ($status === 'ongoing') {
                                    $colorClass = 'bg-blue-100 text-blue-800 border-blue-500 hover:bg-blue-200 ring-1 ring-blue-500';
                                } elseif ($status === 'completed') {
                                    $colorClass = 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200 opacity-80';
                                } elseif ($status === 'cancelled') {
                                     // Should typically be filtered out, but just in case
                                    $colorClass = 'bg-red-50 text-red-800 border-red-200 opacity-50 hidden'; 
                                }
                            @endphp

                            <!-- Block -->
                            <div class="absolute top-2 bottom-2 border rounded-md px-2 flex flex-col justify-center overflow-hidden cursor-pointer transition-all z-[30] hover:z-[50] hover:shadow-md {{ $colorClass }}"
                                 style="left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; min-width: 4px;"
                                 title="{{ $meeting->topic }} ({{ $meetingStart->format('H:i') }} - {{ $meetingEnd->format('H:i') }}) • {{ ucfirst($status) }}">
                                <span class="text-[10px] font-bold truncate leading-tight">{{ $meetingStart->format('H:i') }}-{{ $meetingEnd->format('H:i') }}</span>
                                <span class="text-xs font-semibold truncate">{{ $meeting->topic }}</span>
                            </div>
                        @endforeach
                        
                        <!-- Hover Indicator -->
                        <div class="timeline-hover-indicator absolute inset-y-0 w-px bg-blue-500 pointer-events-none hidden z-[55]">
                            <div class="absolute top-0 -translate-x-1/2 mt-1.5 bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm whitespace-nowrap">
                                <span class="hover-time-label">00:00</span>
                            </div>
                        </div>
                        
                        <!-- Free Space Click Handler (Overlay) -->
                        <div class="absolute inset-0 z-10 cursor-pointer hover:bg-green-900/5 transition-colors"
                             onclick="handleTimelineClick(event, '{{ $room->id }}', '{{ $date }}', this)"
                             onmousemove="handleTimelineHover(event, this)"
                             onmouseleave="hideTimelineHover(this)">
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">No rooms available.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function calculateSnappedTime(event, element, dateStr) {
        // 1. Calculate Click Position
        const rect = element.getBoundingClientRect();
        const offsetX = event.clientX - rect.left;
        const width = rect.width;
        const clickPercent = offsetX / width;

        // 2. Constants
        const startHour = 7;
        const endHour = 19;
        const totalMinutes = (endHour - startHour) * 60;

        // 3. Calculate Time
        const clickedMinutesFromStart = clickPercent * totalMinutes;
        const clickedDate = new Date(dateStr);
        clickedDate.setHours(startHour, 0, 0, 0); 
        clickedDate.setMinutes(clickedDate.getMinutes() + clickedMinutesFromStart);

        // Round to nearest 15 minutes
        const remainder = clickedDate.getMinutes() % 15;
        if (remainder >= 8) {
             clickedDate.setMinutes(clickedDate.getMinutes() + (15 - remainder));
        } else {
             clickedDate.setMinutes(clickedDate.getMinutes() - remainder);
        }
        
        clickedDate.setSeconds(0);
        clickedDate.setMilliseconds(0);

        return { clickedDate, clickPercent };
    }

    function handleTimelineHover(event, element) {
        // Find the indicator in this specific row (sibling of the overlay)
        const parent = element.parentElement;
        const indicator = parent.querySelector('.timeline-hover-indicator');
        const timeLabel = indicator.querySelector('.hover-time-label');
        
        if (!indicator) return;

        // We need dateStr. It's passed in onclick but we need it here.
        // Let's grab it from the onclick attribute string since passing it again is verbose in HTML
        // Or cleaner: add data-date to the container.
        // For now, let's parse it from the onclick handler which has it: handleTimelineClick(event, 'ID', 'DATE', this)
        // Actually, let's just use the global JS variable or assume today if missing? No, must be precise.
        // Let's add data-date to the parent .pattern-grid div.
        // The parent .pattern-grid already has data-meetings. Let's assume passed dateStr is needed.
        // I'll assume I can add data-date="{{ $date }}" to the pattern-grid div above. 
        // Wait, I can't easily edit the parent div in this replacement chunk without targeting line 50+.
        // WORKAROUND: The onclick attribute contains the date string! 
        // onclick="handleTimelineClick(event, '1', '2026-01-13', this)"
        const onClickAttr = element.getAttribute('onclick');
        const dateMatch = onClickAttr.match(/'(\d{4}-\d{2}-\d{2})'/);
        const dateStr = dateMatch ? dateMatch[1] : new Date().toISOString().split('T')[0];

        const { clickedDate } = calculateSnappedTime(event, element, dateStr);

        // Update Label
        const pad = (n) => n < 10 ? '0' + n : n;
        timeLabel.textContent = `${pad(clickedDate.getHours())}:${pad(clickedDate.getMinutes())}`;

        // Update Position
        // Re-calculate percent based on snapped time to align perfectly with grid
        const startHour = 7;
        const startBase = new Date(clickedDate);
        startBase.setHours(startHour, 0, 0, 0);
        const diffMinutes = (clickedDate - startBase) / 60000;
        const totalMinutes = (19 - 7) * 60;
        const percent = (diffMinutes / totalMinutes) * 100;

        indicator.style.left = `${percent}%`;
        indicator.classList.remove('hidden');
    }

    function hideTimelineHover(element) {
        const parent = element.parentElement;
        const indicator = parent.querySelector('.timeline-hover-indicator');
        if (indicator) indicator.classList.add('hidden');
    }

    function handleTimelineClick(event, roomId, dateStr, element) {
        const { clickedDate } = calculateSnappedTime(event, element, dateStr);

        // 4. Find Next Meeting Constraints
        const parentRow = element.parentElement; // element is the overlay, parent is pattern-grid
        let meetingsData = [];
        try {
            meetingsData = JSON.parse(parentRow.getAttribute('data-meetings') || '[]');
        } catch(e) { console.error(e); }
        
        meetingsData.sort((a, b) => new Date(a.start).getTime() - new Date(b.start).getTime());

        let endCapDate = null;
        for (const meeting of meetingsData) {
            const meetingStart = new Date(meeting.start);
            if (meetingStart > clickedDate) {
                endCapDate = meetingStart;
                break;
            }
        }

        // 5. Determine End Time
        const suggestedEndDate = new Date(clickedDate);
        suggestedEndDate.setHours(suggestedEndDate.getHours() + 1); 

        let finalEndDate = suggestedEndDate;
        if (endCapDate && endCapDate < suggestedEndDate) {
            finalEndDate = endCapDate;
        }
        
        const closingTime = new Date(dateStr);
        closingTime.setHours(19, 0, 0, 0);
        if (finalEndDate > closingTime) finalEndDate = closingTime;

        // 6. Format URL
        const formatDateTime = (date) => {
             const pad = (n) => n < 10 ? '0' + n : n;
             return date.getFullYear() + '-' + 
                    pad(date.getMonth() + 1) + '-' + 
                    pad(date.getDate()) + ' ' + 
                    pad(date.getHours()) + ':' + 
                    pad(date.getMinutes()) + ':00';
        };

        const url = `{{ route('meeting.bookings.create') }}?room_id=${roomId}&start_time=${encodeURIComponent(formatDateTime(clickedDate))}&end_time=${encodeURIComponent(formatDateTime(finalEndDate))}`;
        window.location.href = url;
    }
</script>

<style>
.pattern-grid {
    background-image: linear-gradient(to right, rgba(0,0,0,0.02) 1px, transparent 1px);
    background-size: {{ 100 / $totalHours }}% 100%; 
}
.custom-scrollbar::-webkit-scrollbar {
    height: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1; 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8; 
}
</style>
