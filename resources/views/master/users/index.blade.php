@extends('layouts.master')

@section('title', 'User Management')

@section('content')
    <div class="container-fluid px-6 py-4">
        <div class="py-4">
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <p class="text-sm text-gray-600">Manage users and their roles within the system.</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex space-x-2">
                <a href="{{ route('master.users.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Add New User
                </a>
                <div x-data="{ showImportModal: false }">
                    <button @click="showImportModal = true" type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Import Users
                    </button>
                    <!-- Import Modal -->
                    <div x-show="showImportModal" x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                        @keydown.escape.window="showImportModal = false">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="showImportModal = false">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Import Users</h3>
                            <form action="{{ route('master.users.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div>
                                    <label for="file" class="block text-sm font-medium text-gray-700">Choose file to import</label>
                                    <input type="file" name="file" id="file"
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                        required>
                                </div>
                                <div class="mt-6 flex justify-end space-x-3">
                                    <button type="button" @click="showImportModal = false"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none">
                                        Import Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <a href="{{ route('master.users.export') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Export Users
                </a>
                <a href="{{ route('master.users.template') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Download Template
                </a>
            </div>
        </div>

        <!-- Filter and Search Form -->
        <div class="bg-white shadow-md rounded-lg p-4 mb-6">
            <form action="{{ route('master.users.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by name, email, NPK..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Filter by Role</label>
                        <div class="relative" x-data="{ 
                            open: false, 
                            selected: '{{ request('role') }}',
                            get label() { return this.selected || 'All Roles' }
                        }" @click.away="open = false">
                            <input type="hidden" name="role" x-model="selected">
                            <button type="button" @click="open = !open" 
                                class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                <span class="block truncate" x-text="label"></span>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                                </span>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                                style="display: none;">
                                
                                <div @click="selected = ''; open = false"
                                     class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                     :class="{ 'text-green-900 bg-green-50': selected === '', 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected !== '' }">
                                    <span class="block truncate font-medium" :class="{ 'font-semibold': selected === '', 'font-normal': selected !== '' }">All Roles</span>
                                    <span x-show="selected === ''" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                        <i class="fas fa-check text-xs"></i>
                                    </span>
                                </div>

                                @foreach ($roles as $role)
                                    <div @click="selected = '{{ $role->name }}'; open = false"
                                         class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                         :class="{ 'text-green-900 bg-green-50': selected === '{{ $role->name }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected !== '{{ $role->name }}' }">
                                        <span class="block truncate font-medium" :class="{ 'font-semibold': selected === '{{ $role->name }}', 'font-normal': selected !== '{{ $role->name }}' }">{{ $role->name }}</span>
                                        <span x-show="selected === '{{ $role->name }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                            <i class="fas fa-check text-xs"></i>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Apply Filter
                        </button>
                        <a href="{{ route('master.users.index') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100 transition-colors duration-200">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 w-full h-full text-gray-700 font-bold">
                                <span>Full Name</span>
                                @if(request('sort_by', 'name') === 'name')
                                    <i class="fas fa-sort-{{ request('sort_direction', 'asc') === 'asc' ? 'up' : 'down' }} text-green-600"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400 transition-colors"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100 transition-colors duration-200">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'npk', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 w-full h-full text-gray-700 font-bold">
                                <span>NPK</span>
                                @if(request('sort_by') === 'npk')
                                    <i class="fas fa-sort-{{ request('sort_direction') === 'asc' ? 'up' : 'down' }} text-green-600"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400 transition-colors"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100 transition-colors duration-200">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'division', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 w-full h-full text-gray-700 font-bold">
                                <span>Division</span>
                                @if(request('sort_by') === 'division')
                                    <i class="fas fa-sort-{{ request('sort_direction') === 'asc' ? 'up' : 'down' }} text-green-600"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400 transition-colors"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100 transition-colors duration-200">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'department', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 w-full h-full text-gray-700 font-bold">
                                <span>Department</span>
                                @if(request('sort_by') === 'department')
                                    <i class="fas fa-sort-{{ request('sort_direction') === 'asc' ? 'up' : 'down' }} text-green-600"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400 transition-colors"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100 transition-colors duration-200">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'position', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 w-full h-full text-gray-700 font-bold">
                                <span>Position</span>
                                @if(request('sort_by') === 'position')
                                    <i class="fas fa-sort-{{ request('sort_direction') === 'asc' ? 'up' : 'down' }} text-green-600"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400 transition-colors"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100 transition-colors duration-200">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'email', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 w-full h-full text-gray-700 font-bold">
                                <span>Email</span>
                                @if(request('sort_by') === 'email')
                                    <i class="fas fa-sort-{{ request('sort_direction') === 'asc' ? 'up' : 'down' }} text-green-600"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400 transition-colors"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group hover:bg-gray-100 transition-colors duration-200">
                            <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'phone', 'sort_direction' => request('sort_direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center space-x-1 w-full h-full text-gray-700 font-bold">
                                <span>Phone</span>
                                @if(request('sort_by') === 'phone')
                                    <i class="fas fa-sort-{{ request('sort_direction') === 'asc' ? 'up' : 'down' }} text-green-600"></i>
                                @else
                                    <i class="fas fa-sort text-gray-300 group-hover:text-gray-400 transition-colors"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->npk ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->division ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->department ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->position ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->phone ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @foreach ($user->roles as $role)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('master.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center" title="Edit">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.268z"></path></svg>
                                </a>
                                <form action="{{ route('master.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 inline-flex items-center" title="Delete">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
@endsection
