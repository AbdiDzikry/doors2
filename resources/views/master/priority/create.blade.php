@extends('layouts.master')

@section('title', 'Create Priority Guest')

@section('content')
<div class="bg-gray-50">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Form Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Add New Priority Guest</h1>
                        <p class="mt-1 text-sm text-gray-500">Fill in the details below to create a new priority guest.</p>
                    </div>
                    <a href="{{ route('master.priority-guests.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-arrow-left mr-2 text-gray-500"></i>
                        Back
                    </a>
                </div>

                <form action="{{ route('master.priority-guests.store') }}" method="POST" class="mt-8 space-y-8">
                    @csrf

                    <!-- Guest Details Section -->
                    <div>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Guest Details</h3>
                        <hr class="mt-2 border-gray-200">
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <!-- Name -->
                            <div class="sm:col-span-1 relative">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Guest Name</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-user-tie text-gray-400"></i>
                                </div>
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    required placeholder="e.g. John Doe">
                            </div>

                            <!-- Level -->
                            <div class="sm:col-span-1 relative">
                                <label for="level" class="block text-sm font-medium text-gray-700 mb-1">Priority Level (1-5)</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-star text-gray-400"></i>
                                </div>
                                <input type="number" name="level" id="level" value="{{ old('level', 1) }}" min="1" max="5"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-5">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('master.priority-guests.index') }}"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-save mr-2"></i>
                                Save Guest
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
@endpush