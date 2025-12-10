@foreach ($pantryOrders as $order)
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <h2 class="text-xl font-bold mb-2">Meeting: {{ $order->meeting->topic }}</h2>
        <p class="mb-2">Room: {{ $order->meeting->room->name }}</p>
        <p class="mb-2">Time: {{ \Carbon\Carbon::parse($order->meeting->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($order->meeting->end_time)->format('H:i') }}</p>
        <p class="mb-4">Item: {{ $order->pantryItem->name }} x {{ $order->quantity }}</p>

        @if (!empty($order->custom_items))
            <div class="mt-2 text-sm text-gray-700 bg-yellow-100 border-l-4 border-yellow-500 p-3 rounded-r-lg">
                <p class="font-semibold">Custom Request:</p>
                <p class="whitespace-pre-wrap">{{ $order->custom_items }}</p>
            </div>
        @endif

        <div class="mt-4 flex space-x-2">
            @if ($order->status == 'pending')
                <form action="{{ route('dashboard.receptionist.pantry-orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="preparing">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Siapkan</button>
                </form>
            @elseif ($order->status == 'preparing')
                <form action="{{ route('dashboard.receptionist.pantry-orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="delivered">
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Selesai/Antar</button>
                </form>
            @else
                <span class="text-gray-500">Status: {{ ucfirst($order->status) }}</span>
            @endif
        </div>
    </div>
@endforeach