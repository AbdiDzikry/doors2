<div wire:poll.keep-alive>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-bold text-gray-800">
            <i class="fas fa-utensils text-green-600 mr-2"></i> Active Pantry Queue
        </h2>
        <div class="flex items-center space-x-2">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <span class="text-xs text-green-600 font-medium">Live Updates Active</span>
        </div>
    </div>

    @if ($activePantryOrdersGroupedByMeeting->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-50 mb-4">
                <i class="fas fa-check text-2xl text-green-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">All caught up!</h3>
            <p class="text-gray-500 mt-2">There are no pending or preparing pantry orders right now.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($activePantryOrdersGroupedByMeeting as $meetingId => $orders)
                @php 
                    $meeting = $orders->first()->meeting; 
                    $organizer = $meeting->user;
                    $bgClass = $orders->contains('status', 'pending') ? 'border-amber-200 ring-1 ring-amber-100' : 'border-gray-200';
                @endphp
                
                <div class="bg-white rounded-xl shadow-sm border {{ $bgClass }} flex flex-col h-full hover:shadow-md transition-shadow duration-200">
                    <!-- Card Header: Meeting Info & Organizer -->
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50 rounded-t-xl">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-2">
                                    {{ $meeting->room->name }}
                                </span>
                                <h3 class="font-bold text-gray-900 line-clamp-1" title="{{ $meeting->topic }}">{{ $meeting->topic }}</h3>
                                <div class="flex items-center text-xs text-gray-500 mt-1">
                                    <i class="far fa-clock mr-1.5"></i>
                                    {{ $meeting->start_time->format('H:i') }} - {{ $meeting->end_time->format('H:i') }}
                                </div>
                            </div>
                            <!-- Organizer Avatar -->
                            <div class="flex flex-col items-center ml-3">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-sm font-bold border-2 border-white shadow-sm overflow-hidden">
                                    {{ substr($organizer->name, 0, 1) }}
                                </div>
                                <span class="text-[10px] text-gray-500 mt-1 max-w-[60px] truncate text-center" title="{{ $organizer->name }}">
                                    {{ explode(' ', $organizer->name)[0] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Body: Item List -->
                    <div class="p-5 flex-grow">
                        <ul class="space-y-4">
                            @foreach ($orders as $order)
                                <li class="flex flex-col pb-3 border-b border-gray-50 last:border-0 last:pb-0">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 pr-2">
                                            <div class="flex items-center">
                                                <span class="font-semibold text-gray-900 text-sm">{{ $order->pantryItem->name }}</span>
                                                <span class="text-xs text-gray-500 ml-1">x{{ $order->quantity }}</span>
                                            </div>
                                            @if (!empty($order->custom_items))
                                                <p class="text-xs text-amber-600 mt-0.5 italic">
                                                    Note: {{ $order->custom_items }}
                                                </p>
                                            @endif
                                        </div>
                                        
                                        <!-- Granular Actions -->
                                        <div class="flex items-center space-x-1">
                                            @if ($order->status === 'pending')
                                                <form action="{{ route('dashboard.receptionist.pantry-orders.update', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="preparing">
                                                    <button type="submit" class="p-1.5 rounded bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors" title="Mark Preparing">
                                                        <i class="fas fa-fire text-xs"></i>
                                                    </button>
                                                </form>
                                            @elseif ($order->status === 'preparing')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 mr-1">
                                                    Prep
                                                </span>
                                                <form action="{{ route('dashboard.receptionist.pantry-orders.update', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="delivered">
                                                    <button type="submit" class="p-1.5 rounded bg-green-50 text-green-600 hover:bg-green-100 transition-colors" title="Mark Delivered">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <!-- Card Footer: Bulk Actions -->
                    <div class="p-4 bg-gray-50 border-t border-gray-100 rounded-b-xl">
                        <div class="flex gap-2">
                            <!-- Bulk Prepare -->
                            <form action="{{ route('dashboard.receptionist.meetings.pantry-status', $meeting->id) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="preparing">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    <i class="fas fa-fire mr-1.5 text-amber-500"></i> Prep All
                                </button>
                            </form>
                            
                            <!-- Bulk Deliver -->
                            <form action="{{ route('dashboard.receptionist.meetings.pantry-status', $meeting->id) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-2 border border-transparent shadow-sm text-xs font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                    <i class="fas fa-check mr-1.5"></i> Deliver All
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
