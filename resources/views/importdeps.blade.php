<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import Departments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                        
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-center text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-gray-900">
                                        Departament
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 bg-gray-50 dark:bg-gray-900">
                                        Updatet At
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Update
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap bg-gray-50 dark:text-white dark:bg-gray-900">
                                            {{ $item['NAME'] }}
                                        </th>
                                        <td class="px-6 py-4">
                                            @if ($item['Status'] == 0)
                                                <span class="flex h-7 bg-red-500 rounded-full mx-2"></span>
                                            @endif
                                            @if ($item['Status'] == 1)
                                                <span class="flex h-7 bg-yellow-300 rounded-full mx-2"></span>
                                            @endif
                                            @if ($item['Status'] == 2)
                                                <span class="flex h-7 bg-green-500 rounded-full mx-2"></span>
                                            @endif

                                            {{-- <div role="status">
                                                <svg aria-hidden="true" class="inline w-6 h-6 mr-2 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                                                </svg>
                                                <span class="sr-only">Loading...</span>
                                            </div> --}}
                                        </td>
                                        <td class="px-6 py-4 bg-gray-50 dark:bg-gray-900">
                                            {{ $item['UPDATED_AT'] }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <button type="button" onclick="refreshDepartment({{ $item['ID'] }}, '{{ $item['NAME'] }}')"
                                            class="text-white bg-[#2557D6] hover:bg-[#2557D6]/90 focus:ring-4 focus:ring-[#2557D6]/50 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:focus:ring-[#2557D6]/50 mr-2 mb-2">
                                                Update
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <script>
                                function refreshDepartment(id, name) {
                                $.ajax({
                                    url: '/deps/' + id + '/refresh?name=' + name,
                                    type: 'GET',
                                    success: function(response) {
                                    // obsługa odpowiedzi serwera po poprawnym wykonaniu zapytania
                                        console.log(response);
                                    },
                                    error: function(xhr, status, error) {
                                    // obsługa błędu podczas wykonywania zapytania
                                    }
                                });
                                }
                            </script>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
