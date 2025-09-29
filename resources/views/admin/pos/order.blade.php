<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            @if ($order->table)
                Order for Table: {{ $order->table->name }}
            @elseif ($order->reservation)
                Room Service: Room {{ $order->reservation->hotelRoom->room_number ?? 'N/A' }}
            @else
                Order
            @endif
            <span class="text-base font-normal text-gray-500">(Order #{{ $order->id }})</span>
        </h2>
    </x-slot>

    {{-- Inisialisasi Alpine.js untuk modal --}}
    <div x-data="{ showChargeModal: false }" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

            <div class="flex flex-col md:flex-row gap-6">

                <div class="w-full md:w-2/3">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Menu</h3>
                            <div class="space-y-6">
                                @forelse($menuCategories as $category)
                                    @if($category->menus->isNotEmpty())
                                        <div>
                                            <h4 class="text-lg font-bold text-gray-800 dark:text-gray-200 border-b border-gray-300 dark:border-gray-600 pb-2 mb-3">
                                                {{ $category->name }}
                                            </h4>
                                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                                @foreach($category->menus as $menu)
                                                    <form action="{{ route('admin.pos.order.add', $order) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                                        <button type="submit" class="w-full h-full p-3 bg-gray-200 dark:bg-gray-700 rounded-lg text-left hover:bg-blue-500 hover:text-white dark:hover:text-white transition duration-150">
                                                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $menu->name }}</p>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400">No menu categories found for this restaurant.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-1/3">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex flex-col h-full text-gray-900 dark:text-gray-100">
                             <h3 class="text-xl font-bold mb-4">Current Bill</h3>

                             <div class="space-y-4 mb-4 flex-grow overflow-y-auto">
                                @forelse($order->items as $item)
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold">{{ $item->menu->name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Rp {{ number_format($item->price, 0, ',', '.') }}
                                            </p>
                                            <div class="flex items-center mt-1 space-x-2">
                                                <form action="{{ route('admin.pos.order.decrease', $item) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-full w-6 h-6 flex items-center justify-center font-bold">-</button>
                                                </form>
                                                <span class="font-bold text-sm px-1">{{ $item->quantity }}</span>
                                                <form action="{{ route('admin.pos.order.increase', $item) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-full w-6 h-6 flex items-center justify-center font-bold">+</button>
                                                </form>
                                                <form action="{{ route('admin.pos.order.remove', $item) }}" method="POST" onsubmit="return confirm('Remove this item?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="font-semibold">
                                            Rp {{ number_format($item->total_price, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-gray-500">No items added yet.</p>
                                @endforelse
                             </div>

                             <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2 mt-auto">
                                <div class="flex justify-between">
                                    <span>Subtotal</span>
                                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                                </div>
                                
                                <div class="pb-2">
                                    <form action="{{ route('admin.pos.order.discount', $order) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <select name="discount_type" class="text-xs p-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="percentage" {{ $order->discount_type == 'percentage' ? 'selected' : '' }}>%</option>
                                            <option value="fixed" {{ $order->discount_type == 'fixed' ? 'selected' : '' }}>Rp</option>
                                        </select>
                                        <input type="number" step="any" name="discount_value" value="{{ $order->discount_value ?? 0 }}" class="w-full text-xs p-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Discount">
                                        <button type="submit" class="text-xs bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600">Apply</button>
                                    </form>
                                     @error('discount_value')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-between text-red-500">
                                    <span>Discount</span>
                                    <span>- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span>Tax (11%)</span>
                                    <span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                                </div>
                                 <div class="flex justify-between font-bold text-lg">
                                    <span>TOTAL</span>
                                    <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                                </div>
                             </div>

                             <div class="mt-6 space-y-2">
                                <button @click="showChargeModal = true" type="button" class="w-full bg-indigo-500 text-white font-bold py-3 px-4 rounded hover:bg-indigo-600 transition duration-150">
                                    CHARGE TO ROOM
                                </button>
                                
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.pos.order.print', $order) }}" target="_blank" class="w-full text-center bg-gray-500 text-white font-bold py-2 px-4 rounded hover:bg-gray-600 transition duration-150">
                                        PRINT BILL
                                    </a>
                                    <form class="w-full" action="{{ route('admin.pos.order.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                        @csrf
                                        <button type="submit" class="w-full bg-red-500 text-white font-bold py-2 px-4 rounded hover:bg-red-600 transition duration-150">
                                            CANCEL
                                        </button>
                                    </form>
                                </div>
                            
                                <form action="{{ route('admin.pos.order.complete', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to complete this order?');">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-500 text-white font-bold py-3 px-4 rounded hover:bg-green-600 transition duration-150">
                                        COMPLETE PAYMENT
                                    </button>
                                </form>
                             </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div x-show="showChargeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
            <div @click.away="showChargeModal = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
                <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Charge Bill to Room</h3>
                
                @if($activeReservations->isNotEmpty())
                    <form action="{{ route('admin.pos.order.charge', $order) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="reservation_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Select Guest Room</label>
                            <select id="reservation_id" name="reservation_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">-- Select a room --</option>
                                @foreach($activeReservations as $reservation)
                                    <option value="{{ $reservation->id }}">
                                        Room {{ $reservation->hotelRoom->room_number ?? 'N/A' }} - {{ $reservation->guest_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('reservation_id')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-end gap-4 mt-6">
                            <button type="button" @click="showChargeModal = false" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded-md hover:bg-gray-400">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600">Confirm Charge</button>
                        </div>
                    </form>
                @else
                    {{-- Tampilan jika tidak ada tamu yang check-in --}}
                    <div class="text-center py-8">
                        <p class="text-gray-600 dark:text-gray-400">Tidak ada kamar yang sedang check-in saat ini.</p>
                    </div>
                     <div class="flex justify-end gap-4 mt-6">
                        <button type="button" @click="showChargeModal = false" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded-md hover:bg-gray-400">Close</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>