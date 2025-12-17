<div class="p-4">
    <div class="flex justify-between items-center mb-4 gap-2">
        <input type="text" wire:model.live="search" placeholder="Search external participants..." class="w-full bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-700 text-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
        <button type="button" wire:click="toggleCreateForm" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline whitespace-nowrap">
            {{ $showCreateForm ? 'Cancel' : 'Create New' }}
        </button>
    </div>

    <!-- Create New Participant Form -->
    @if ($showCreateForm)
        <div class="mb-6 p-4 border border-green-200 rounded-lg bg-green-50 shadow-sm animate-fade-in-down">
            <h4 class="font-bold text-gray-800 mb-3 text-sm">Create New External Participant</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-xs font-bold mb-1" for="newName">Name <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="newName" id="newName" class="w-full bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-700 text-sm focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all duration-200 @error('newName') border-red-500 @enderror">
                    @error('newName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                     <label class="block text-gray-700 text-xs font-bold mb-1" for="newCompany">Company <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="newCompany" id="newCompany" class="w-full bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-700 text-sm focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all duration-200 @error('newCompany') border-red-500 @enderror">
                    @error('newCompany') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-xs font-bold mb-1" for="newPhone">Phone <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="newPhone" id="newPhone" class="w-full bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-700 text-sm focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all duration-200 @error('newPhone') border-red-500 @enderror">
                    @error('newPhone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-xs font-bold mb-1" for="newEmail">Email</label>
                    <input type="email" wire:model="newEmail" id="newEmail" class="w-full bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-700 text-sm focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all duration-200 @error('newEmail') border-red-500 @enderror">
                    @error('newEmail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-xs font-bold mb-1" for="newAddress">Address</label>
                <textarea wire:model="newAddress" id="newAddress" rows="2" class="w-full bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-gray-700 text-sm focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition-all duration-200 @error('newAddress') border-red-500 @enderror"></textarea>
                @error('newAddress') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end">
                <button type="button" wire:click="createNewParticipant" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline text-sm">
                    Save & Add Guest
                </button>
            </div>
        </div>
    @endif

    @if ($this->search !== '')
        <div class="border rounded max-h-60 overflow-y-auto mb-4 bg-white">
            @forelse ($externalParticipants as $participant)
                <div class="flex items-center justify-between p-3 border-b last:border-b-0 hover:bg-gray-50">
                     <div class="flex flex-col">
                         <span class="font-medium text-sm text-gray-800">{{ $participant->name }}</span>
                         <span class="text-xs text-gray-500">{{ $participant->company }} • {{ $participant->email }}</span>
                    </div>
                    @if (!in_array($participant->id, $selectedParticipants))
                        <button type="button" wire:click="addParticipant({{ $participant->id }})" class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-semibold py-1 px-3 rounded transition-colors">Add</button>
                    @else
                        <span class="text-green-600 text-xs font-semibold bg-green-50 py-1 px-3 rounded-full border border-green-200">Added</span>
                    @endif
                </div>
            @empty
                <div class="p-4 text-center text-gray-500 text-sm">
                    No external participants found.
                </div>
            @endforelse
        </div>
    @endif

    <h3 class="font-bold mb-3 text-gray-800 text-sm">Selected Participants:</h3>
    @if ($selectedExternalParticipants->isNotEmpty())
        <div class="border rounded-lg max-h-52 overflow-y-auto bg-white shadow-sm">
            @foreach ($selectedExternalParticipants as $participant)
                <div class="flex items-center justify-between p-3 border-b last:border-b-0 hover:bg-gray-50 group">
                    <div class="flex items-center">
                         <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs uppercase mr-3">
                            {{ substr($participant->name, 0, 2) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900">{{ $participant->name }}</span>
                            <span class="text-xs text-gray-500">{{ $participant->company }}</span>
                        </div>
                    </div>
                    <button type="button" wire:click="removeParticipant({{ $participant->id }})" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50" title="Remove">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-gray-500 text-sm italic p-2 border border-dashed rounded text-center">No external participants selected.</div>
    @endif
</div>