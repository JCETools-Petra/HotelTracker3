<x-property-user-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Front Office - Tampilan Kamar') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="frontOffice()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex justify-center">
                <form action="{{ route('property.frontoffice.index') }}" method="GET" class="flex items-center space-x-2 bg-white dark:bg-gray-800 p-3 rounded-lg shadow">
                    <a href="{{ route('property.frontoffice.index', ['date' => $viewDate->copy()->subDay()->toDateString()]) }}" class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <input type="date" name="date" value="{{ $viewDate->toDateString() }}" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <a href="{{ route('property.frontoffice.index', ['date' => $viewDate->copy()->addDay()->toDateString()]) }}" class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($hotelRooms->isEmpty())
                        <p>Tidak ada kamar yang terdaftar untuk properti ini. Silakan tambahkan kamar terlebih dahulu di panel admin.</p>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5">
                            @foreach ($hotelRooms as $room)
                                @php
                                    $reservation = $room->reservations->first();
                                    $isOccupiedOrBooked = $reservation && $reservation->status !== 'Checked-out';
                                    $isAvailable = !$isOccupiedOrBooked && $room->status === 'Tersedia';
                                    $isClickable = $isOccupiedOrBooked || $isAvailable;
                                    $statusText = $isOccupiedOrBooked ? $reservation->status : $room->status;
                                    
                                    $statusClass = '';
                                    $statusBadgeClass = 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200';

                                    if ($isOccupiedOrBooked) {
                                        if ($reservation->status === 'Booked') {
                                            $statusClass = 'border-blue-500';
                                            $statusBadgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                        } elseif ($reservation->status === 'Checked-in') {
                                            $statusClass = 'border-red-500';
                                            $statusBadgeClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                        }
                                    } else {
                                        switch ($room->status) {
                                            case 'Tersedia':
                                                $statusClass = 'border-green-500';
                                                $statusBadgeClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                                break;
                                            case 'Kotor':
                                                $statusClass = 'border-yellow-500 opacity-60';
                                                $statusBadgeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                                break;
                                            case 'Pembersihan':
                                                $statusClass = 'border-cyan-500 opacity-60';
                                                $statusBadgeClass = 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200';
                                                break;
                                            case 'Perbaikan':
                                                $statusClass = 'border-gray-500 opacity-60';
                                                break;
                                        }
                                    }
                                @endphp

                                <div @if($isClickable) @click="openModal({{ $room->id }}, '{{ $room->room_number }}', {{ $isOccupiedOrBooked ? 'true' : 'false' }}, {{ $isOccupiedOrBooked ? $reservation->toJson() : 'null' }})" @endif
                                     class="relative bg-gray-50 dark:bg-gray-900 rounded-lg shadow-lg flex flex-col justify-between border-l-8 p-4 h-40 {{ $isClickable ? 'cursor-pointer transition-all duration-200 hover:shadow-xl hover:scale-105' : 'cursor-not-allowed' }} {{ $statusClass }}">
                                    
                                    @if(!$isOccupiedOrBooked && ($room->status === 'Kotor' || $room->status === 'Pembersihan'))
                                        <form action="{{ route('property.frontoffice.room.update-status', $room) }}" method="POST" class="absolute top-2 right-2 z-10">
                                            @csrf
                                            <input type="hidden" name="status" value="Tersedia">
                                            <button type="submit" class="p-1 bg-green-500 text-white rounded-full hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" title="Tandai sebagai Bersih & Tersedia">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </button>
                                        </form>
                                    @endif

                                    <div class="flex-shrink-0">
                                        <div class="flex justify-between items-baseline">
                                            <span class="font-bold text-3xl text-gray-800 dark:text-gray-200">{{ $room->room_number }}</span>
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $statusBadgeClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 -mt-1">{{ $room->roomType->name }}</p>
                                    </div>

                                    <div class="text-xs text-gray-600 dark:text-gray-300 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 min-h-[40px]">
                                        @if($isOccupiedOrBooked)
                                            <p class="font-semibold truncate text-sm" title="{{ $reservation->guest_name }}">{{ $reservation->guest_name }}</p>
                                            <p>{{ \Carbon\Carbon::parse($reservation->checkin_date)->format('d M') }} - {{ \Carbon\Carbon::parse($reservation->checkout_date)->format('d M') }}</p>
                                        @else
                                            <p class="italic text-gray-400 dark:text-gray-500">{{ $room->status }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div x-show="showModal" @click.away="showModal = false" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl" @click.stop>
                    <div class="p-4 border-b dark:border-gray-700">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100" x-text="modalTitle"></h3>
                    </div>

                    <div x-show="!isOccupiedOrBooked">
                        <form action="{{ route('property.frontoffice.reservation.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="hotel_room_id" x-model="selectedRoomId">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Detail Tamu</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </span>
                                        <x-text-input id="guest_name" name="guest_name" class="w-full pl-10" placeholder="Nama Lengkap Tamu" required />
                                    </div>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        </span>
                                        <x-text-input id="guest_phone" name="guest_phone" class="w-full pl-10" placeholder="Nomor Telepon" />
                                    </div>
                                </div>
                                <div class="relative mt-6">
                                     <span class="absolute top-3 left-0 flex items-center pl-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </span>
                                    <textarea id="guest_address" name="guest_address" rows="2" class="block w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm pl-10" placeholder="Alamat Tamu"></textarea>
                                </div>
                                <hr class="my-6 border-gray-200 dark:border-gray-700">
                                <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Detail Menginap dan Pembayaran</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="checkin_date" value="Tanggal Check-in" />
                                        <x-text-input id="checkin_date" type="date" name="checkin_date" class="w-full mt-1" value="{{ $viewDate->toDateString() }}" required />
                                    </div>
                                    <div>
                                        <x-input-label for="checkout_date" value="Tanggal Check-out" />
                                        <x-text-input id="checkout_date" type="date" name="checkout_date" class="w-full mt-1" value="{{ $viewDate->copy()->addDay()->toDateString() }}" required />
                                    </div>
                                    <div>
                                        <x-input-label for="segment" value="Segmentasi Pasar" />
                                        <select name="segment" id="segment" class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="Walk In">Walk In</option>
                                            <option value="OTA">OTA</option>
                                            <option value="Travel Agent">Travel Agent</option>
                                            <option value="Government">Pemerintahan</option>
                                            <option value="Corporation">Korporasi</option>
                                            <option value="Compliment">Compliment</option>
                                            <option value="House Use">House Use</option>
                                            <option value="Affiliasi">Afiliasi</option>
                                        </select>
                                    </div>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pt-6">
                                            <span class="text-gray-500 sm:text-sm">Rupiah</span>
                                        </span>
                                        <x-input-label for="final_price" value="Total Harga" />
                                        <x-text-input id="final_price" type="number" name="final_price" class="w-full mt-1 pl-16" placeholder="Kosongkan jika Compliment/House Use" />
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-2">
                                <x-secondary-button type="button" @click="showModal = false">Batal</x-secondary-button>
                                <x-primary-button type="submit">Simpan Reservasi</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <div x-show="isOccupiedOrBooked">
                        <div x-show="reservationDetails.status === 'Booked'">
                            <div class="p-6">
                                <p class="mb-4 text-center">Tamu <strong><span x-text="reservationDetails.guest_name"></span></strong> akan check-in hari ini.</p>
                                <form :action="`/property/front-office/check-in/${reservationDetails.id}`" method="POST" id="checkInForm">
                                    @csrf
                                    <div>
                                        <x-input-label for="key_number_booked" value="Nomor Kunci (Opsional)" />
                                        <x-text-input id="key_number_booked" name="key_number" class="w-full mt-1" />
                                    </div>
                                </form>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
                                <form :action="`/property/front-office/cancel/${reservationDetails.id}`" method="POST" onsubmit="return confirm('Anda yakin ingin MEMBATALKAN reservasi ini?');">
                                    @csrf
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 dark:hover:text-red-400 font-semibold">Batalkan Reservasi</button>
                                </form>
                                <div class="space-x-2">
                                    <x-secondary-button type="button" @click="showModal = false">Tutup</x-secondary-button>
                                    <x-primary-button type="submit" form="checkInForm">Proses Check-in</x-primary-button>
                                </div>
                            </div>
                        </div>

                        <div x-show="reservationDetails.status === 'Checked-in'">
                            <div class="p-6">
                                <p class="mb-2 text-center">Tamu <strong><span x-text="reservationDetails.guest_name"></span></strong> sedang menginap.</p>
                                <p class="text-center text-sm text-gray-500">Checkout pada <span x-text="formatDate(reservationDetails.checkout_date)"></span></p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end space-x-2">
                                 <a :href="`/property/folio/${reservationDetails.id}`" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Lihat Folio & Tagihan
                                </a>
                                <x-secondary-button type="button" @click="showModal = false">Tutup</x-secondary-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function frontOffice() {
                return {
                    showModal: false,
                    isOccupiedOrBooked: false,
                    modalTitle: '',
                    selectedRoomId: null,
                    reservationDetails: {},

                    formatDate(dateString) {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        const options = { day: 'numeric', month: 'short', year: 'numeric' };
                        return date.toLocaleDateString('id-ID', options);
                    },

                    openModal(roomId, roomNumber, isOccupiedOrBooked, reservation) {
                        this.selectedRoomId = roomId;
                        this.isOccupiedOrBooked = isOccupiedOrBooked;
                        
                        if (isOccupiedOrBooked && reservation) {
                            this.modalTitle = `Detail Kamar ${roomNumber}`;
                            this.reservationDetails = reservation;
                        } else {
                            this.modalTitle = `Reservasi Baru - Kamar ${roomNumber}`;
                            this.reservationDetails = {};
                        }

                        this.showModal = true;
                    }
                }
            }
        </script>
    </div>
</x-property-user-layout>