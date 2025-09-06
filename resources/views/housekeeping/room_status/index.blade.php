<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Status Kamar Housekeeping') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $statusOrder = ['Kotor', 'Pembersihan', 'Perbaikan', 'Terisi', 'Tersedia'];
                $statusColors = [
                    'Kotor' => 'bg-yellow-500',
                    'Pembersihan' => 'bg-blue-500',
                    'Perbaikan' => 'bg-gray-500',
                    'Terisi' => 'bg-red-500',
                    'Tersedia' => 'bg-green-500',
                ];
            @endphp

            @foreach ($statusOrder as $status)
                @if(isset($rooms[$status]) && $rooms[$status]->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 border-b dark:border-gray-700">
                            <h3 class="text-lg font-semibold flex items-center">
                                <span class="w-4 h-4 rounded-full mr-3 {{ $statusColors[$status] }}"></span>
                                {{ $status }} ({{ $rooms[$status]->count() }} Kamar)
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
                            @foreach ($rooms[$status] as $room)
                                <div class="p-3 rounded-lg text-center border dark:border-gray-600">
                                    <p class="font-bold text-xl">{{ $room->room_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $room->roomType->name }}</p>

                                    @if($status === 'Kotor' || $status === 'Pembersihan')
                                        <div class="mt-2 text-xs flex items-center justify-center space-x-1">

                                            @if($status === 'Kotor')
                                                <form action="{{ route('housekeeping.room-status.update', $room) }}" method="POST" class="flex-1">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ \App\Models\HotelRoom::STATUS_PEMBERSIHAN }}">
                                                    <button type="submit" class="w-full bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">
                                                        Mulai
                                                    </button>
                                                </form>
                                                <form action="{{ route('housekeeping.room-status.update', $room) }}" method="POST" class="flex-1">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ \App\Models\HotelRoom::STATUS_TERSEDIA }}">
                                                    <button type="submit" class="w-full bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600">
                                                        Selesai
                                                    </button>
                                                </form>

                                            @elseif($status === 'Pembersihan')
                                                <form action="{{ route('housekeeping.room-status.update', $room) }}" method="POST" class="w-full">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ \App\Models\HotelRoom::STATUS_TERSEDIA }}">
                                                    <button type="submit" class="w-full bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600">
                                                        Selesai
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</x-app-layout>