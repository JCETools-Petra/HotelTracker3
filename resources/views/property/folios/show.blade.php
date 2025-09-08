<x-property-user-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Folio Tamu: {{ $reservation->guest_name }}
                </h2>
                {{-- PERBAIKAN: Mengambil data nomor kamar dan tipe kamar dari relasi yang benar --}}
                <p class="text-sm text-gray-500">
                    Kamar {{ $reservation->hotelRoom->room_number ?? 'N/A' }} ({{ $reservation->hotelRoom->roomType->name ?? 'N/A' }})
                </p>
            </div>
            <a href="{{ route('property.frontoffice.index') }}" class="text-sm text-blue-600 hover:underline">
                &larr; Kembali ke Tampilan Kamar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Menampilkan notifikasi error --}}
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            
            {{-- Menampilkan notifikasi sukses --}}
            @if(session('success'))
                 <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold border-b pb-2 mb-4">Rincian Tagihan</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase">Deskripsi</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase">Tagihan (Charges)</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase">Pembayaran (Payments)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($folio->items->sortBy('created_at') as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->created_at->isoFormat('D MMM YYYY') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $item->description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                @if($item->type === 'charge')
                                                    {{ number_format($item->amount, 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                @if($item->type === 'payment')
                                                    {{ number_format($item->amount, 0, ',', '.') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-sm text-gray-500">Belum ada transaksi.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-gray-100 dark:bg-gray-700/50 text-sm">
                                    @if($folio->subtotal > 0)
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="px-6 py-2 text-right font-semibold">Subtotal</td>
                                        <td class="px-6 py-2 text-right font-semibold">Rp {{ number_format($folio->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="px-6 py-2 text-right">Layanan ({{ $folio->service_percentage }}%)</td>
                                        <td class="px-6 py-2 text-right">Rp {{ number_format($folio->service_amount, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="px-6 py-2 text-right border-b dark:border-gray-600">Pajak ({{ $folio->tax_percentage }}%)</td>
                                        <td class="px-6 py-2 text-right border-b dark:border-gray-600">Rp {{ number_format($folio->tax_amount, 0, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="px-6 py-3 text-right font-bold">Grand Total</td>
                                        <td class="px-6 py-3 text-right font-bold">Rp {{ number_format($folio->grand_total, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="px-6 py-3 text-right font-bold">Total Pembayaran</td>
                                        <td class="px-6 py-3 text-right font-bold text-green-600">Rp {{ number_format($folio->total_payments, 0, ',', '.') }}</td>
                                    </tr>
                                    @if ($folio->balance < 0)
                                        <tr>
                                            <td colspan="2"></td>
                                            <td class="px-6 py-3 text-right font-bold text-lg">Uang Kembalian</td>
                                            <td class="px-6 py-3 text-right font-bold text-lg text-blue-600 dark:text-blue-400">Rp {{ number_format(abs($folio->balance), 0, ',', '.') }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="2"></td>
                                            <td class="px-6 py-3 text-right font-bold text-lg">Saldo Terutang</td>
                                            <td class="px-6 py-3 text-right font-bold text-lg {{ round($folio->balance) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">Rp {{ number_format($folio->balance, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-2">Finalisasi & Cetak</h3>
                            <div class="space-y-3">
                                <a href="{{ route('property.folio.print-receipt', $reservation) }}" target="_blank" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                                    Print Receipt
                                </a>
                                <form action="{{ route('property.folio.process-checkout', $reservation) }}" method="POST" onsubmit="return confirm('Anda yakin ingin check-out tamu ini?');">
                                    @csrf
                                    <x-primary-button class="w-full justify-center" 
                                                      :disabled="round($folio->balance, 2) > 0" 
                                                      title="{{ round($folio->balance, 2) > 0 ? 'Saldo harus lunas (Rp 0) untuk check-out' : 'Lanjutkan proses check-out' }}">
                                        Proses Check-out
                                    </x-primary-button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <form action="{{ route('property.folio.add-charge', $folio) }}" method="POST" class="p-6">
                            @csrf
                            <h3 class="text-lg font-semibold mb-4">Tambah Tagihan Baru</h3>
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="charge_description" value="Deskripsi (Cth: Laundry, Mini Bar)" />
                                    <x-text-input id="charge_description" name="description" class="w-full mt-1" required />
                                </div>
                                <div>
                                    <x-input-label for="charge_amount" value="Jumlah (Rp)" />
                                    <x-text-input id="charge_amount" type="number" name="amount" class="w-full mt-1" required />
                                </div>
                                <x-primary-button>+ Tambah Tagihan</x-primary-button>
                            </div>
                        </form>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <form action="{{ route('property.folio.add-payment', $folio) }}" method="POST" class="p-6">
                               @csrf
                            <h3 class="text-lg font-semibold mb-4">Catat Pembayaran</h3>
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="payment_description" value="Deskripsi (Cth: Tunai, Kartu Kredit)" />
                                    <x-text-input id="payment_description" name="description" class="w-full mt-1" required />
                                </div>
                                <div>
                                    <x-input-label for="payment_amount" value="Jumlah (Rp)" />
                                    <x-text-input id="payment_amount" type="number" name="amount" class="w-full mt-1" required />
                                </div>
                                <x-primary-button>+ Catat Pembayaran</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-property-user-layout>