
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Doors App | Dharma Polimetal</title>

        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path fill='%23089244' d='M416 0H96C60.7 0 32 28.7 32 64V448c0 35.3 28.7 64 64 64H416c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64zM288 288c-17.7 0-32 14.3-32 32s14.3 32 32 32s32-14.3 32-32s-14.3-32-32-32z'/></svg>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

    @vite('resources/css/app.css')


    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

        {{-- <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

        <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/resource-timeline@6.1.11/index.global.min.js'></script> --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @livewireStyles

    @stack('styles')

</head>

<body class="overflow-hidden">

    <div x-data="{ sidebarOpen: true }" class="flex h-screen bg-gray-100">

        <!-- Sidebar backdrop -->

        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black bg-opacity-50 transition-opacity lg:hidden"></div>



        <!-- Sidebar -->

        <div x-show="sidebarOpen" x-cloak

            class="flex flex-col fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"

            :class="{ '-translate-x-full': !sidebarOpen }">

            <div class="flex items-center justify-center h-20 bg-white border-b border-gray-100 mb-2">
                <a href="{{ route('dashboard') }}" class="flex items-center text-gray-800 text-2xl font-bold tracking-wider uppercase hover:text-green-600 transition-colors">
                    Doors
                </a>
            </div>



                        <nav class="flex-1 overflow-y-auto mt-4 px-2 space-y-1">



                            @auth



                                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">



                                    <i class="fas fa-tachometer-alt mr-3"></i>



                                    <span>Dashboard</span>



                                </x-sidebar-link>



            



                                                                                                <!-- Master Data Group -->



            



                                                                                    @role('Super Admin|Admin')



            



                                                                                    <div x-data="{ open: {{ request()->routeIs('master.*') ? 'true' : 'false' }} }" class="relative">

                        <x-sidebar-link href="#" @click="open = !open" :active="request()->routeIs('master.*')">

                            <i class="fas fa-database mr-3"></i>

                            <span>Master Data</span>

                            <i class="fas fa-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>

                        </x-sidebar-link>

                        <div x-show="open" x-cloak class="ml-4 border-l border-gray-300">

                            <x-sidebar-link :href="route('master.external-participants.index')" :active="request()->routeIs('master.external-participants.*')">

                                <i class="fas fa-user-friends mr-3"></i>

                                <span>External Participants</span>

                            </x-sidebar-link>

                            <x-sidebar-link :href="route('master.pantry-items.index')" :active="request()->routeIs('master.pantry-items.*')">

                                <i class="fas fa-boxes mr-3"></i>

                                <span>Pantry Items</span>

                            </x-sidebar-link>

                            <x-sidebar-link :href="route('master.rooms.index')" :active="request()->routeIs('master.rooms.*')">

                                <i class="fas fa-door-closed mr-3"></i>

                                <span>Rooms</span>

                            </x-sidebar-link>

                            <x-sidebar-link :href="route('master.priority-guests.index')" :active="request()->routeIs('master.priority-guests.*')">

                                <i class="fas fa-star mr-3"></i>

                                <span>Priority Guests</span>

                            </x-sidebar-link>

                            <x-sidebar-link :href="route('master.users.index')" :active="request()->routeIs('master.users.*')">

                                <i class="fas fa-users-cog mr-3"></i>

                                <span>Users</span>

                            </x-sidebar-link>

                        </div>

                    </div>

                    @endrole



                    <!-- Meeting Management Group -->

                    @role('Super Admin|Karyawan')

                    <div x-data="{ open: {{ request()->routeIs('meeting.*') ? 'true' : 'false' }} }" class="relative">

                        <x-sidebar-link href="#" @click="open = !open" :active="request()->routeIs('meeting.*')">

                            <i class="fas fa-handshake mr-3"></i>

                            <span>Meeting Management</span>

                            <i class="fas fa-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>

                        </x-sidebar-link>

                        <div x-show="open" x-cloak class="ml-4 border-l border-gray-700">

                            <x-sidebar-link :href="route('meeting.room-reservations.index')" :active="request()->routeIs('meeting.room-reservations.*')">

                                <i class="fas fa-calendar-alt mr-3"></i>

                                <span>Room Reservation</span>

                            </x-sidebar-link>

                            <x-sidebar-link :href="route('meeting.meeting-lists.index')" :active="request()->routeIs('meeting.meeting-lists.*')">

                                <i class="fas fa-list-alt mr-3"></i>

                                <span>Meeting List</span>

                            </x-sidebar-link>



                            <x-sidebar-link :href="route('meeting.analytics.index')" :active="request()->routeIs('meeting.analytics.*')">

                                <i class="fas fa-chart-line mr-3"></i>

                                <span>Analytics</span>

                            </x-sidebar-link>

                        </div>

                    </div>

                    @endrole
                    
                    @role('Super Admin|Karyawan')
                        <x-sidebar-link :href="route('guide.index')" :active="request()->routeIs('guide.index')">
                            <i class="fas fa-book-open mr-3"></i>
                            <span>User Guide</span>
                        </x-sidebar-link>
                        
                        <x-sidebar-link :href="route('survey.create')" :active="request()->routeIs('survey.create')">
                            <i class="far fa-smile mr-3"></i>
                            <span>Give Feedback</span>
                        </x-sidebar-link>
                    @endrole

                    <!-- Settings Group -->

                    @role('Super Admin')

                    <div x-data="{ open: {{ request()->routeIs('settings.*') || request()->routeIs('survey.index') ? 'true' : 'false' }} }" class="relative">

                        <x-sidebar-link href="#" @click="open = !open" :active="request()->routeIs('settings.*') || request()->routeIs('survey.index')">

                            <i class="fas fa-cogs mr-3"></i>

                            <span>Settings & Tools</span>

                            <i class="fas fa-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>

                        </x-sidebar-link>

                        <div x-show="open" x-cloak class="ml-4 border-l border-gray-700">

                            <x-sidebar-link :href="route('settings.configurations.index')" :active="request()->routeIs('settings.configurations.*')">

                                <i class="fas fa-cog mr-3"></i>

                                <span>Configurations</span>

                            </x-sidebar-link>

                            <x-sidebar-link :href="route('settings.role-permissions.index')" :active="request()->routeIs('settings.role-permissions.*')">

                                <i class="fas fa-user-shield mr-3"></i>

                                <span>Role & Permissions</span>

                            </x-sidebar-link>
                            
                            <x-sidebar-link :href="route('survey.index')" :active="request()->routeIs('survey.index')">

                                <i class="fas fa-poll mr-3"></i>

                                <span>Survey Results</span>

                            </x-sidebar-link>

                        </div>

                    </div>

                    @endrole



                                        <!-- Receptionist Group -->



                                        @role('Super Admin|Resepsionis')



                                        <div x-data="{ open: {{ request()->routeIs('dashboard.receptionist') || request()->routeIs('master.pantry-items.*') ? 'true' : 'false' }} }" class="relative">



                                            <x-sidebar-link href="#" @click="open = !open" :active="request()->routeIs('dashboard.receptionist') || request()->routeIs('master.pantry-items.*')">



                                                <i class="fas fa-concierge-bell mr-3"></i>



                                                <span>Receptionist</span>



                                                <i class="fas fa-chevron-down ml-auto" :class="{ 'rotate-180': open }"></i>



                                            </x-sidebar-link>



                                            <div x-show="open" x-cloak class="ml-4 border-l border-gray-300">



                                                <x-sidebar-link :href="route('dashboard.receptionist')" :active="request()->routeIs('dashboard.receptionist')">



                                                    <i class="fas fa-clipboard-list mr-3"></i>



                                                    <span>Dashboard</span>



                                                </x-sidebar-link>



                                                <x-sidebar-link :href="route('master.pantry-items.index')" :active="request()->routeIs('master.pantry-items.*')">



                                                    <i class="fas fa-boxes mr-3"></i>



                                                    <span>Pantry Items</span>



                                                </x-sidebar-link>



                                            </div>



                                        </div>



                                        @endrole

                    {{-- <!-- Tablet Display Link -->
                    <x-sidebar-link :href="route('tablet.room.display', ['room' => 1])" :active="request()->routeIs('tablet.room.display')">
                        <i class="fas fa-tablet-alt mr-3"></i>
                        <span>Tablet Display (Room 1)</span>
                    </x-sidebar-link> --}}

                @endauth

            </nav>

        </div>



        <!-- Main content -->

        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top bar -->

            <header class="flex justify-between items-center py-4 px-6 bg-white border-b-4 border-primary">

                <div class="flex items-center">

                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none lg:hidden">

                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                            <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>

                        </svg>

                    </button>





                </div>



                <div class="flex items-center">

                    @auth

                        <x-dropdown align="right" width="48">

                            <x-slot name="trigger">

                                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">

                                    <div>{{ Auth::user()->name }}</div>

                                    <div class="ml-1">

                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">

                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />

                                        </svg>

                                    </div>

                                </button>

                            </x-slot>



                            <x-slot name="content">

                                <x-dropdown-link :href="route('profile.edit')">

                                    {{ __('Profile') }}

                                </x-dropdown-link>



                                <form method="POST" action="{{ route('logout') }}">

                                    @csrf

                                    <x-dropdown-link :href="route('logout')"

                                            onclick="event.preventDefault(); this.closest('form').submit();">

                                        {{ __('Log Out') }}

                                    </x-dropdown-link>

                                </form>

                            </x-slot>

                        </x-dropdown>

                    @else

                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-green-500">Log in</a>

                        @if (Route::has('register'))

                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-green-500">Register</a>

                        @endif

                    @endauth

                </div>

            </header>



            <!-- Page Content -->

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 min-h-0">

                <div class="container mx-auto px-6 py-8">

                    @if (isset($header))

                        <h3 class="text-gray-700 text-3xl font-medium">

                            {{ $header }}

                        </h3>

                    @endif

                    @yield('content')

                </div>

            </main>

        </div>

    </div>



    @if (session('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('error') }}
        </div>
    @endif

    @if (session('message'))
        <div class="fixed bottom-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('message') }}
        </div>
    @endif



    @vite('resources/js/app.js')

    @livewireScripts

    <script>
        let calendar; // Declare calendar globally
        let modal; // Declare modal globally
        let modalTitle;
        let modalBody;
        let modalFooter;

        function initFullCalendar(eventsData) {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;

            if (calendar) { // Destroy existing calendar instance if it exists
                calendar.destroy();
            }

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: eventsData,
                eventClick: function(info) {
                    modal = document.getElementById('eventModal');
                    modalTitle = document.getElementById('modalTitle');
                    modalBody = document.getElementById('modalBody');
                    modalFooter = document.getElementById('modalFooter');

                    modalTitle.innerHTML = `<div class="flex items-center">
                                                <div class="mr-3 flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 sm:h-10 sm:w-10">
                                                    <i class="fas fa-calendar-alt text-primary-600"></i>
                                                </div>
                                                <h3 class="text-lg leading-6 font-medium text-gray-900">${info.event.title}</h3>
                                            </div>`;

                    let body = `<div class="mt-2 space-y-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-door-closed text-gray-500 w-5 mr-3"></i>
                                        <p><strong>Room:</strong> ${info.event.extendedProps.room_name}</p>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-gray-500 w-5 mr-3"></i>
                                        <p><strong>Time:</strong> ${new Date(info.event.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${new Date(info.event.end).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-gray-500 w-5 mr-3"></i>
                                        <p><strong>Status:</strong> <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${info.event.extendedProps.status === 'pending_confirmation' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800'}">${info.event.extendedProps.status.replace('_', ' ')}</span></p>
                                    </div>
                                </div>`;
                    modalBody.innerHTML = body;

                    let footer = '';
                    if (info.event.extendedProps.status === 'pending_confirmation') {
                        footer += `<button type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="window.Livewire.dispatch('confirmMeeting', { meetingId: ${info.event.id} }); closeModal();"><i class="fas fa-check mr-2"></i>Confirm</button>`;
                        footer += `<button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="window.Livewire.dispatch('cancelMeeting', { meetingId: ${info.event.id} }); closeModal();"><i class="fas fa-times mr-2"></i>Cancel Meeting</button>`;
                    }
                    footer += `<button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeModal()">Close</button>`;
                    modalFooter.innerHTML = footer;

                    modal.classList.remove('hidden');
                }
            });
            calendar.render();
        }

        document.addEventListener('livewire:load', function() {
            window.closeModal = function() {
                document.getElementById('eventModal').classList.add('hidden');
            }

            window.Livewire.on('recurringMeetingsUpdated', event => {
                if (calendar) {
                    calendar.removeAllEvents();
                    calendar.addEventSource(event.events);
                } else {
                    // If calendar is not initialized yet, initialize it (should not happen with x-init)
                    // This case is mostly for when the component is loaded as the initial tab.
                    initFullCalendar(event.events);
                }
            });
        });
    </script>

    @stack('scripts')

</body>

</html>
