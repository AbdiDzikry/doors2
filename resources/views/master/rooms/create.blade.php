@extends('layouts.master')

@section('title', 'Create Room')

@section('content')
<div class="bg-gray-50">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Form Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Add New Room</h1>
                        <p class="mt-1 text-sm text-gray-500">Fill in the details below to create a new meeting room.</p>
                    </div>
                    <a href="{{ route('master.rooms.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-arrow-left mr-2 text-gray-500"></i>
                        Back
                    </a>
                </div>

                <!-- Session Messages -->
                @if (session('message'))
                    <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline">{{ session('message') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif


                <form action="{{ route('master.rooms.store') }}" method="POST" enctype="multipart/form-data" class="mt-8 space-y-8">
                    @csrf

                    <!-- Room Details Section -->
                    <div>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Room Details</h3>
                        <hr class="mt-2 border-gray-200">
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Name -->
                            <div class="sm:col-span-6 relative">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-door-closed text-gray-400"></i>
                                </div>
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    required placeholder="e.g. Melati Room">
                            </div>

                            <!-- Description -->
                            <div class="sm:col-span-6">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" id="description" rows="3"
                                    class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    placeholder="A brief description of the room.">{{ old('description') }}</textarea>
                            </div>

                             <!-- Facilities -->
                            <div class="sm:col-span-6">
                                <label for="facilities" class="block text-sm font-medium text-gray-700 mb-1">Facilities</label>
                                <textarea name="facilities" id="facilities" rows="3"
                                    class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    placeholder="e.g. Whiteboard, Projector, Video Conference">{{ old('facilities') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Capacity Section -->
                    <div>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Location & Capacity</h3>
                        <hr class="mt-2 border-gray-200">
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Floor -->
                            <div class="sm:col-span-3 relative">
                                <label for="floor" class="block text-sm font-medium text-gray-700 mb-1">Floor</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-layer-group text-gray-400"></i>
                                </div>
                                <input type="text" name="floor" id="floor" value="{{ old('floor') }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    placeholder="e.g. 5th Floor">
                            </div>

                            <!-- Capacity -->
                            <div class="sm:col-span-3 relative">
                                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-users text-gray-400"></i>
                                </div>
                                <input type="number" name="capacity" id="capacity" value="{{ old('capacity', 1) }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    placeholder="e.g. 10">
                            </div>
                        </div>
                    </div>

                     <!-- Status & Image Section -->
                    <div>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Status & Image</h3>
                        <hr class="mt-2 border-gray-200">
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                             <!-- Status -->
                            <div class="sm:col-span-3">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status"
                                    class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                    <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="under_maintenance" {{ old('status') == 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                </select>
                            </div>

                            <!-- Image -->
                            <div class="sm:col-span-6">
                                <label for="image" class="block text-sm font-medium text-gray-700">Room Image</label>
                                <div class="mt-1 flex items-center">
                                    <input type="file" name="image" id="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-5">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('master.rooms.index') }}"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-save mr-2"></i>
                                Save Room
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