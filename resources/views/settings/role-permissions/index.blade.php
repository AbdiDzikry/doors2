@extends('layouts.master')

@section('title', 'Roles & Permissions')

@section('content')
    <div class="container-fluid px-6 py-4">
        <div class="py-4">
            <h1 class="text-2xl font-bold text-gray-800">Roles & Permissions Management</h1>
            <p class="text-sm text-gray-600">Manage user roles and their associated permissions.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Roles List Card -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <h4 class="text-xl font-semibold text-gray-800 mb-4">Existing Roles</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($roles as $role)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $role->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @forelse ($role->permissions as $permission)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 mr-1 mb-1">
                                                {{ $permission->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400">No permissions assigned</span>
                                        @endforelse
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <a href="{{ route('settings.role-permissions.edit', $role->id) }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit Role">
                                                <i class="far fa-edit text-lg"></i>
                                            </a>
                                            <form action="{{ route('settings.role-permissions.destroy', $role->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this role? This will remove it from all users.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Delete Role">
                                                    <i class="far fa-trash-alt text-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No roles found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create New Role Card -->
            <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-6">
                <h4 class="text-xl font-semibold text-gray-800 mb-4">Create New Role</h4>
                <form action="{{ route('settings.role-permissions.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Role Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('name') border-red-500 @enderror" placeholder="e.g., Manager">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Assign Permissions (Optional)</label>
                        <div class="mt-2 grid grid-cols-1 gap-2">
                            @foreach ($permissions as $permission)
                                <div class="flex items-center">
                                    <input type="checkbox" name="permissions[]" id="permission_{{ $permission->id }}" value="{{ $permission->id }}" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                    <label for="permission_{{ $permission->id }}" class="ml-2 block text-sm text-gray-900">{{ $permission->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('permissions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection