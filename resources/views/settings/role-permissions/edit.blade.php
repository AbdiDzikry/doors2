@extends('layouts.master')

@section('title', 'Edit Role Permissions')

@section('content')
    <div class="container-fluid px-6 py-4">
        <div class="py-4">
            <h1 class="text-2xl font-bold text-gray-800">Edit Role: {{ $role->name }}</h1>
            <p class="text-sm text-gray-600">Update role name and assign/revoke permissions.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('settings.role-permissions.update', ['role' => $role->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm @error('name') border-red-500 @enderror" placeholder="e.g., Manager">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Permissions:</label>
                    
                    {{-- Categorized Permissions --}}
                    @php
                        $permissionGroups = [
                            'System Settings' => ['manage settings', 'manage configurations', 'manage roles and permissions'],
                            'Master Data' => ['manage master data', 'manage users', 'manage rooms', 'manage pantry', 'manage external participants', 'manage priority guests'],
                            'Meeting & Room' => ['access meeting room', 'book rooms', 'view analytics'],
                            'General Affair' => ['manage assets'],
                            'Dashboards' => ['access pantry dashboard', 'access tablet mode'],
                        ];
                        
                        // Define parent-child relationships
                        $parentPermissions = ['manage settings', 'manage master data', 'access meeting room', 'manage assets'];
                        $permissionDependencies = [
                            'manage configurations' => 'manage settings',
                            'manage roles and permissions' => 'manage settings',
                            'manage users' => 'manage master data',
                            'manage rooms' => 'manage master data',
                            'manage external participants' => 'manage master data',
                            'manage priority guests' => 'manage master data',
                            'book rooms' => 'access meeting room',
                            'view analytics' => 'access meeting room',
                        ];
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="permissions-container">
                        @foreach ($permissionGroups as $groupName => $groupPerms)
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <h5 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-3">{{ $groupName }}</h5>
                                <div class="space-y-2">
                                    @foreach ($permissions as $permission)
                                        @if (in_array($permission->name, $groupPerms))
                                            @php
                                                $isParent = in_array($permission->name, $parentPermissions);
                                                $isChecked = in_array($permission->name, old('permissions', $rolePermissions));
                                            @endphp
                                            <div class="flex items-center {{ $isParent ? 'bg-white border border-green-200 rounded px-2 py-1.5' : '' }}">
                                                <input 
                                                    type="checkbox" 
                                                    name="permissions[]" 
                                                    id="permission_{{ $permission->id }}" 
                                                    value="{{ $permission->id }}" 
                                                    data-permission-name="{{ $permission->name }}"
                                                    @if($isParent) data-is-parent="true" @endif
                                                    @if(isset($permissionDependencies[$permission->name])) data-requires="{{ $permissionDependencies[$permission->name] }}" @endif
                                                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded permission-checkbox"
                                                    {{ $isChecked ? 'checked' : '' }}>
                                                <label for="permission_{{ $permission->id }}" class="ml-2 block text-sm {{ $isParent ? 'font-semibold text-gray-800' : 'text-gray-700' }}">
                                                    {{ $permission->name }}
                                                    @if($isParent)
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">PARENT</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @error('permissions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end">
                    <a href="{{ route('settings.role-permissions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript for managing parent-child permission dependencies --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.permission-checkbox');
            
            // Initialize: disable children if parent not checked
            updateChildrenState();
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const isParent = this.dataset.isParent === 'true';
                    const requiredParent = this.dataset.requires;
                    
                    if (isParent) {
                        // This is a parent checkbox - enable/disable its children
                        const parentName = this.dataset.permissionName;
                        toggleChildren(parentName, this.checked);
                    } else if (this.checked && requiredParent) {
                        // Child was checked - auto-check parent if not already
                        const parentCheckbox = document.querySelector(`[data-permission-name="${requiredParent}"]`);
                        if (parentCheckbox && !parentCheckbox.checked) {
                            parentCheckbox.checked = true;
                            toggleChildren(requiredParent, true);
                            showNotification(`Auto-enabled: "${requiredParent}" (required parent)`, 'success');
                        }
                    }
                });
            });
            
            function toggleChildren(parentName, enable) {
                // Find all children of this parent
                const children = document.querySelectorAll(`[data-requires="${parentName}"]`);
                children.forEach(child => {
                    child.disabled = !enable;
                    if (!enable) {
                        child.checked = false; // Uncheck when disabling
                    }
                    // Update visual state
                    const label = child.parentElement.querySelector('label');
                    if (label) {
                        if (enable) {
                            label.classList.remove('text-gray-400', 'cursor-not-allowed');
                            label.classList.add('text-gray-700');
                        } else {
                            label.classList.remove('text-gray-700');
                            label.classList.add('text-gray-400', 'cursor-not-allowed');
                        }
                    }
                });
            }
            
            function updateChildrenState() {
                // On page load, ensure children match parent state
                const parentCheckboxes = document.querySelectorAll('[data-is-parent="true"]');
                parentCheckboxes.forEach(parent => {
                    const parentName = parent.dataset.permissionName;
                    toggleChildren(parentName, parent.checked);
                });
            }
            
            function showNotification(message, type = 'success') {
                const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 ${bgColor} border px-4 py-3 rounded shadow-lg z-50`;
                notification.innerHTML = `<strong>${message}</strong>`;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
        });
    </script>
@endsection