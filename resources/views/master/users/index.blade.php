@extends('layouts.master')

@section('title', 'User Management')

@section('content')
<div class="bg-gray-50">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Page Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Manage users and their roles within the system.
                            <span class="ml-2 px-2 py-0.5 bg-gray-100 text-[10px] text-gray-500 border border-gray-200 rounded-full font-medium italic">
                            <i class="fas fa-clock mr-1"></i>Auto-sync: daily
                            @if(isset($syncStatus))
                                <span class="ml-2 px-1.5 py-0.5 rounded text-[10px] uppercase font-bold {{ $syncStatus === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                                    @if($syncStatus === 'success')
                                        <i class="fas fa-check-circle mr-1"></i>Success
                                    @else
                                        <i class="fas fa-exclamation-circle mr-1"></i>Failed
                                    @endif
                                </span>
                                @if(isset($syncLastRun))
                                    <span class="text-xs text-gray-400 ml-1" title="Last run at {{ $syncLastRun }}">
                                        ({{ \Carbon\Carbon::parse($syncLastRun)->diffForHumans() }})
                                    </span>
                                @endif
                            @endif
                            </span>
                        </p>
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <a href="{{ route('master.users.create') }}"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Add New
                        </a>
                        
                        <div x-data="{ showImportModal: false }">
                            <button @click="showImportModal = true"
                                class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                <i class="fas fa-file-import mr-2"></i>
                                Import Users
                            </button>
                            <!-- Import Modal -->
                            <div x-show="showImportModal" x-cloak
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                @keydown.escape.window="showImportModal = false">
                                <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="showImportModal = false">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Import New Users</h3>
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

                        <div x-data="{ showContactModal: false }">
                            <button @click="showContactModal = true"
                                class="flex items-center justify-center px-4 py-2 text-sm font-medium text-indigo-700 bg-indigo-100 rounded-lg hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <i class="fas fa-address-book mr-2"></i>
                                Update Contacts
                            </button>
                            <!-- Contact Update Modal -->
                            <div x-show="showContactModal" x-cloak
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                @keydown.escape.window="showContactModal = false">
                                <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="showContactModal = false">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Update Employee Contacts</h3>
                                    <p class="text-sm text-gray-500 mb-4">
                                        Update Email and Phone for existing users by matching Employee No. (NPK).
                                        <a href="{{ route('master.users.contact-template') }}" class="text-indigo-600 hover:text-indigo-800 font-medium underline">
                                            Download existing data template
                                        </a>
                                    </p>
                                    <form action="{{ route('master.users.import-contacts') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div>
                                            <label for="contact_file" class="block text-sm font-medium text-gray-700">Choose file to import</label>
                                            <input type="file" name="file" id="contact_file"
                                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                                required>
                                        </div>
                                        <div class="mt-6 flex justify-end space-x-3">
                                            <button type="button" @click="showContactModal = false"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none">
                                                Update Contacts
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('master.users.export') }}"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                            <i class="fas fa-file-export mr-2"></i>
                            Export
                        </a>

                        <form action="{{ route('master.users.sync') }}" method="POST" class="inline" 
                            x-data="{ loading: false }" 
                            @submit="if(confirm('Mulai sinkronisasi data karyawan dari API? Proses ini mungkin memakan waktu beberapa detik.')) { loading = true } else { event.preventDefault() }">
                            @csrf
                            <button type="submit" 
                                :disabled="loading"
                                class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="!loading">
                                    <div class="flex items-center">
                                        <i class="fas fa-sync-alt mr-2"></i>
                                        Sync API
                                    </div>
                                </template>
                                <template x-if="loading">
                                    <div class="flex items-center">
                                        <i class="fas fa-circle-notch fa-spin mr-2"></i>
                                        Syncing...
                                    </div>
                                </template>
                            </button>
                        </form>
                        
                        <a href="{{ route('master.users.template') }}"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
                            title="Download Template">
                            <i class="fas fa-file-download"></i>
                        </a>
                    </div>
                </div>

                <!-- Filter and Search Form -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 mb-6">
                    <form action="{{ route('master.users.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="search" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by name, email, NPK..." class="block w-full rounded-lg border-gray-200 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                            </div>
                            <div>
                                <label for="role" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Filter by Role</label>
                                <div class="relative" x-data="{ 
                                    open: false, 
                                    selected: '{{ request('role') }}',
                                    get label() { return this.selected || 'All Roles' }
                                }" @click.away="open = false">
                                    <input type="hidden" name="role" x-model="selected">
                                    <button type="button" @click="open = !open" 
                                        class="relative w-full bg-white border border-gray-200 rounded-lg shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 text-sm"
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
                            <div class="flex items-end space-x-2">
                                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm">
                                    Apply Filter
                                </button>
                                <a href="{{ route('master.users.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors text-sm">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
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
                                    <tr class="hover:bg-gray-50 transition-colors">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <a href="{{ route('master.users.edit', $user) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                                    <i class="far fa-edit text-lg"></i>
                                                </a>
                                                <form action="{{ route('master.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                                        <i class="far fa-trash-alt text-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            <div class="flex flex-col items-center justify-center py-4">
                                                 <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                                                <p>No users found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
