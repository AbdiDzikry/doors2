@extends('layouts.master')

@section('title', 'System Configurations')

@section('content')
    <div class="container-fluid px-6 py-4">
        <div class="py-4">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">System Configurations</h1>
            <p class="text-sm text-gray-600">Manage application settings and behaviors</p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">
                <i class="fas fa-calendar-check mr-2 text-blue-600"></i>
                Meeting Management
            </h2>

            <form action="{{ route('settings.configurations.update-bulk') }}" method="POST">
                @csrf
                @method('PUT')

                @php
                    $autoCancelConfig = $configurations->firstWhere('key', 'auto_cancel_unattended_meetings');
                    $isEnabled = $autoCancelConfig && $autoCancelConfig->value === '1';
                @endphp

                <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <div class="flex-shrink-0 mt-1">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="configurations[auto_cancel_unattended_meetings]" 
                                   value="1" 
                                   {{ $isEnabled ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    <div class="flex-grow">
                        <h3 class="text-base font-semibold text-gray-800 mb-1">
                            Auto-cancel unattended meetings
                        </h3>
                        <p class="text-sm text-gray-600">
                            Automatically cancel meetings <strong>30 minutes</strong> after start time if no one checks in. 
                            This frees up the room for other bookings and prevents resource waste.
                        </p>
                        <div class="mt-2 text-xs text-gray-500 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span>Meetings with at least one participant checked in will not be cancelled</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Future configurations can be added here --}}
        <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-6 mt-6 text-center">
            <i class="fas fa-cog text-gray-400 text-3xl mb-2"></i>
            <p class="text-gray-500 text-sm">More configuration options coming soon...</p>
        </div>
    </div>
@endsection