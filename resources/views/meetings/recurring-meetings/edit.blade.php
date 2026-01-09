@extends('layouts.master')

@section('title', 'Edit Recurring Meeting')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Edit Recurring Meeting</h1>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form action="{{ route('meeting.recurring-meetings.update', $recurringMeeting->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="frequency" class="block text-gray-700 text-sm font-bold mb-2">Frequency:</label>
                <div class="relative" x-data="{
                    open: false,
                    selected: '{{ $recurringMeeting->frequency }}',
                    options: { 'daily': 'Daily', 'weekly': 'Weekly', 'monthly': 'Monthly' },
                    get label() { return this.options[this.selected] }
                }" @click.away="open = false">
                    <input type="hidden" name="frequency" x-model="selected" id="frequency">
                    
                    <button type="button" @click="open = !open" 
                        class="relative w-full bg-white border border-gray-300 rounded shadow appearance-none py-2 px-3 text-left cursor-pointer focus:outline-none focus:shadow-outline leading-tight text-gray-700"
                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                        <span class="block truncate" x-text="label"></span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                        </span>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-green-500/30"
                        style="display: none;">
                        @foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $val => $text)
                            <div @click="selected = '{{ $val }}'; open = false"
                                 class="cursor-pointer select-none relative py-2 pl-3 pr-9 transition-colors duration-150"
                                 :class="{ 'text-green-900 bg-green-50': selected == '{{ $val }}', 'text-gray-900 hover:bg-green-50 hover:text-green-700': selected != '{{ $val }}' }">
                                <span class="block truncate font-medium" :class="{ 'font-bold': selected == '{{ $val }}' }">{{ $text }}</span>
                                <span x-show="selected == '{{ $val }}'" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                    <i class="fas fa-check text-xs"></i>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="ends_at" class="block text-gray-700 text-sm font-bold mb-2">Ends At:</label>
                <div class="relative group" x-data="datePicker({ value: '{{ \Carbon\Carbon::parse($recurringMeeting->ends_at)->format('Y-m-d') }}' })">
                    <input type="hidden" name="ends_at" x-model="value" id="ends_at">
                    
                    <button type="button" @click="open = !open" 
                        class="relative w-full bg-white border border-gray-300 rounded shadow appearance-none py-2 px-3 text-left cursor-pointer focus:outline-none focus:shadow-outline leading-tight text-gray-700"
                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                        <span class="block truncate" x-text="formattedDate || 'Select Date'"></span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-gray-400 group-hover:text-green-600 transition-colors">
                            <i class="fas fa-calendar-alt text-xs"></i>
                        </span>
                    </button>

                    <!-- Calendar Dropdown -->
                    <div x-show="open" @click.away="open = false" 
                        class="absolute z-10 mt-1 w-64 bg-white shadow-lg rounded-xl p-4 text-sm border border-green-500/30 bottom-0 left-0 lg:bottom-auto lg:top-full"
                        style="display: none;">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span x-text="months[month]" class="text-base font-bold text-gray-800"></span>
                                <span x-text="year" class="ml-1 text-base text-gray-600 font-normal"></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" class="transition-colors hover:bg-gray-100 rounded-lg p-1" @click="prevMonth">
                                    <i class="fas fa-arrow-up text-gray-600"></i>
                                </button>
                                <button type="button" class="transition-colors hover:bg-gray-100 rounded-lg p-1" @click="nextMonth">
                                    <i class="fas fa-arrow-down text-gray-600"></i>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 mb-2">
                            <template x-for="(day, index) in days" :key="index">
                                <div class="px-0.5">
                                    <div x-text="day" class="text-xs font-medium text-center text-gray-800"></div>
                                </div>
                            </template>
                        </div>
                        <div class="grid grid-cols-7">
                            <template x-for="blank in blankdays">
                                <div class="text-center border p-1 border-transparent text-sm"></div>
                            </template>
                            <template x-for="(date, dateIndex) in no_of_days" :key="dateIndex">
                                <div class="px-0.5 mb-1">
                                    <div @click="getDateValue(date)"
                                        x-text="date"
                                        class="cursor-pointer text-center text-sm rounded-lg leading-7 transition-colors duration-150 ease-in-out"
                                        :class="{ 'bg-green-500 text-white': isSelected(date), 'text-gray-700 hover:bg-green-100': !isSelected(date), 'bg-green-100': isToday(date) && !isSelected(date) }"
                                    ></div>
                                </div>
                            </template>
                        </div>
                        <div class="flex justify-between mt-2 pt-2 border-t border-gray-100">
                            <button type="button" @click="value = ''; open = false" class="text-xs text-green-500 hover:text-green-700">Clear</button>
                            <button type="button" @click="init(); open = false" class="text-xs text-green-500 hover:text-green-700">Today</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update</button>
                <a href="{{ route('meeting.recurring-meetings.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Cancel</a>
            </div>
        </form>
    </div>
@endsection