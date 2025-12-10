@extends('layouts.master')

@section('title', 'Master External Participants')

@section('content')
<div class="bg-gray-50">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Page Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">External Participant Management</h1>
                        <p class="mt-1 text-sm text-gray-500">View, search, add, and manage participants.</p>
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <a href="{{ route('master.external-participants.create') }}"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Add New
                        </a>
                        <div x-data="{ showImportModal: false }">
                            <button @click="showImportModal = true"
                                class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                <i class="fas fa-file-import mr-2"></i>
                                Import
                            </button>
                            <!-- Import Modal -->
                            <div x-show="showImportModal" x-cloak
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                @keydown.escape.window="showImportModal = false">
                                <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="showImportModal = false">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Import Participants</h3>
                                    <form action="{{ route('master.external-participants.import') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div>
                                            <label for="file" class="block text-sm font-medium text-gray-700">Choose file to import</label>
                                            <input type="file" name="file" id="file"
                                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                                                required>
                                        </div>
                                        <div class="mt-6 flex justify-end space-x-3">
                                            <button type="button" @click="showImportModal = false"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none">
                                                Import Data
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('master.external-participants.export') }}"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                            <i class="fas fa-file-export mr-2"></i>
                            Export
                        </a>
                        <a href="{{ route('master.external-participants.template') }}"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
                            title="Download Excel Template">
                            <i class="fas fa-file-download"></i>
                        </a>
                    </div>
                </div>

                <!-- Livewire Participant List -->
                <div class="mt-6">
                    @livewire('master.external-participant-list')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
@endpush