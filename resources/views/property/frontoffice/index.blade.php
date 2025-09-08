<x-property-user-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Front Office - Tampilan Kamar
        </h2>
    </x-slot>

    <div x-data="frontOffice()" class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            {{-- Navigasi Tanggal --}}
            <div class="mb-4 flex justify-center">
                <form action="{{ route('property.frontoffice.index') }}" method="GET" class="flex items-center space-x-2 bg-white dark:bg-gray-800 p-3 rounded-lg shadow">
                    <a href="{{ route('property.frontoffice.index', ['date' => $viewDate->copy()->subDay()->toDateString()]) }}" class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <input type="date" name="date" value="{{ $viewDate->toDateString() }}" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <a href="{{ route('property.frontoffice.index', ['date' => $viewDate->copy()->addDay()->toDateString()]) }}" class="p-2 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </form>
            </div>

            {{-- Grid Kamar --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5">
                        @foreach ($hotelRooms as $hotelRoom)
                            @php
                                $reservation = $hotelRoom->reservation;
                                
                                $statusText = '';
                                $statusClass = '';
                                $badgeClass = '';
                                $isClickable = true;

                                if ($reservation) {
                                    $statusText = $reservation->status;
                                    $statusClass = $reservation->status === 'Checked-in' ? 'border-red-500' : 'border-blue-500';
                                    $badgeClass = $reservation->status === 'Checked-in' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                } else {
                                    $statusText = ucfirst($hotelRoom->status);
                                    if ($hotelRoom->status === 'clean' || $hotelRoom->status === 'inspected') {
                                        $statusClass = 'border-green-500';
                                        $badgeClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                    } elseif ($hotelRoom->status === 'dirty') {
                                        $statusClass = 'border-yellow-500';
                                        $badgeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                    } else {
                                        $statusClass = 'border-gray-500';
                                        $badgeClass = 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                        $isClickable = false;
                                    }
                                }
                            @endphp

                            <div @if($isClickable) 
                                     @click="openModal({{ $hotelRoom->load('roomType')->toJson() }}, {{ $reservation ? $reservation->toJson() : 'null' }})" 
                                 @endif
                                 class="relative bg-gray-50 dark:bg-gray-900 rounded-lg shadow-lg flex flex-col justify-between border-l-8 p-4 h-40 {{ $isClickable ? 'cursor-pointer transition-all duration-200 hover:shadow-xl hover:scale-105' : 'cursor-not-allowed opacity-70' }} {{ $statusClass }}">
                                
                                <div class="flex-shrink-0">
                                    <div class="flex justify-between items-baseline">
                                        <span class="font-bold text-3xl text-gray-800 dark:text-gray-200">{{ $hotelRoom->room_number }}</span>
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badgeClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-1">{{ $hotelRoom->roomType->name }}</p>
                                </div>

                                <div class="text-xs text-gray-600 dark:text-gray-300 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 min-h-[40px]">
                                    @if($reservation)
                                        <p class="font-semibold truncate text-sm" title="{{ $reservation->guest_name }}">{{ $reservation->guest_name }}</p>
                                        <p>{{ \Carbon\Carbon::parse($reservation->checkin_date)->format('d M') }} - {{ \Carbon\Carbon::parse($reservation->checkout_date)->format('d M') }}</p>
                                    @else
                                        <p class="italic text-gray-400 dark:text-gray-500">Available</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Modal --}}
            <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="display: none;" x-cloak>
                <div @click.away="showModal = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-lg">
                    
                    <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100" x-text="modalTitle"></h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">&times;</button>
                    </div>

                    <div>
                        {{-- Tampilan untuk Kamar KOSONG & BERSIH (Form Reservasi) --}}
                        <div x-show="modalContent === 'checkin'">
                             <form action="{{ route('property.frontoffice.reservation.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="hotel_room_id" x-bind:value="selectedRoom.id">
                                <div class="p-6 space-y-4">
                                    <h4 class="font-semibold">Detail Tamu</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <x-text-input name="guest_name" placeholder="Nama Lengkap Tamu" required />
                                        <x-text-input name="guest_phone" placeholder="Nomor Telepon" />
                                    </div>
                                    <div class="mt-4">
                                        <x-input-label for="guest_address" value="Alamat Tamu (Opsional)" />
                                        <textarea name="guest_address" id="guest_address" rows="3"
                                                  class="block w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:ring-indigo-500 rounded-md shadow-sm"
                                                  placeholder="Masukkan alamat lengkap tamu"></textarea>
                                    </div>

                                    <h4 class="font-semibold pt-4">Detail Menginap</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="checkin_date" value="Tanggal Check-in" />
                                            <x-text-input id="checkin_date" type="date" name="checkin_date" class="w-full mt-1" value="{{ $viewDate->toDateString() }}" required />
                                        </div>
                                        <div>
                                            <x-input-label for="checkout_date" value="Tanggal Check-out" />
                                            <x-text-input id="checkout_date" type="date" name="checkout_date" class="w-full mt-1" value="{{ $viewDate->copy()->addDay()->toDateString() }}" required />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
                                        <div>
                                            <x-input-label for="segment" value="Segmentasi Pasar" />
                                            <select name="segment" id="segment" class="w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                                <option value="Walk In">Walk In</option>
                                                <option value="OTA">OTA</option>
                                                <option value="Travel Agent">Travel Agent</option>
                                                <option value="Government">Pemerintahan</option>
                                                <option value="Corporation">Korporasi</option>
                                                <option value="Affiliasi">Affiliasi</option>
                                                <option value="Compliment">Compliment</option>
                                                <option value="House Use">House Use</option>
                                            </select>
                                        </div>
                                        <div>
                                            <x-input-label for="final_price" value="Harga per Malam" />
                                            <x-text-input id="final_price" type="number" name="final_price" class="w-full mt-1" placeholder="Contoh: 500000" />
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                                    <x-primary-button>Buat Reservasi</x-primary-button>
                                </div>
                            </form>
                        </div>

                        {{-- Tampilan untuk Kamar SUDAH DIPESAN (Form Check-in) --}}
                        <div x-show="modalContent === 'booked'">
                        <div class="p-6">
                            <p class="mb-4 text-center">Tamu <strong x-text="selectedReservation.guest_name"></strong> memiliki reservasi untuk hari ini.</p>
                            
                            {{-- PERBAIKAN UTAMA: Menambahkan form dengan input field untuk Nomor Kunci --}}
                            <form :action="'{{ url('/') }}/property/front-office/check-in/' + selectedReservation.id" method="POST" id="checkInForm">
                                @csrf
                                <x-input-label for="key_number_booked" value="Nomor Kunci (Opsional)" />
                                <x-text-input id="key_number_booked" name="key_number" class="w-full mt-1" placeholder="Masukkan nomor kunci kamar" />
                            </form>

                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                            <x-primary-button type="submit" form="checkInForm">Proses Check-in</x-primary-button>
                        </div>
                    </div>

                        {{-- Tampilan untuk Kamar SUDAH TERISI (Info & Link Folio) --}}
                        <div x-show="modalContent === 'occupied'">
                            <div class="p-6">
                                <p class="mb-2 text-center">Tamu <strong x-text="selectedReservation.guest_name"></strong> sedang menginap.</p>
                                <p class="text-center text-sm text-gray-500">Checkout pada <span x-text="new Date(selectedReservation.checkout_date).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})"></span></p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
                                <div x-show="selectedReservation && selectedReservation.folio">
                                    <a :href="`{{ url('/') }}/property/folio/${selectedReservation.folio.id}`"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-green-700">
                                    Lihat Folio & Tagihan
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Tampilan untuk Kamar KOTOR (Form Ganti Status) --}}
                        <div x-show="modalContent === 'cleaning'">
                            <form :action="'{{ url('/') }}/property/front-office/hotel-room/' + selectedRoom.id + '/update-status'" method="POST" >
                                @csrf
                                @method('PUT')
                                <div class="p-6">
                                    <p class="mb-4">Ubah status untuk kamar <strong x-text="selectedRoom.room_number"></strong>:</p>
                                    <select name="status" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                        <option value="clean">Tandai Bersih (Clean)</option>
                                        <option value="inspected">Tandai Sudah Diinspeksi (Inspected)</option>
                                    </select>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 flex justify-end gap-4">
                                    <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">Batal</button>
                                    <x-primary-button>Update Status</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        function frontOffice() {
            return {
                showModal: false,
                modalTitle: '',
                modalContent: '',
                selectedRoom: {},
                selectedReservation: null,
                openModal(room, reservation) {
                    this.selectedRoom = room;
                    this.selectedReservation = reservation;

                    if (reservation) { 
                        if (reservation.status === 'Checked-in') {
                            this.modalTitle = `Detail Kamar ${room.room_number}`;
                            this.modalContent = 'occupied';
                        } else { // Status 'Booked'
                            this.modalTitle = `Check-in Tamu - Kamar ${room.room_number}`;
                            this.modalContent = 'booked';
                        }
                    } else if (room.status === 'dirty') { 
                        this.modalTitle = `Status Kamar ${room.room_number}`;
                        this.modalContent = 'cleaning';
                    } else { // Status 'clean' atau 'inspected'
                        this.modalTitle = `Reservasi Baru - Kamar ${room.room_number}`;
                        this.modalContent = 'checkin';
                    }

                    this.showModal = true;
                }
            }
        }
    </script>
    @endpush
</x-property-user-layout>