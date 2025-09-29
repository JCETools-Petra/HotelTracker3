<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            New Room Service Order
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-4">Select a Guest Room</h3>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">Select the guest's room to start a new room service order. The bill will be charged to the room's folio.</p>
                    
                    {{-- Formulir ini akan kita buat fungsional di langkah selanjutnya --}}
                    <form action="#" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="reservation_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Guest Room</label>
                            <select id="reservation_id" name="reservation_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">-- Select a room --</option>
                                @foreach($activeReservations as $reservation)
                                    <option value="{{ $reservation->id }}">
                                        Room {{ $reservation->hotelRoom->room_number ?? 'N/A' }} - {{ $reservation->guest_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.pos.show', $restaurant) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
                                Cancel
                            </a>
                            <x-primary-button>
                                Start Order
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>