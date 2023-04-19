<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Single Calendar') }}
        </h2>
    </x-slot>

    <div class="float-left w-1/6 min-w-120 max-w-200 mr-10 ml-2 pr-5 dark:bg-gray-800 mt-12 rounded-lg">
        <div class="h-full px-3 py-4 overflow-y-auto">
            <ul class="space-y-2 font-medium w-fit ml-2">
                <li>
                    <a href="{{ route('calendar') }}"
                        class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('calendar') ? 'bg-indigo-600' : '' }}">
                        <i class='fas fa-calendar-alt'></i>
                        <span class="flex-1 ml-3 whitespace-nowrap">{{ __('New Calendar') }}</span>
                    </a>
                </li>

                @foreach ($user_calendars as $calendar)
                    <li>
                        <a href="{{ route('view.calendar', $calendar->id) }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('') ? 'bg-indigo-600' : '' }}">
                            <i class='fas fa-calendar-alt'></i>
                            <span
                                class="flex-1 ml-3 whitespace-nowrap">{{ mb_strimwidth($calendar['Calendar_Name'], 0, 14, '...') }}</span>
                        </a>
                    </li>
                @endforeach

                <li>
                    <a href="#"
                        class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class='fas fa-database'></i>
                        <span class="flex-1 ml-3 whitespace-nowrap">{{ __('All calendars') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-white-100">
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-3"
                            role="alert">
                            <strong class="font-bold">Holy smokes!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg class="fill-current h-6 w-6 text-red-500" role="button"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <title>Close</title>
                                    <path
                                        d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                                </svg>
                            </span>
                        </div>
                    @endif
                    

                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.js"></script>
        <script src='fullcalendar/lang-all.js'></script>
        <script> 
            document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: 'GMT',
                initialView: 'timeGridWeek',
                initialDate: @json($calendar_start_date),
                slotMinTime: '9:00:00',
                slotMaxTime: '16:00:00',
                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false,
                    hour12: false
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridTwoDay,timeGridDay,listWeek'
                },
                slotDuration: '00:05:00',
                eventTimeFormat: { // like '14:30:00'
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false,
                    hour12: false
                },
                views: {
                    timeGridTwoDay: {
                        type: 'timeGrid',
                        duration: { days: 2 },
                        buttonText: 'two days'
                    }
                },
                events: @json($calendar_data)
            });
        calendar.render();
        });
        </script>
    @endpush
</x-app-layout>
