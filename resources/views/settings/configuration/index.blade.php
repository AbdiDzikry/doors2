@extends('layouts.master')

@section('title', 'Settings - Configurations')

@section('content')
    <div class="container-fluid px-6 py-4">
        <div class="py-4">
            <h1 class="text-2xl font-bold text-gray-800">Settings - Configurations</h1>
            <p class="text-sm text-gray-600">Manage application key-value configurations.</p>
        </div>

        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
            <p class="font-bold">About Application Configurations</p>
            <p class="text-sm">This page allows you to manage application-wide settings. Each setting is defined by a unique <strong>Key</strong> and its corresponding <strong>Value</strong>. The optional <strong>Description</strong> provides context for what the setting does.</p>
            <ul class="list-disc list-inside mt-2 text-sm">
                <li><strong>Key:</strong> A unique identifier for the setting. Keys are typically uppercase and use underscores for spaces.</li>
                <li><strong>Value:</strong> The actual setting data. Be cautious when changing values, as they can directly affect application behavior.</li>
                <li><strong>Description:</strong> Explains the purpose and expected format of the setting.</li>
            </ul>
            <div class="mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                <p class="font-bold text-xs uppercase tracking-wider text-blue-800 mb-2">Known System Keys / Examples</p>
                <ul class="list-disc list-inside text-sm text-blue-900 space-y-1">
                    <li><code class="font-bold">default_meeting_duration</code>: Sets the default duration for new meeting requests (in minutes). <em>Example: 60</em></li>
                    <li><code class="font-bold">office_start_hour</code>: (Optional) Start of operational hours (0-23). <em>Example: 7</em></li>
                    <li><code class="font-bold">office_end_hour</code>: (Optional) End of operational hours (0-23). <em>Example: 18</em></li>
                </ul>
            </div>
            <p class="mt-2 text-sm font-semibold">Caution: Incorrectly modifying configurations can lead to application errors. Only change settings if you understand their purpose.</p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Quick Settings Section --}}
        <div class="bg-white shadow-md rounded-xl p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">
                <i class="fas fa-toggle-on mr-2 text-blue-600"></i>
                Quick Settings
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
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Save Quick Settings
                    </button>
                </div>
            </form>
        </div>

        {{-- All Configurations Table --}}
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-cog mr-2 text-gray-600"></i>
            All Configurations
        </h2>

        <div class="mb-6 flex justify-between items-center">
            <form action="{{ route('settings.configurations.index') }}" method="GET" class="flex items-center space-x-2">
                <input type="text" name="search" placeholder="Search by Key..." value="{{ request('search') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('settings.configurations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Clear
                    </a>
                @endif
            </form>
            <a href="{{ route('settings.configurations.create') }}" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                Add New Configuration
            </a>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($configurations as $config)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $config->key }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $config->value }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $config->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <a href="{{ route('settings.configurations.edit', $config) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit Configuration">
                                        <i class="far fa-edit text-lg"></i>
                                    </a>
                                    <form action="{{ route('settings.configurations.destroy', $config) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this configuration?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Delete Configuration">
                                            <i class="far fa-trash-alt text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No configurations found. Click "Add New Configuration" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $configurations->links() }}
        </div>
    </div>
@endsection