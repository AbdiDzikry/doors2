<div wire:poll.10s>
    @if($notifications->count() > 0)
        <div class="mb-6 space-y-2">
            @foreach($notifications as $notification)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm flex items-start justify-between animate-pulse">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-battery-empty text-red-500 mt-0.5"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-bold text-red-800">
                                {{ $notification->data['title'] ?? 'Alert' }}
                            </h3>
                            <div class="mt-1 text-sm text-red-700">
                                {{ $notification->data['message'] ?? '' }}
                                <div class="mt-1 text-xs text-red-500 font-medium">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button wire:click="markAsRead('{{ $notification->id }}')" class="bg-red-100 rounded-md inline-flex text-red-500 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 p-1.5">
                            <span class="sr-only">Dismiss</span>
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
