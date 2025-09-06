<x-print-layout>
    <div class="max-w-2xl mx-auto bg-white p-8">
        <div class="text-center border-b pb-4">
            <h1 class="text-2xl font-bold">{{ $reservation->property->name }}</h1>
            <p class="text-sm text-gray-600">{{ $reservation->property->address }}</p>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
            <div>
                <h2 class="font-semibold text-gray-800">TANDA TERIMA UNTUK:</h2>
                <p>{{ $reservation->guest_name }}</p>
                <p>{{ $reservation->guest_address }}</p>
                <p>{{ $reservation->guest_phone }}</p>
            </div>
            <div class="text-right">
                <p><span class="font-semibold">No. Reservasi:</span> #{{ $reservation->id }}</p>
                <p><span class="font-semibold">Tanggal Cetak:</span> {{ now()->isoFormat('D MMM YYYY') }}</p>
                <p><span class="font-semibold">Kamar:</span> {{ $reservation->hotelRoom->room_number }}</p>
                <p><span class="font-semibold">Check-in:</span> {{ $reservation->checkin_date->isoFormat('D MMM YYYY') }}</p>
                <p><span class="font-semibold">Check-out:</span> {{ $reservation->checkout_date->isoFormat('D MMM YYYY') }}</p>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-lg font-semibold border-b pb-2 mb-2">Rincian Transaksi</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 text-left font-semibold">Tanggal</th>
                        <th class="py-2 text-left font-semibold">Deskripsi</th>
                        <th class="py-2 text-right font-semibold">Tagihan</th>
                        <th class="py-2 text-right font-semibold">Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($folio->items as $item)
                        <tr class="border-b">
                            <td class="py-2">{{ $item->created_at->isoFormat('D MMM YYYY') }}</td>
                            <td class="py-2">{{ $item->description }}</td>
                            <td class="py-2 text-right">
                                @if($item->type === 'charge')
                                    {{ number_format($item->amount, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                @if($item->type === 'payment')
                                    {{ number_format($item->amount, 0, ',', '.') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            <div class="w-full max-w-sm text-sm">
                <div class="flex justify-between py-1">
                    <span>Subtotal:</span>
                    <span>Rp {{ number_format($folio->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span>Layanan (10%):</span>
                    <span>Rp {{ number_format($folio->service_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1 border-b">
                    <span>Pajak (11%):</span>
                    <span>Rp {{ number_format($folio->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1 font-bold">
                    <span>Grand Total:</span>
                    <span>Rp {{ number_format($folio->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span>Total Pembayaran:</span>
                    <span>Rp {{ number_format($folio->total_payments, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between py-2 border-t-2 mt-2 font-bold text-base">
                    @if ($folio->balance < 0)
                        <span>Uang Kembalian:</span>
                        <span>Rp {{ number_format(abs($folio->balance), 0, ',', '.') }}</span>
                    @else
                        <span>Saldo Akhir:</span>
                        <span>Rp {{ number_format($folio->balance, 0, ',', '.') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-12 text-center text-xs text-gray-500">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p>Dokumen ini dicetak oleh sistem pada {{ now()->isoFormat('D MMMM YYYY, HH:mm') }}</p>
        </div>
    </div>
</x-print-layout>