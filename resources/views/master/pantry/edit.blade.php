@extends('layouts.master')

@section('title', 'Edit Pantry Item')

@section('content')
<div class="bg-gray-50">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Form Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Pantry Item</h1>
                        <p class="mt-1 text-sm text-gray-500">Update the details for {{ $item->name }}.</p>
                    </div>
                    <a href="{{ route('master.pantry-items.index') }}"
                        class="flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-arrow-left mr-2 text-gray-500"></i>
                        Back
                    </a>
                </div>

                <form action="{{ route('master.pantry-items.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="mt-8 space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Item Details Section -->
                    <div>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Item Details</h3>
                        <hr class="mt-2 border-gray-200">
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Name -->
                            <div class="sm:col-span-4 relative">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    required placeholder="e.g. Coffee">
                            </div>

                            <!-- Type -->
                            <div class="sm:col-span-2">
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select name="type" id="type"
                                    class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                    <option value="makanan" {{ old('type', $item->type) == 'makanan' ? 'selected' : '' }}>Food</option>
                                    <option value="minuman" {{ old('type', $item->type) == 'minuman' ? 'selected' : '' }}>Beverage</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="sm:col-span-6">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" id="description" rows="3"
                                    class="block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    placeholder="A brief description of the item.">{{ old('description', $item->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Stock & Status Section -->
                    <div>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Stock & Status</h3>
                        <hr class="mt-2 border-gray-200">
                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Stock -->
                            <div class="sm:col-span-2 relative">
                                <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none pt-6">
                                    <i class="fas fa-boxes text-gray-400"></i>
                                </div>
                                <input type="number" name="stock" id="stock" value="{{ old('stock', $item->stock) }}"
                                    class="pl-10 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                    placeholder="0">
                            </div>





                            <!-- Image -->
                            <div class="sm:col-span-6">
                                <label for="image" class="block text-sm font-medium text-gray-700">Item Image</label>
                                <div class="mt-1 flex items-center">
                                    @if($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-16 h-16 rounded-md object-cover mr-4">
                                    @endif
                                    <input type="file" name="image" id="image" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-5">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('master.pantry-items.index') }}"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-save mr-2"></i>
                                Update Item
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