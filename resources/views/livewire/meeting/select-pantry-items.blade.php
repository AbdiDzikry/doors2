<div>
    <!-- Dropdown for adding Pantry Items -->
    <div class="mb-4 flex items-center space-x-2">
        <div class="relative w-full" x-data="{ 
            open: false, 
            selected: @entangle('itemToAdd'),
            selectedName: 'Select a pantry item'
        }" @click.away="open = false">
            <button type="button" @click="open = !open" 
                class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200"
                :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                <span class="block truncate" x-text="selected ? selectedName : 'Select a pantry item'"></span>
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                </span>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                style="display: none;">
                @foreach ($allItems as $item)
                    <div @click="selected = '{{ $item->id }}'; selectedName = '{{ addslashes($item->name) }}'; open = false; $wire.set('itemToAdd', '{{ $item->id }}')"
                         class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                         :class="{ 'text-green-900 bg-green-50': selected == '{{ $item->id }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected != '{{ $item->id }}' }">
                        <span class="block truncate font-medium" :class="{ 'font-semibold': selected == '{{ $item->id }}' }">{{ $item->name }}</span>
                        <span x-show="selected == '{{ $item->id }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                            <i class="fas fa-check text-xs"></i>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        <button type="button" wire:click="addPantryItem" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Add
        </button>
    </div>

    <!-- Selected Items -->
    <div class="space-y-3">
        <h3 class="text-md font-semibold text-gray-700">Selected Items</h3>
        @if (!empty($orders))
            @foreach ($orders as $index => $order)
                @php
                    // Get the pantry item's details from the pre-loaded collection
                    $item = $selectedItemsData->get($order['pantry_item_id']);
                @endphp
                @if ($item)
                    <div class="bg-white p-3 rounded-md shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-800">{{ $item->name }}</span>
                            <div class="flex items-center space-x-3">
                                <label for="quantity-{{ $index }}" class="text-sm">Qty:</label>
                                <input type="number" id="quantity-{{ $index }}" wire:model.live="orders.{{ $index }}.quantity" min="1" class="w-20 bg-white border border-gray-300 rounded-lg shadow-sm px-2 py-1 mx-2 text-center text-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                <button type="button" wire:click="removePantryItem({{ $index }})" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Conditionally show textarea for custom items --}}
                        @if ($item->name === $customItemName)
                            <div class="mt-3">
                                <label for="custom-{{ $index }}" class="sr-only">Custom Request Details</label>
                                <textarea id="custom-{{ $index }}" 
                                          wire:model.live="orders.{{ $index }}.custom_items" 
                                          class="block w-full bg-white border border-gray-300 rounded-lg shadow-sm px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 transition-all duration-200 placeholder-gray-400" 
                                          placeholder="Please specify your request (e.g., '2x Kopi Hitam, 1x Teh Manis')..."></textarea>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div class="text-center py-4 px-3 bg-gray-50 rounded-md">
                <p class="text-sm text-gray-500">No pantry items added yet.</p>
            </div>
        @endif
    </div>
</div>
