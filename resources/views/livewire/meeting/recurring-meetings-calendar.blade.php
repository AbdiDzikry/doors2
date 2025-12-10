<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">My Recurring Meetings</h1>
            <p class="mt-1 text-sm text-gray-600">
                A calendar view of your recurring meetings. Use this to confirm or cancel upcoming occurrences.
            </p>
        </div>
        <a href="{{ route('meeting.bookings.create') }}" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="fas fa-plus mr-2"></i>
            Create New Meeting
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <div id="calendar"></div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Legend</h3>
            <div class="flex items-center space-x-4 text-sm">
                <div class="flex items-center">
                    <span class="w-4 h-4 rounded-full bg-green-500 mr-2"></span>
                    <span>Confirmed</span>
                </div>
                <div class="flex items-center">
                    <span class="w-4 h-4 rounded-full bg-amber-500 mr-2"></span>
                    <span>Pending Confirmation</span>
                </div>
                <div class="flex items-center">
                    <span class="w-4 h-4 rounded-full bg-red-500 mr-2"></span>
                    <span>Cancelled</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="eventModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle"></h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="modalBody"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse" id="modalFooter">
                </div>
            </div>
        </div>
    </div>
</div>
