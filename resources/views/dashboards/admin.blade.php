@extends('layouts.master')
@section('title', 'Admin Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
@endpush

@section('content')
<div class="container mx-auto px-6 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Dashboard</h1>
    <p class="text-gray-600 mb-8">Welcome back, {{ Auth::user()->name }}. Here's a snapshot of the system.</p>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card title="Total Rooms" :value="$totalRooms" icon="fas fa-door-open" :route="route('master.rooms.index')" />
        <x-stat-card title="Rooms In Use" :value="$roomsInUse" icon="fas fa-person-booth" :route="route('meeting.meeting-lists.index')" />
        <x-stat-card title="Total Users" :value="$totalUsers" icon="fas fa-users" :route="route('master.users.index')" />
        <x-stat-card title="Meetings Today" :value="$totalMeetingsToday" icon="fas fa-calendar-day" :route="route('meeting.meeting-lists.index')" />
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Chart -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Meeting Activity (Last 7 Days)</h2>
            <canvas id="meetingsChart"></canvas>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Links</h2>
            <div class="space-y-4">
                <a href="{{ route('master.rooms.create') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-plus-circle text-green-500 mr-4"></i>
                    <span class="font-medium text-gray-700">Add New Room</span>
                </a>
                <a href="{{ route('master.users.create') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-user-plus text-blue-500 mr-4"></i>
                    <span class="font-medium text-gray-700">Add New User</span>
                </a>
                 <a href="{{ route('master.pantry-items.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-boxes text-purple-500 mr-4"></i>
                    <span class="font-medium text-gray-700">Manage Pantry</span>
                </a>
                <a href="{{ route('master.external-participants.index') }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-user-friends text-yellow-500 mr-4"></i>
                    <span class="font-medium text-gray-700">External Participants</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('meetingsChart').getContext('2d');
        const meetingsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Number of Meetings',
                    data: @json($chartData),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
@endpush