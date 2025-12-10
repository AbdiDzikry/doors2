<div class="">
    <div class="flex flex-col sm:flex-row sm:space-x-4 mb-6">
        <div class="relative w-full sm:w-auto mb-4 sm:mb-0">
            <select wire:model.live="roleFilter" class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-md leading-tight focus:outline-none focus:bg-white focus:border-primary transition ease-in-out duration-150">
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
            </div>
        </div>
        <input wire:model.live="search" type="text" placeholder="Search users..." class="form-input block w-full px-4 py-2 text-base font-normal text-gray-700 bg-white bg-clip-padding border border-gray-300 rounded-md transition ease-in-out m-0 focus:text-gray-700 focus:bg-white focus:border-primary focus:outline-none">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">NPK</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Division</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Position</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Phone</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Roles</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="@if($loop->even) bg-gray-50 @else bg-white @endif">
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">{{ $user->name }}</td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">{{ $user->email }}</td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">{{ $user->npk }}</td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">{{ $user->division }}</td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">{{ $user->department }}</td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">{{ $user->position }}</td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">{{ $user->phone }}</td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">{{ implode(', ', $user->getRoleNames()->toArray()) }}</td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">
                            <a href="{{ route('master.users.edit', $user) }}" class="text-primary hover:text-primary-dark mr-3">Edit</a>
                            <form action="{{ route('master.users.destroy', $user) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>