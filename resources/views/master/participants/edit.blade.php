@extends('layouts.master')

@section('title', 'Edit External Participant')

@section('content')
<div class="bg-gray-50">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Form Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Participant</h1>
                        <p class="mt-1 text-sm text-gray-500">Update the participant's details below.</p>
                    </div>
                    <a href="{{ route('master.external-participants.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-arrow-left mr-2 text-gray-500"></i>
                        Back
                    </a>
                </div>

                <form action="{{ route('master.external-participants.update', $participant->id) }}" method="POST" class="mt-8 space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Personal Information Section -->
                    <div>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Personal Information</h3>
                        <hr class="mt-2 border-gray-200">
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <!-- Name -->
                            <div class="relative">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" name="name" id="name" value="{{ old('name', $participant->name) }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('name') border-red-500 @enderror"
                                    required oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                                @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Email -->
                            <div class="relative">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" name="email" id="email" value="{{ old('email', $participant->email) }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('email') border-red-500 @enderror"
                                    required>
                                @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Phone -->
                            <div class="relative sm:col-span-2">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $participant->phone) }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('phone') border-red-500 @enderror"
                                    oninput="this.value = this.value.replace(/[^0-9\s+\-()]/g, '')">
                                @error('phone')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- Organizational Information Section -->
                    <div>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Organizational Information</h3>
                        <hr class="mt-2 border-gray-200">
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <!-- Company -->
                            <div class="relative">
                                <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-building text-gray-400"></i>
                                </div>
                                <input type="text" name="company" id="company" value="{{ old('company', $participant->company) }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('company') border-red-500 @enderror">
                                @error('company')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Department Removed -->

                            <!-- Type -->
                            <div class="relative sm:col-span-2">
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <div class="relative" x-data="{ 
                                    open: false, 
                                    selected: '{{ old('type', $participant->type) }}',
                                    options: {
                                        'external': 'External',
                                        'internal': 'Internal'
                                    },
                                    get label() { return this.options[this.selected] }
                                }" @click.away="open = false">
                                    <input type="hidden" name="type" :value="selected">
                                    <button type="button" @click="open = !open" 
                                        class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                        <span class="block truncate" x-text="label"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                        </span>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                        style="display: none;">
                                        @foreach (['external' => 'External', 'internal' => 'Internal'] as $val => $text)
                                            <div @click="selected = '{{ $val }}'; open = false"
                                                 class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                                 :class="{ 'text-green-900 bg-green-50': selected == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected != '{{ $val }}' }">
                                                <span class="block truncate font-medium" :class="{ 'font-semibold': selected == '{{ $val }}' }">{{ $text }}</span>
                                                <span x-show="selected == '{{ $val }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                                    <i class="fas fa-check text-xs"></i>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <!-- Address -->
                            <div class="relative sm:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                <textarea name="address" id="address" rows="3"
                                    class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('address') border-red-500 @enderror">{{ old('address', $participant->address) }}</textarea>
                                @error('address')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-5">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('master.external-participants.index') }}"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-save mr-2"></i>
                                Update Participant
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