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
            return $user->restaurant_id
                ? redirect()->route('admin.pos.show', $user->restaurant_id)
                : redirect()->route('admin.dashboard')->with('error', 'Your account is not associated with any restaurant.');
        }

        $query = Restaurant::query();
        if ($user->role === 'manager_properti') {
            $query->where('property_id', $user->property_id);
        }
        $restaurants = $query->get();

        return view('admin.pos.index', compact('restaurants'));
    }

    public function show(Restaurant $restaurant)
    {
        $this->authorize('viewPos', $restaurant);
        // PERBAIKAN: Muat relasi 'pendingOrder' untuk setiap meja
        $tables = $restaurant->tables()->with('pendingOrder')->get();
        return view('admin.pos.show', compact('restaurant', 'tables'));
    }

    public function findOrCreateOrderForTable(Table $table)
    {
        // Otorisasi untuk memastikan user boleh mengakses POS ini
        $this->authorize('viewPos', $table->restaurant);

        // Cari pesanan yang masih 'pending' untuk meja ini.
        // Jika tidak ada, 'firstOrCreate' akan membuat yang baru secara otomatis.
        $order = Order::firstOrCreate(
            ['table_id' => $table->id, 'status' => 'pending'],
            ['restaurant_id' => $table->restaurant_id, 'user_id' => Auth::id()]
        );

        // Ambil semua data yang dibutuhkan oleh halaman order
        $order->load('restaurant', 'table', 'items.menu', 'reservation.hotelRoom');
        
        $menuCategories = \App\Models\MenuCategory::where('restaurant_id', $order->restaurant_id)
                                    ->with('menus')
                                    ->get();
        
        $activeReservations = \App\Models\Reservation::where('property_id', $order->restaurant->property_id)
                                        ->where('status', 'checked-in')
                                        ->with('hotelRoom')
                                        ->get();

        // PERBAIKAN UTAMA: Langsung tampilkan view, jangan redirect.
        // Ini akan menghentikan loop 404.
        return view('admin.pos.order', compact('order', 'menuCategories', 'activeReservations'));
    }

    public function showOrder(Order $order)
    {
        // Otorisasi untuk memastikan user boleh melihat order ini
        $this->authorize('viewPos', $order->restaurant);
        
        // Memuat data-data terkait (relasi) yang dibutuhkan oleh halaman view
        $order->load('restaurant', 'table', 'items.menu', 'reservation.hotelRoom');
        
        // Ambil KATEGORI menu untuk ditampilkan di sisi kanan
        $menuCategories = \App\Models\MenuCategory::where('restaurant_id', $order->restaurant_id)
                                    ->with('menus') // Eager load menus untuk setiap kategori
                                    ->get();

        // PERBAIKAN: Ambil data reservasi aktif untuk modal "Charge to Room"
        $activeReservations = \App\Models\Reservation::where('property_id', $order->restaurant->property_id)
                                        ->where('status', 'checked-in')
                                        ->with('hotelRoom')
                                        ->get();

        // Kirim SEMUA data yang dibutuhkan (order, menuCategories, dan activeReservations) ke view
        return view('admin.pos.order', compact('order', 'menuCategories', 'activeReservations'));
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

    public function storeRoomServiceOrder(Request $request, Restaurant $restaurant)
    {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
        ]);

        $reservation = Reservation::find($data['reservation_id']);

        // Buat order baru yang terhubung dengan reservasi
        $order = Order::create([
            'restaurant_id' => $restaurant->id,
            'table_id' => null, // Room service tidak menggunakan meja
            'user_id' => Auth::id(),
            'status' => 'pending',
            'reservation_id' => $reservation->id, // Hubungkan dengan reservasi
        ]);

        // Arahkan ke halaman order untuk pesanan yang baru dibuat
        return redirect()->route('admin.pos.order.show', $order);
    }

    public function chargeToRoom(Request $request, Order $order)
    {
        if (!$order->reservation_id) {
            return back()->with('error', 'This order is not linked to a room.');
        }

        $folio = $order->reservation->folio;
        if (!$folio) {
            return back()->with('error', 'Folio for this guest was not found.');
        }

        $folio->items()->create([
            'description' => 'Restaurant Charge - Order #' . $order->id,
            'amount' => $order->grand_total,
            'type' => 'charge',
        ]);

        $folio->recalculate();
        $order->update(['status' => 'completed']);

        return redirect()->route('admin.pos.show', $order->restaurant)
            ->with('success', 'Bill successfully charged to Room ' . $order->reservation->hotelRoom->room_number);
    }

    public function addItem(Request $request, Order $order)
    {
        $request->validate(['menu_id' => 'required|exists:menus,id']);
        $menu = \App\Models\Menu::find($request->menu_id);
        $this->authorize('viewPos', $order->restaurant);

        $orderItem = $order->items()->where('menu_id', $menu->id)->first();

        if ($orderItem) {
            // Jika item sudah ada, tambah kuantitasnya
            $orderItem->increment('quantity');
        } else {
            // Jika item baru, buat entri baru
            $order->items()->create([
                'menu_id' => $menu->id,
                'quantity' => 1,
                'price' => $menu->price,
                // PERBAIKAN: Tambahkan 'total_price' saat membuat item baru.
                // Karena kuantitasnya 1, total_price sama dengan harga menu.
                'total_price' => $menu->price,
            ]);
        }
        
        // Hitung ulang total pesanan setelah ada perubahan
        $this->recalculateOrderTotal($order);

        return back()->with('success', 'Item added to order.');
    }

    public function decreaseItem(OrderItem $orderItem)
    {
        $this->authorize('viewPos', $orderItem->order->restaurant);
        if ($orderItem->quantity > 1) {
            $orderItem->decrement('quantity');
        } else {
            $orderItem->delete();
        }
        $this->recalculateOrderTotal($orderItem->order);
        return back();
    }

    public function removeItem(OrderItem $orderItem)
    {
        $this->authorize('viewPos', $orderItem->order->restaurant);
        $order = $orderItem->order;
        $orderItem->delete();
        $this->recalculateOrderTotal($order);
        return back()->with('success', 'Item removed from order.');
    }

    public function applyDiscount(Request $request, Order $order)
    {
        $this->authorize('viewPos', $order->restaurant);
        $request->validate(['discount_type' => 'required|in:percentage,fixed', 'discount_value' => 'required|numeric|min:0']);
        $order->update(['discount_type' => $request->discount_type, 'discount_value' => $request->discount_value]);
        $this->recalculateOrderTotal($order);
        return back()->with('success', 'Discount applied successfully.');
    }

    public function completeOrder(Order $order)
    {
        $this->authorize('viewPos', $order->restaurant);
        $order->update(['status' => 'completed']);
        if ($order->table) {
            $order->table->update(['status' => 'available']);
        }
        return redirect()->route('admin.pos.show', $order->restaurant_id)->with('success', "Order #{$order->id} has been completed and paid.");
    }

    public function cancelOrder(Order $order)
    {
        $this->authorize('viewPos', $order->restaurant);
        $order->update(['status' => 'cancelled']);
        if ($order->table) {
            $order->table->update(['status' => 'available']);
        }
        return redirect()->route('admin.pos.show', $order->restaurant_id)->with('success', "Order #{$order->id} has been cancelled.");
    }

    public function printBill(Order $order)
    {
        $this->authorize('viewPos', $order->restaurant);
        return view('admin.pos.print', compact('order'));
    }

    private function recalculateOrderTotal(Order $order)
    {
        $order->load('items');
        $subtotal = $order->items->sum(function($item) {
            return $item->quantity * $item->price;
        });
        
        $discountAmount = 0;
        if ($order->discount_type === 'percentage') {
            $discountAmount = $subtotal * ($order->discount_value / 100);
        } elseif ($order->discount_type === 'fixed') {
            $discountAmount = $order->discount_value;
        }
        if ($discountAmount > $subtotal) { $discountAmount = $subtotal; }
        
        $taxableAmount = $subtotal - $discountAmount;
        $taxRate = $order->restaurant->tax_rate ?? 0.11; // 11% default
        $taxAmount = $taxableAmount * $taxRate;
        
        $grandTotal = $taxableAmount + $taxAmount;

        $order->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
        ]);
    }

    public function history(Restaurant $restaurant)
    {
        $this->authorize('viewPos', $restaurant);

        // Ambil semua pesanan yang sudah selesai atau dibatalkan untuk restoran ini
        $orders = \App\Models\Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->with(['table', 'reservation.hotelRoom']) // Ambil juga data meja atau kamar
            ->latest() // Urutkan dari yang terbaru
            ->paginate(20); // Batasi 20 pesanan per halaman

        return view('admin.pos.history', compact('restaurant', 'orders'));
    }
    
}