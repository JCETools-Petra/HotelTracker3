<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('POS - ') }} {{ $restaurant->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Menampilkan pesan sukses atau error dari redirect --}}
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-6">
                        <h3 class="text-lg font-bold mb-2">Room Service</h3>
                        <a href="{{ route('admin.pos.roomservice.create', $restaurant) }}" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-3 bg-indigo-500 text-white font-bold rounded-md hover:bg-indigo-600 transition duration-150">
                            Create Room Service Order
                        </a>
                    </div>

                    <h3 class="text-2xl font-bold text-center mb-6">Dine-in Orders</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @forelse ($tables as $table)
                            {{-- PERBAIKAN UTAMA ADA DI BLOK <a> DI BAWAH INI --}}
                            <a href="{{ route('admin.pos.order.for-table', $table) }}" 
                               class="block p-4 rounded-lg shadow-md text-center transition duration-300 text-white
                                      {{-- Cek apakah relasi pendingOrder ada. Jika ya, meja terisi (merah). --}}
                                      {{ $table->pendingOrder ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' }}">
                                <div class="text-2xl font-bold">{{ $table->name }}</div>
                                {{-- Tampilkan status berdasarkan keberadaan pendingOrder --}}
                                <div class="text-sm capitalize">{{ $table->pendingOrder ? 'Occupied' : 'Available' }}</div>
                            </a>
                        @empty
                            <p class="text-center col-span-full text-gray-500">
                                No tables have been added to this restaurant yet.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>