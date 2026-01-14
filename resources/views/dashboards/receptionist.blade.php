@extends('layouts.master')
@section('title', 'Receptionist Dashboard')
@section('content')
<div class="container-fluid px-6 py-8" x-data="{ 'showToast': false, 'toastMessage': '' }" @keydown.escape.window="showToast = false">
    
    <!-- Battery Notifications -->
    @livewire('dashboard.receptionist.notifications')
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Receptionist Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Manage meeting room pantry orders and logistics.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-4">
            <img src="{{ asset('logo_dharma.png') }}" alt="Dharma Polimetal" class="h-14 object-contain mr-4">
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100 flex items-center">
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg mr-3">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Pending Orders</span>
                    <span class="block text-xl font-bold text-gray-900">{{ $pendingPantryOrdersCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pantry Queue Section -->
    <div class="mb-10">
        @livewire('dashboard.receptionist.pantry-queue')
    </div>

    <!-- Historical Orders Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h2 class="text-lg font-bold text-gray-800">Order History</h2>
            
            <!-- Search & Filter -->
            <form action="{{ route('dashboard.receptionist') }}" method="GET" class="flex items-center space-x-2">
                
                <!-- Status Filter -->
                <select name="status_filter" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 py-1.5 pl-3 pr-8">
                    <option value="delivered" {{ request('status_filter', 'delivered') === 'delivered' ? 'selected' : '' }}>History (Delivered)</option>
                    <option value="all" {{ request('status_filter') === 'all' ? 'selected' : '' }}>All Records</option>
                    <option value="pending" {{ request('status_filter') === 'pending' ? 'selected' : '' }}>Pending Only</option>
                    <option value="preparing" {{ request('status_filter') === 'preparing' ? 'selected' : '' }}>Preparing Only</option>
                </select>

                <!-- Search -->
                <div class="relative">
                    <input type="text" name="search" placeholder="Search orders..." value="{{ request('search') }}" class="pl-9 pr-4 py-1.5 text-sm border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-xs"></i>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Meeting</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Room</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($historicalMeetings as $meeting)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap align-top">
                                <div class="text-sm font-medium text-gray-900">{{ $meeting->topic ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $meeting->user->name ?? 'Unknown' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 align-top">
                                {{ $meeting->room->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 align-top" colspan="2">
                                <div class="space-y-2">
                                    @foreach($meeting->pantryOrders as $order)
                                        <div class="flex items-center justify-between border-b border-gray-100 last:border-0 pb-1 last:pb-0">
                                            <div class="flex items-center">
                                                <span class="text-sm text-gray-900">{{ $order->pantryItem->name ?? 'N/A' }}</span>
                                                <span class="text-xs text-gray-500 ml-1">x{{ $order->quantity }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @php
                                                        // Check if meeting has ended and order is not delivered
                                                        $isExpired = false;
                                                        if ($meeting->end_time && $order->status !== 'delivered' && $order->status !== 'cancelled') {
                                                            $isExpired = $meeting->end_time < now();
                                                        }
                                                    @endphp

                                                    @if($isExpired)
                                                        bg-red-100 text-red-800
                                                    @else
                                                        @switch($order->status)
                                                            @case('pending') bg-amber-100 text-amber-800 @break
                                                            @case('preparing') bg-blue-100 text-blue-800 @break
                                                            @case('delivered') bg-green-100 text-green-800 @break
                                                            @default bg-gray-100 text-gray-800
                                                        @endswitch
                                                    @endif">
                                                    {{ $isExpired ? 'Not Delivered' : ucfirst($order->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 align-top">
                                {{ $meeting->start_time->format('d M H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">
                                No history found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $historicalMeetings->links() }}
        </div>
    </div>
</div>
@endsection
