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

                {{-- {{ request()->is('/viewcalendar/' . $calendar_id) }} --}}

                @foreach ($user_calendars as $calendar)
                    <li>
                        <a href="{{ route('view.calendar', $calendar->id) }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->is('/viewcalendar/' . $calendar_id) ? 'bg-indigo-600' : '' }}">
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
    
    <div class="float-right w-1/6 min-w-120 max-w-200 mr-2 pr-5 dark:bg-gray-800 mt-12 rounded-lg">
        <div class="h-full px-3 py-4 overflow-y-auto flex flex-col">
            <p class="text-center text-white">{{ __('Actions') }}</p>
            {{-- <a class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 shadow-lg shadow-blue-500/50 hover:shadow-blue-700/50 mt-6" data-modal-toggle="saveModal">{{ __('Save') }}</a> --}}
            {{-- <a class="text-white bg-yellow-700 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-yellow-400 dark:hover:bg-yellow-500 dark:focus:ring-yellow-800 mt-8" href="#" onClick="window.location.reload();">{{ __('Discard Changes') }}</a> --}}
            <a class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 mt-4" data-modal-toggle="deleteModal">{{ __('Delete') }}</a>

            @include('components.calendar.modal-delete')
            @include('components.calendar.modal-save-edited-calendar')
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-white-100">
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-3"
                            role="alert">
                            <strong class="font-bold">{{ __('Holy smokes!') }}</strong>
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

    {{-- <div id="alert-border-1" class="flex p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800" role="alert">
        <svg class="flex-shrink-0 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
        <div class="ml-3 text-sm font-medium">
          A simple info alert with an <a href="#" class="font-semibold underline hover:no-underline">example link</a>. Give it a click if you like.
        </div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex h-8 w-8 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-border-1" aria-label="Close">
          <span class="sr-only">Dismiss</span>
          <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
        </button>
    </div> --}}

    @include('components.calendar.javascript-elements')
</x-app-layout>
