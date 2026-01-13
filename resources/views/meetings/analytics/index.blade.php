@extends('layouts.master')

@section('title', $isSuperAdmin ? 'Meeting Analytics' : 'My Analytics')

@section('content')
<div class="container-fluid px-6 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $isSuperAdmin ? 'Meeting Analytics' : 'My Analytics' }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $isSuperAdmin ? 'Insights into meeting room usage and department trends.' : 'Your personal meeting activity and participation insights.' }}
            </p>
        </div>
        <div class="mt-4 md:mt-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                <i class="fas fa-chart-line mr-1.5"></i> Live Data
            </span>
            <button type="button" 
                onclick="Livewire.dispatch('openAnalyticsModal', { 
                    filters: {
                        startDate: '{{ $startDate->format('Y-m-d') }}',
                        endDate: '{{ $endDate->format('Y-m-d') }}',
                        division: '{{ request('division') }}',
                        department: '{{ request('department') }}'
                    }
                })"
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 transition-colors cursor-pointer">
                <i class="fas fa-list mr-1.5"></i> View Details
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-8" x-data='{ 
        filter: @json($filter, JSON_HEX_APOS),
        division: @json(request("division"), JSON_HEX_APOS),
        department: @json(request("department"), JSON_HEX_APOS),
        open: false,
        openDivision: false,
        openDepartment: false,
        options: {
            "week": "Weekly View", 
            "month": "Monthly View", 
            "custom": "Custom Range"
        },
        divisions: @json($divisions ?? [], JSON_HEX_APOS),
        departments: @json($departments ?? [], JSON_HEX_APOS),
        get activeLabel() { return this.options[this.filter] },
        get activeDivisionLabel() { return this.division ? this.division : "All Divisions" },
        get activeDepartmentLabel() { return this.department ? this.department : "All Departments" }
    }'>
        <form action="{{ route('meeting.analytics.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 items-end">
            
            <!-- Filter Type -->
            <div class="w-full">
                <label for="filter" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Period View</label>
                <div class="relative" @click.away="open = false">
                    <input type="hidden" name="filter" x-model="filter">
                    
                    <button type="button" @click="open = !open" 
                        class="relative w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-4 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 text-sm transition-all duration-200"
                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                        <span class="block truncate" x-text="activeLabel"></span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                        </span>
                    </button>

                    <div x-show="open" 
                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none text-sm border border-green-500/30"
                        style="display: none;">
                        <template x-for="(label, value) in options" :key="value">
                            <div @click="filter = value; open = false;"
                                 class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150"
                                 :class="{ 'text-green-900 bg-green-50': filter == value, 'text-gray-900 hover:bg-green-50 hover:text-green-700': filter != value }">
                                <span class="block truncate font-medium" :class="{ 'font-semibold': filter == value, 'font-normal': filter != value }" x-text="label"></span>
                                <span x-show="filter == value" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                    <i class="fas fa-check text-xs"></i>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Single Date Picker -->
            <div x-show="filter !== 'custom'" class="w-full">
                <label for="date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Select Date</label>
                <div class="relative" x-data="datePicker({ value: '{{ $date->format('Y-m-d') }}' })">
                    <input type="hidden" name="date" x-model="value">
                    
                    <button type="button" @click="open = !open" 
                        class="relative w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-4 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 text-sm transition-all duration-200"
                        :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                        <span class="block truncate" x-text="formattedDate"></span>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                             <i class="far fa-calendar text-sm"></i>
                        </div>
                    </button>

                    <div x-show="open" @click.away="open = false" 
                        class="absolute z-10 mt-1 w-64 bg-white shadow-lg rounded-xl p-4 text-sm border border-green-500/30"
                        style="display: none;">
                        
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span x-text="months[month]" class="text-lg font-bold text-gray-800"></span>
                                <span x-text="year" class="ml-1 text-lg text-gray-600 font-normal"></span>
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
                                         :class="{ 'bg-blue-500 text-white': isSelected(date), 'text-gray-700 hover:bg-blue-100': !isSelected(date), 'bg-blue-100': isToday(date) && !isSelected(date) }"
                                    ></div>
                                </div>
                            </template>
                        </div>
                        
                         <div class="flex justify-between mt-2 pt-2 border-t border-gray-100">
                            <button type="button" @click="value = ''; open = false" class="text-xs text-blue-500 hover:text-blue-700">Clear</button>
                            <button type="button" @click="init(); open = false" class="text-xs text-blue-500 hover:text-blue-700">Today</button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Custom Range Inputs -->
            <template x-if="filter === 'custom'">
                <div class="contents">
                    <div class="w-full">
                        <label for="start_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Start Date</label>
                        <div class="relative" x-data="datePicker({ value: '{{ isset($startDate) ? $startDate->format('Y-m-d') : today()->startOfMonth()->format('Y-m-d') }}' })">
                            <input type="hidden" name="start_date" x-model="value">
                            
                            <button type="button" @click="open = !open" 
                                class="relative w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-4 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 text-sm transition-all duration-200"
                                :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                <span class="block truncate" x-text="formattedDate"></span>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <i class="far fa-calendar-alt text-sm"></i>
                                </div>
                            </button>

                            <div x-show="open" @click.away="open = false" 
                                class="absolute z-10 mt-1 w-64 bg-white shadow-lg rounded-xl p-4 text-sm border border-green-500/30"
                                style="display: none;">
                                
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <span x-text="months[month]" class="text-lg font-bold text-gray-800"></span>
                                        <span x-text="year" class="ml-1 text-lg text-gray-600 font-normal"></span>
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
                                                :class="{ 'bg-blue-500 text-white': isSelected(date), 'text-gray-700 hover:bg-blue-100': !isSelected(date), 'bg-blue-100': isToday(date) && !isSelected(date) }"
                                            ></div>
                                        </div>
                                    </template>
                                </div>
                                
                                <div class="flex justify-between mt-2 pt-2 border-t border-gray-100">
                                    <button type="button" @click="value = ''; open = false" class="text-xs text-blue-500 hover:text-blue-700">Clear</button>
                                    <button type="button" @click="init(); open = false" class="text-xs text-blue-500 hover:text-blue-700">Today</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full">
                        <label for="end_date" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">End Date</label>
                         <div class="relative" x-data="datePicker({ value: '{{ isset($endDate) ? $endDate->format('Y-m-d') : today()->endOfMonth()->format('Y-m-d') }}' })">
                            <input type="hidden" name="end_date" x-model="value">
                            
                            <button type="button" @click="open = !open" 
                                class="relative w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-4 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 text-sm transition-all duration-200"
                                :class="{ 'border-green-500 ring-1 ring-green-500': open }">
                                <span class="block truncate" x-text="formattedDate"></span>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <i class="far fa-calendar-check text-sm"></i>
                                </div>
                            </button>

                            <div x-show="open" @click.away="open = false" 
                                class="absolute z-10 mt-1 w-64 bg-white shadow-lg rounded-xl p-4 text-sm border border-green-500/30"
                                style="display: none;">
                                
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <span x-text="months[month]" class="text-lg font-bold text-gray-800"></span>
                                        <span x-text="year" class="ml-1 text-lg text-gray-600 font-normal"></span>
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
                                                :class="{ 'bg-blue-500 text-white': isSelected(date), 'text-gray-700 hover:bg-blue-100': !isSelected(date), 'bg-blue-100': isToday(date) && !isSelected(date) }"
                                            ></div>
                                        </div>
                                    </template>
                                </div>
                                
                                <div class="flex justify-between mt-2 pt-2 border-t border-gray-100">
                                    <button type="button" @click="value = ''; open = false" class="text-xs text-blue-500 hover:text-blue-700">Clear</button>
                                    <button type="button" @click="init(); open = false" class="text-xs text-blue-500 hover:text-blue-700">Today</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Admin Filters -->
            @if($isSuperAdmin)
                <!-- Division Filter -->
                <div class="w-full">
                     <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Division</label>
                     <div class="relative" @click.away="openDivision = false">
                        <input type="hidden" name="division" x-model="division">
                        
                        <button type="button" @click="openDivision = !openDivision" 
                            class="relative w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-4 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 text-sm transition-all duration-200"
                            :class="{ 'border-green-500 ring-1 ring-green-500': openDivision }">
                            <span class="block truncate" x-text="activeDivisionLabel"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': openDivision }"></i>
                            </span>
                        </button>

                        <div x-show="openDivision" 
                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none text-sm border border-green-500/30"
                            style="display: none;">
                            <!-- Option: All Divisions -->
                            <div @click="division = ''; openDivision = false;"
                                class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150"
                                :class="{ 'text-green-900 bg-green-50': division == '', 'text-gray-900 hover:bg-green-50 hover:text-green-700': division != '' }">
                                <span class="block truncate font-medium" :class="{ 'font-semibold': division == '', 'font-normal': division != '' }">All Divisions</span>
                                <span x-show="division == ''" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                    <i class="fas fa-check text-xs"></i>
                                </span>
                            </div>
                            <!-- Dynamic Options -->
                            <template x-for="divName in divisions" :key="divName">
                                <div @click="division = divName; openDivision = false;"
                                    class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150"
                                    :class="{ 'text-green-900 bg-green-50': division == divName, 'text-gray-900 hover:bg-green-50 hover:text-green-700': division != divName }">
                                    <span class="block truncate font-medium" :class="{ 'font-semibold': division == divName, 'font-normal': division != divName }" x-text="divName"></span>
                                    <span x-show="division == divName" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                        <i class="fas fa-check text-xs"></i>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Department Filter -->
                 <div class="w-full">
                     <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Department</label>
                     <div class="relative" @click.away="openDepartment = false">
                        <input type="hidden" name="department" x-model="department">
                        
                        <button type="button" @click="openDepartment = !openDepartment" 
                            class="relative w-full bg-gray-50 border border-gray-200 rounded-lg shadow-sm pl-4 pr-10 py-2.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 text-sm transition-all duration-200"
                            :class="{ 'border-green-500 ring-1 ring-green-500': openDepartment }">
                            <span class="block truncate" x-text="activeDepartmentLabel"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'transform rotate-180': openDepartment }"></i>
                            </span>
                        </button>

                        <div x-show="openDepartment" 
                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none text-sm border border-green-500/30"
                            style="display: none;">
                            <!-- Option: All Departments -->
                            <div @click="department = ''; openDepartment = false;"
                                class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150"
                                :class="{ 'text-green-900 bg-green-50': department == '', 'text-gray-900 hover:bg-green-50 hover:text-green-700': department != '' }">
                                <span class="block truncate font-medium" :class="{ 'font-semibold': department == '', 'font-normal': department != '' }">All Departments</span>
                                <span x-show="department == ''" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                    <i class="fas fa-check text-xs"></i>
                                </span>
                            </div>
                            <!-- Dynamic Options -->
                            <template x-for="deptName in departments" :key="deptName">
                                <div @click="department = deptName; openDepartment = false;"
                                    class="cursor-pointer select-none relative py-2 pl-4 pr-9 transition-colors duration-150"
                                    :class="{ 'text-green-900 bg-green-50': department == deptName, 'text-gray-900 hover:bg-green-50 hover:text-green-700': department != deptName }">
                                    <span class="block truncate font-medium" :class="{ 'font-semibold': department == deptName, 'font-normal': department != deptName }" x-text="deptName"></span>
                                    <span x-show="department == deptName" class="absolute inset-y-0 right-0 flex items-center pr-4 text-green-600">
                                        <i class="fas fa-check text-xs"></i>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="w-full">
                     <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Search</label>
                     <input type="text" name="search" value="{{ request('search') }}" placeholder="Search User/Topic..." class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block">
                </div>
            @endif

            <!-- Action Button -->
            <div class="w-full">
                 <!-- Spacing/Label filler only if really needed, but button aligns to bottom due to items-end -->
                <button type="submit" class="w-full px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all duration-200 flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i> Apply
                </button>
            </div>
        </form>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Busy Hours -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Busy Hours</h3>
                    <p class="text-xs text-gray-500">Peak meeting times during the day</p>
                </div>
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                    <i class="far fa-clock"></i>
                </div>
            </div>
            <div class="p-6 relative h-80">
                <canvas id="busyHoursChart"></canvas>
            </div>
        </div>

        <!-- Meeting Status -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Status Distribution</h3>
                    <p class="text-xs text-gray-500">Overview of meeting outcomes</p>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                     <i class="fas fa-tasks"></i>
                </div>
            </div>
            <div class="p-6 relative h-80 flex items-center justify-center">
                <canvas id="meetingStatusDistributionChart"></canvas>
            </div>
        </div>

        <!-- Room Usage -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Room Usage</h3>
                    <p class="text-xs text-gray-500">Most frequently booked rooms</p>
                </div>
                 <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                    <i class="far fa-building"></i>
                </div>
            </div>
            <div class="p-6 relative h-80 flex items-center justify-center">
                <canvas id="roomUsageChart"></canvas>
            </div>
        </div>

        <!-- Department Usage -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900">{{ $isSuperAdmin ? 'Department Usage' : 'Invited by Department' }}</h3>
                    <p class="text-xs text-gray-500">{{ $isSuperAdmin ? 'Bookings by department' : 'Departments of organizers who invited you' }}</p>
                </div>
                 <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="p-6 relative h-80 flex items-center justify-center">
                <canvas id="departmentUsageChart"></canvas>
            </div>
        </div>

        <!-- Peak Days -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Peak Days</h3>
                    <p class="text-xs text-gray-500">Meeting volume by day of week</p>
                </div>
                 <div class="p-2 bg-red-50 rounded-lg text-red-600">
                    <i class="far fa-calendar-alt"></i>
                </div>
            </div>
            <div class="p-6 relative h-80 flex items-center justify-center">
                <canvas id="peakDaysChart"></canvas>
            </div>
        </div>

        <!-- Meeting Duration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
            <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Meeting Duration</h3>
                    <p class="text-xs text-gray-500">Distribution by meeting length</p>
                </div>
                 <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                    <i class="far fa-hourglass"></i>
                </div>
            </div>
            <div class="p-6 relative h-80 flex items-center justify-center">
                <canvas id="meetingDurationChart"></canvas>
            </div>
        </div>

    </div>
