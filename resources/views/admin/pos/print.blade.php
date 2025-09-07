<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill - Order #{{ $order->id }}</title>
    <style>
        body { font-family: monospace; margin: 0; padding: 10px; }
        .receipt { width: 300px; margin: auto; }
        .header { text-align: center; margin-bottom: 10px; }
        .item { display: flex; justify-content: space-between; }
        .total { display: flex; justify-content: space-between; font-weight: bold; border-top: 1px dashed #000; margin-top: 10px; padding-top: 5px; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <div class="header">
            <h3>{{ $order->restaurant->name }}</h3>
            <p>Order #{{ $order->id }}</p>
            <p>Table: {{ $order->table->name ?? 'N/A' }}</p>
            <p>{{ $order->created_at->format('d-M-Y H:i') }}</p>
        </div>
        <hr>
        @foreach($order->items as $item)
            <div class="item">
                <span>{{ $item->quantity }}x {{ $item->menu->name }}</span>
                <span>{{ number_format($item->total_price) }}</span>
            </div>
        @endforeach
        <hr>
        <div class="item">
            <span>Subtotal</span>
            <span>{{ number_format($order->subtotal) }}</span>
        </div>

        {{-- =============================================== --}}
        {{--  PERUBAHAN: Menampilkan Diskon Jika Ada         --}}
        {{-- =============================================== --}}
        @if($order->discount_amount > 0)
            <div class="item">
                <span>Discount
                    @if($order->discount_type == 'percentage')
                        ({{ $order->discount_value }}%)
                    @endif
                </span>
                <span>-{{ number_format($order->discount_amount) }}</span>
            </div>
        @endif
        {{-- =============================================== --}}
        {{--  AKHIR PERUBAHAN                               --}}
        {{-- =============================================== --}}

        <div class="item">
            <span>Tax</span>
            <span>{{ number_format($order->tax_amount) }}</span>
        </div>
        <div class="total">
            <span>TOTAL</span>
            <span>{{ number_format($order->grand_total) }}</span>
        </div>
        <div class="header" style="margin-top: 20px;">
            <p>Thank you!</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Again</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>