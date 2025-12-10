@extends('layouts.master')

@section('title', 'Master Priority Guests')

@section('content')
<div class="bg-gray-50">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Page Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Priority Guest Management</h1>
                        <p class="mt-1 text-sm text-gray-500">View, search, add, and manage priority guests.</p>
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <a href="{{ route('master.priority-guests.create') }}"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Add New Guest
                        </a>
                    </div>
                </div>

                <!-- Livewire Priority Guest List -->
                <div class="mt-6">
                    @livewire('master.priority-guest-list')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
@endpush