</div>
@livewire('analytics.meeting-details-modal')
@endsection

@push('scripts')
<script>


    document.addEventListener('DOMContentLoaded', function () {

        // Global Chart Defaults
        Chart.defaults.font.family = "'Inter', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', sans-serif";
        Chart.defaults.color = '#64748b'; // Tailwind gray-500
        Chart.defaults.scale.grid.borderColor = '#f1f5f9'; // Tailwind gray-100
        Chart.defaults.scale.grid.color = '#f1f5f9'; // Tailwind gray-100

        // Brand Palette
        const colors = {
            primary: '#10b981', // green-500
            primaryLight: 'rgba(16, 185, 129, 0.2)',
            secondary: '#3b82f6', // blue-500
            secondaryLight: 'rgba(59, 130, 246, 0.2)',
            tertiary: '#f59e0b', // amber-500
            tertiaryLight: 'rgba(245, 158, 11, 0.2)',
            quaternary: '#6366f1', // indigo-500
            quaternaryLight: 'rgba(99, 102, 241, 0.2)',
            danger: '#ef4444', // red-500
            dangerLight: 'rgba(239, 68, 68, 0.2)',
            gray: '#94a3b8',
            grayLight: 'rgba(148, 163, 184, 0.2)'
        };

        const chartPalette = [
            '#10b981', '#3b82f6', '#f59e0b', '#6366f1', '#ec4899', '#8b5cf6', '#14b8a6', '#f43f5e'
        ];

        // 1. Busy Hours Chart (Bar)
        const busyHoursCtx = document.getElementById('busyHoursChart').getContext('2d');
        new Chart(busyHoursCtx, {
            type: 'bar',
            data: {
                labels: @json(array_keys($busyHours)),
                datasets: [{
                    label: 'Calculated Meetings',
                    data: @json(array_values($busyHours)),
                    backgroundColor: colors.primary,
                    borderRadius: 6,
                    barThickness: 20,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                let hour = context[0].label;
                                // Ensure 2 digits for hour
                                hour = hour.toString().padStart(2, '0');
                                return hour + ':00 WIB';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 2] },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // 2. Meeting Status Distribution (Doughnut - cleaner than polar usually)
        const statusCtx = document.getElementById('meetingStatusDistributionChart').getContext('2d');
        
        // Map status keys to specific colors if possible, else use palette
        const statusData = @json($meetingStatusDistribution);
        const statusKeys = Object.keys(statusData);
        const statusValues = Object.values(statusData);
        
        // Simple color mapper helper
        const getStatusColor = (status) => {
            switch(status.toLowerCase()) {
                case 'completed': return colors.primary;
                case 'scheduled': return colors.secondary;
                case 'cancelled': return colors.danger;
                case 'ongoing': return colors.tertiary;
                default: return colors.gray;
            }
        };

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusKeys.map(k => k.charAt(0).toUpperCase() + k.slice(1)), // Capitalize
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusKeys.map(k => getStatusColor(k)),
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20 }
                    }
                }
            }
        });

        // 3. Room Usage (Bar - Horizontal for better readability of names)
        const roomCtx = document.getElementById('roomUsageChart').getContext('2d');
        new Chart(roomCtx, {
            type: 'bar',
            indexAxis: 'y', // Horizontal bars
            data: {
                labels: @json(array_keys($roomUsage)),
                datasets: [{
                    label: 'Bookings',
                    data: @json(array_values($roomUsage)),
                    backgroundColor: colors.quaternary,
                    borderRadius: 4,
                    barThickness: 16
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 2] },
                        ticks: { stepSize: 1 }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });

        // 4. Department Usage (Pie)
        const deptCtx = document.getElementById('departmentUsageChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'pie',
            data: {
                labels: @json(array_keys($departmentUsage)),
                datasets: [{
                    data: @json(array_values($departmentUsage)),
                    backgroundColor: chartPalette,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { usePointStyle: true, padding: 15, font: { size: 11 } }
                    }
                }
            }
        });


        // 5. Peak Days Chart (Bar)
        const peakDaysCtx = document.getElementById('peakDaysChart').getContext('2d');
        new Chart(peakDaysCtx, {
            type: 'bar',
            data: {
                labels: @json(array_keys($peakDays)),
                datasets: [{
                    label: 'Meetings',
                    data: @json(array_values($peakDays)),
                    backgroundColor: colors.danger,
                    borderRadius: 6,
                    barThickness: 20,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 2] },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        // 6. Meeting Duration Chart (Pie)
        const durationCtx = document.getElementById('meetingDurationChart').getContext('2d');
        new Chart(durationCtx, {
            type: 'pie',
            data: {
                labels: @json(array_keys($meetingDuration)),
                datasets: [{
                    data: @json(array_values($meetingDuration)),
                    backgroundColor: [colors.primary, colors.tertiary, colors.danger], // Green, Amber, Red scheme
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { usePointStyle: true, padding: 15, font: { size: 11 } }
                    }
                }
            }
        });
    });
</script>
@endpush
