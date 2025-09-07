<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Reservation; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Folio;     
use App\Models\FolioItem; 

class PosController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'restaurant') {
            if ($user->restaurant_id) {
                return redirect()->route('admin.pos.show', $user->restaurant_id);
            } else {
                return redirect()->route('admin.dashboard')->with('error', 'Your account is not associated with any restaurant.');
            }
        }

        $query = Restaurant::query();
        if ($user->role === 'manager_properti') {
            $query->where('property_id', $user->property_id);
        }

        $restaurants = $query->get();

        return view('admin.pos.index', compact('restaurants'));
    }

    public function order(Table $table)
    {
        // Otorisasi: Pastikan user bisa mengakses meja ini (melalui restorannya)
        $this->authorize('viewPos', $table->restaurant);

        // Cari pesanan yang aktif (belum selesai/bayar) untuk meja ini
        $order = Order::where('table_id', $table->id)
                    ->whereNotIn('status', ['completed', 'paid', 'cancelled'])
                    ->first();

        // Jika TIDAK ADA pesanan aktif DAN meja tersedia, buat pesanan baru.
        if (!$order && $table->status == 'available') {
            $order = Order::create([
                'restaurant_id' => $table->restaurant_id,
                'table_id' => $table->id,
                'status' => 'new',
            ]);
            $table->update(['status' => 'occupied']);
        } 
        // Jika TIDAK ADA pesanan aktif TAPI meja tidak tersedia (kasus aneh), kembalikan.
        elseif (!$order && $table->status != 'available') {
            return redirect()->route('admin.pos.show', $table->restaurant_id)->with('error', 'Table is occupied but has no active order. Please check.');
        }

        // Ambil menu yang tersedia
        $menuCategories = $table->restaurant->menuCategories()->with(['menus' => function ($query) {
            $query->where('is_available', true);
        }])->get();
        
        // ======================================================
        // PERBAIKAN FINAL: Menggunakan whereHas untuk query yang lebih tegas
        // ======================================================
        $activeReservations = Reservation::where('property_id', $table->restaurant->property_id)
                                        ->where('status', 'checked-in')
                                        ->whereHas('hotelRoom') // <-- Mengganti has() dengan whereHas()
                                        ->with('hotelRoom')
                                        ->get();

        return view('admin.pos.order', compact('order', 'menuCategories', 'table', 'activeReservations'));
    }

    public function chargeToRoom(Request $request, Order $order)
    {
        $this->authorize('viewPos', $order->restaurant);

        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
        ]);

        $reservation = Reservation::find($request->reservation_id);

        // Pastikan reservasi memiliki folio
        if (!$reservation->folio) {
            return back()->with('error', 'Selected reservation does not have a folio. Cannot add charge.');
        }

        // 1. Tambahkan item baru ke folio
        $reservation->folio->items()->create([
            'description' => "Restaurant Bill - Order #" . $order->id,
            'amount' => $order->grand_total,
            'type' => 'charge', // Tipe item adalah 'charge' atau 'debit'
        ]);

        // 2. Perbarui status pesanan
        $order->update([
            'status' => 'billed_to_room',
            'reservation_id' => $reservation->id,
        ]);

        // 3. Kosongkan kembali meja
        if ($order->table) {
            $order->table->update(['status' => 'available']);
        }

        return redirect()->route('admin.pos.show', $order->restaurant_id)
                         ->with('success', "Order #{$order->id} has been successfully charged to Room " . ($reservation->hotel_room->number ?? 'N/A'));
    }

    /**
     * Tampilkan halaman POS untuk restoran yang dipilih.
     */
    public function show(Restaurant $restaurant)
    {
        // Otorisasi: Pastikan pengguna boleh mengakses POS ini.
        $this->authorize('viewPos', $restaurant);

        // Ambil semua meja dari restoran ini
        $tables = $restaurant->tables()->get();

        return view('admin.pos.show', compact('restaurant', 'tables'));
    }

    public function addItem(Request $request, Order $order)
    {
        // Validasi input
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
        ]);

        $menu = Menu::find($request->menu_id);

        // Otorisasi: Pastikan user bisa memodifikasi pesanan ini
        $this->authorize('viewPos', $order->restaurant);

        // Cek apakah item sudah ada di pesanan
        $orderItem = $order->items()->where('menu_id', $menu->id)->first();

        if ($orderItem) {
            // Jika sudah ada, tambah kuantitasnya
            $orderItem->increment('quantity');
            $orderItem->update(['total_price' => $orderItem->quantity * $orderItem->price]);
        } else {
            // Jika belum ada, buat item baru
            $order->items()->create([
                'menu_id' => $menu->id,
                'quantity' => 1,
                'price' => $menu->price, // Simpan harga saat ini
                'total_price' => $menu->price,
            ]);
        }
        
        // Hitung ulang total pesanan
        $this->recalculateOrderTotal($order);

        return back()->with('success', 'Item added to order.');
    }

    /**
     * Helper function untuk menghitung ulang total pesanan.
     */
    public function applyDiscount(Request $request, Order $order)
    {
        $this->authorize('viewPos', $order->restaurant);

        $request->validate([
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $order->update([
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
        ]);

        $this->recalculateOrderTotal($order);

        return back()->with('success', 'Discount applied successfully.');
    }

    private function recalculateOrderTotal(Order $order)
    {
        $order->load('items');

        $subtotal = $order->items->sum('total_price');
        
        // Kalkulasi Diskon
        $discountAmount = 0;
        if ($order->discount_type === 'percentage') {
            // Diskon persen dihitung dari subtotal
            $discountAmount = $subtotal * ($order->discount_value / 100);
        } elseif ($order->discount_type === 'fixed') {
            // Diskon nominal langsung mengurangi
            $discountAmount = $order->discount_value;
        }

        // Pastikan diskon tidak lebih besar dari subtotal
        if ($discountAmount > $subtotal) {
            $discountAmount = $subtotal;
        }
        
        // Kalkulasi Pajak setelah diskon
        $taxableAmount = $subtotal - $discountAmount;
        $taxRate = 0.11; // 11%
        $taxAmount = $taxableAmount * $taxRate;
        
        $grandTotal = $taxableAmount + $taxAmount;

        $order->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
        ]);
    }

    /**
     * Mengurangi kuantitas item pesanan.
     */
    public function decreaseItem(OrderItem $orderItem)
    {
        $this->authorize('viewPos', $orderItem->order->restaurant);

        if ($orderItem->quantity > 1) {
            $orderItem->decrement('quantity');
            $orderItem->update(['total_price' => $orderItem->quantity * $orderItem->price]);
        } else {
            // Jika kuantitas 1, hapus item
            $orderItem->delete();
        }

        $this->recalculateOrderTotal($orderItem->order);

        return back();
    }

    /**
     * Menghapus item dari pesanan.
     */
    public function removeItem(OrderItem $orderItem)
    {
        $this->authorize('viewPos', $orderItem->order->restaurant);
        
        $order = $orderItem->order; // Simpan order sebelum item dihapus
        $orderItem->delete();

        $this->recalculateOrderTotal($order);

        return back()->with('success', 'Item removed from order.');
    }

    public function completeOrder(Order $order)
    {
        $this->authorize('viewPos', $order->restaurant);

        // 1. Ubah status pesanan menjadi 'completed' atau 'paid'
        $order->update(['status' => 'completed']);

        // 2. Ambil meja yang terkait dan ubah statusnya menjadi 'available'
        if ($order->table) {
            $order->table->update(['status' => 'available']);
        }

        // 3. Arahkan kembali ke tampilan meja dengan pesan sukses
        return redirect()->route('admin.pos.show', $order->restaurant_id)->with('success', "Order #{$order->id} has been completed and paid.");
    }

    public function cancelOrder(Order $order)
    {
        $this->authorize('viewPos', $order->restaurant);

        // 1. Ubah status pesanan menjadi 'cancelled'
        $order->update(['status' => 'cancelled']);

        // 2. Kosongkan meja
        if ($order->table) {
            $order->table->update(['status' => 'available']);
        }

        return redirect()->route('admin.pos.show', $order->restaurant_id)->with('success', "Order #{$order->id} has been cancelled.");
    }

    public function printBill(Order $order)
    {
        $this->authorize('viewPos', $order->restaurant);
        
        // Kita akan buat view khusus untuk cetak
        return view('admin.pos.print', compact('order'));
    }

    public function createRoomServiceOrder(Restaurant $restaurant)
    {
        $this->authorize('viewPos', $restaurant);

        $activeReservations = Reservation::where('property_id', $restaurant->property_id)
                                          ->where('status', 'checked-in')
                                          ->with('hotelRoom')
                                          ->get();
        
        return view('admin.pos.roomservice_create', compact('restaurant', 'activeReservations'));
    }
}