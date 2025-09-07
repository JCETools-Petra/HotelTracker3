<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyIncomeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HotelRoomController as AdminHotelRoomController;
use App\Http\Controllers\Admin\IncomeController as AdminIncomeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\TargetController;
use App\Http\Controllers\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Admin\MiceCategoryController;
use App\Http\Controllers\Admin\PricePackageController;
use App\Http\Controllers\Admin\PricingRuleController;
use App\Http\Controllers\Admin\RevenueTargetController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\Sales\BookingController;
use App\Http\Controllers\Sales\CalendarController as SalesCalendarController;
use App\Http\Controllers\Sales\DashboardController as SalesDashboardController;
use App\Http\Controllers\Sales\DocumentController;
use App\Http\Controllers\Housekeeping\InventoryController;
use App\Http\Controllers\Housekeeping\RoomStatusController;
use App\Http\Controllers\Ecommerce\BarDisplayController;
use App\Http\Controllers\Ecommerce\DashboardController as EcommerceDashboardController;
use App\Http\Controllers\Ecommerce\ReservationController as EcommerceReservationController;
use App\Http\Controllers\FolioController;
use App\Http\Controllers\FrontOfficeController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PosController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if (in_array($user->role, ['admin', 'owner', 'pengurus'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'manager_properti') {
            return redirect()->route('property.dashboard');
        } elseif ($user->role === 'restaurant') {
            return redirect()->route('admin.pos.index');
        } elseif ($user->role === 'pengguna_properti') {
            return redirect()->route('property.dashboard');
        } elseif ($user->role === 'sales') {
            return redirect()->route('sales.dashboard');
        } elseif ($user->role === 'online_ecommerce') {
            return redirect()->route('ecommerce.dashboard');
        } elseif ($user->role === 'hk') {
            return redirect()->route('housekeeping.room-status.index');
        }
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        $user = Auth::user();
        if (in_array($user->role, ['admin', 'owner', 'pengurus'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'manager_properti') {
            return redirect()->route('property.dashboard');
        } elseif ($user->role === 'restaurant') {
            return redirect()->route('admin.pos.index');
        } elseif ($user->role === 'pengguna_properti') {
            return redirect()->route('property.dashboard');
        } elseif ($user->role === 'online_ecommerce') {
            return redirect()->route('ecommerce.dashboard');
        } elseif ($user->role === 'sales') {
            return redirect()->route('sales.dashboard');
        } elseif ($user->role === 'hk') {
            return redirect()->route('housekeeping.room-status.index');
        }
        abort(403, 'Tidak ada dashboard yang sesuai untuk peran Anda.');
    })->name('dashboard');
});

require __DIR__ . '/auth.php';


// Grup Admin (Laporan & Manajemen)
// PERBAIKAN UTAMA: Middleware utama hanya untuk akses umum ke prefix /admin
Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin,owner,pengurus,manager_properti,restaurant'])->name('admin.')->group(function () {

    // Rute Laporan (akses oleh admin, owner, pengurus)
    Route::middleware('role:admin,owner,pengurus')->group(function() {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/kpi-analysis', [AdminDashboardController::class, 'kpiAnalysis'])->name('kpi.analysis');
        Route::get('/kpi-analysis/export', [AdminDashboardController::class, 'exportKpiAnalysis'])->name('kpi.analysis.export');
        Route::get('/properties/compare', [AdminPropertyController::class, 'showComparisonForm'])->name('properties.compare_page');
        Route::get('/properties/compare/results', [AdminPropertyController::class, 'showComparisonResults'])->name('properties.compare.results');
        Route::get('properties/{property}', [AdminPropertyController::class, 'show'])->name('properties.show');
    });

    // Rute Manajemen (Admin & Owner)
    Route::middleware('role:admin,owner')->group(function() {
        Route::get('/dashboard/export/excel', [AdminDashboardController::class, 'exportPropertiesSummaryExcel'])->name('dashboard.export.excel');
        Route::get('/dashboard/export/csv', [AdminDashboardController::class, 'exportPropertiesSummaryCsv'])->name('dashboard.export.csv');
        Route::get('/sales-analytics', [AdminDashboardController::class, 'salesAnalytics'])->name('sales.analytics');
        Route::get('/calendar/unified', [AdminDashboardController::class, 'unifiedCalendar'])->name('calendar.unified');
        Route::get('/calendar/unified/events', [AdminDashboardController::class, 'getUnifiedCalendarEvents'])->name('calendar.unified.events');
        Route::resource('users', AdminUserController::class);
        Route::get('/users-trashed', [AdminUserController::class, 'trashed'])->name('users.trashed');
        Route::post('/users/{user}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
        Route::delete('/users/{user}/force-delete', [AdminUserController::class, 'forceDelete'])->name('users.force-delete');
        Route::resource('properties', AdminPropertyController::class)->except(['show']);
        Route::resource('revenue-targets', RevenueTargetController::class);
        Route::resource('targets', TargetController::class);
        Route::resource('mice-categories', MiceCategoryController::class);
        Route::resource('price-packages', PricePackageController::class);
        Route::get('/inventories/select', [AdminInventoryController::class, 'showPropertySelection'])->name('inventories.select');
        Route::get('/properties/{property}/inventories', [AdminInventoryController::class, 'index'])->name('inventories.index');
        Route::get('/properties/{property}/inventories/create', [AdminInventoryController::class, 'create'])->name('inventories.create');
        Route::post('/properties/{property}/inventories', [AdminInventoryController::class, 'store'])->name('inventories.store');
        Route::get('/inventories/{inventory}/edit', [AdminInventoryController::class, 'edit'])->name('inventories.edit');
        Route::put('/inventories/{inventory}', [AdminInventoryController::class, 'update'])->name('inventories.update');
        Route::delete('/inventories/{inventory}', [AdminInventoryController::class, 'destroy'])->name('inventories.destroy');
        Route::get('/reports/amenities', [AdminInventoryController::class, 'report'])->name('reports.amenities');
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity_log.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
        Route::resource('properties.rooms', AdminRoomController::class)->shallow();
        Route::resource('properties.room-types', RoomTypeController::class)->shallow();
        Route::resource('properties.hotel_rooms', AdminHotelRoomController::class)->shallow()->names('properties.hotel-rooms');
        Route::resource('properties.incomes', AdminIncomeController::class)->shallow();
        Route::post('properties/{property}/occupancy', [AdminPropertyController::class, 'updateOccupancy'])->name('properties.occupancy.update');
        Route::prefix('properties/{property}/pricing-rule')->name('pricing-rules.')->group(function () {
            Route::get('/', [PricingRuleController::class, 'index'])->name('index');
            Route::post('/store-room-type', [PricingRuleController::class, 'storeRoomType'])->name('room-type.store');
            Route::put('/update-pricing-rule/{roomType}', [PricingRuleController::class, 'updatePricingRule'])->name('rule.update');
            Route::delete('/destroy-room-type/{roomType}', [PricingRuleController::class, 'destroyRoomType'])->name('room-type.destroy');
            Route::put('/update-property-bars', [PricingRuleController::class, 'updatePropertyBars'])->name('property-bars.update');
        });
    });

    // Rute F&B (Akses oleh admin, owner, manager_properti)
    Route::middleware('role:admin,owner,manager_properti')->group(function () {
        Route::resource('restaurants', RestaurantController::class);
        Route::resource('menu-categories', MenuCategoryController::class);
        Route::resource('tables', TableController::class);
        Route::resource('menus', MenuController::class);
    });
    
    // Rute POS (Akses oleh semua peran di grup admin utama, karena sudah difilter di controller)
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/{restaurant}', [PosController::class, 'show'])->name('pos.show');
    Route::get('pos/order/table/{table}', [PosController::class, 'order'])->name('pos.order');
    Route::post('pos/order/{order}/add', [PosController::class, 'addItem'])->name('pos.order.add');
    Route::post('pos/order/item/{orderItem}/increase', [PosController::class, 'increaseItem'])->name('pos.order.increase');
    Route::post('pos/order/item/{orderItem}/decrease', [PosController::class, 'decreaseItem'])->name('pos.order.decrease');
    Route::delete('pos/order/item/{orderItem}/remove', [PosController::class, 'removeItem'])->name('pos.order.remove');
    Route::post('pos/order/{order}/complete', [PosController::class, 'completeOrder'])->name('pos.order.complete');
    Route::get('pos/order/{order}/print', [PosController::class, 'printBill'])->name('pos.order.print');
    Route::post('pos/order/{order}/cancel', [PosController::class, 'cancelOrder'])->name('pos.order.cancel');
    Route::post('pos/order/{order}/apply-discount', [PosController::class, 'applyDiscount'])->name('pos.order.discount');
    Route::post('pos/order/{order}/charge-to-room', [PosController::class, 'chargeToRoom'])->name('pos.order.charge');
    Route::get('pos/{restaurant}/room-service', [PosController::class, 'createRoomServiceOrder'])->name('pos.roomservice.create');

});

// Route Sales
Route::prefix('sales')->middleware(['auth', 'verified', 'role:sales,owner'])->name('sales.')->group(function () {
    Route::get('/dashboard', [SalesDashboardController::class, 'index'])->name('dashboard');
    Route::resource('bookings', BookingController::class);
    Route::get('/bookings/{booking}/download-beo', [BookingController::class, 'downloadBeo'])->name('bookings.download_beo');
    Route::get('/bookings/{booking}/show-beo', [BookingController::class, 'showBeo'])->name('bookings.show_beo');
    Route::get('/bookings/{booking}/beo', [BookingController::class, 'beo'])->name('bookings.beo');
    Route::post('/bookings/{booking}/beo', [BookingController::class, 'storeBeo'])->name('bookings.storeBeo');
    Route::get('/bookings/{booking}/beo/show', [BookingController::class, 'showBeo'])->name('bookings.showBeo');
    Route::get('/bookings/{booking}/beo/print', [BookingController::class, 'printBeo'])->name('bookings.printBeo');
    Route::get('/bookings/{booking}/quotation', [DocumentController::class, 'generateQuotation'])->name('documents.quotation');
    Route::get('/bookings/{booking}/invoice', [DocumentController::class, 'generateInvoice'])->name('documents.invoice');
    Route::get('/bookings/{booking}/beo/pdf', [DocumentController::class, 'generateBeo'])->name('documents.beo');
    Route::get('/calendar', [SalesCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [SalesCalendarController::class, 'events'])->name('calendar.events');
});

// Route Housekeeping
Route::prefix('housekeeping')->middleware(['auth', 'verified', 'role:hk,owner'])->name('housekeeping.')->group(function () {
    Route::get('/room-status', [RoomStatusController::class, 'index'])->name('room-status.index');
    Route::post('/room-status/{room}/update', [RoomStatusController::class, 'update'])->name('room-status.update');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/select-room', [InventoryController::class, 'selectRoom'])->name('inventory.select-room');
    Route::get('/inventory/assign/{room}', [InventoryController::class, 'assign'])->name('inventory.assign');
    Route::post('/inventory/update/{room}', [InventoryController::class, 'updateInventory'])->name('inventory.update');
    Route::get('/history', [InventoryController::class, 'history'])->name('inventory.history');
});

// Route Pengguna Properti
// PERBAIKAN: Menambahkan 'manager_properti' ke middleware
Route::prefix('property')->middleware(['auth', 'verified', 'role:pengguna_properti,owner,manager_properti'])->name('property.')->group(function () {
    Route::get('/dashboard', [PropertyIncomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/calendar', [PropertyIncomeController::class, 'calendar'])->name('calendar.index');
    Route::get('/calendar-data', [PropertyIncomeController::class, 'getCalendarData'])->name('calendar.data');
    
    // --- GRUP RUTE UNTUK FRONT OFFICE ---
    Route::prefix('front-office')->name('frontoffice.')->group(function () {
        Route::get('/', [FrontOfficeController::class, 'index'])->name('index');
        Route::post('/reservation', [FrontOfficeController::class, 'storeReservation'])->name('reservation.store');
        Route::post('/check-in/{reservation}', [FrontOfficeController::class, 'checkIn'])->name('check-in');
        Route::post('/cancel/{reservation}', [FrontOfficeController::class, 'cancel'])->name('cancel');
        Route::post('/hotel-room/{room}/update-status', [FrontOfficeController::class, 'updateRoomStatus'])->name('room.update-status');
    });

    // --- GRUP RUTE UNTUK FOLIO ---
    Route::prefix('folio')->name('folio.')->group(function () {
        Route::get('/{reservation}', [FolioController::class, 'show'])->name('show');
        Route::post('/{folio}/add-charge', [FolioController::class, 'addCharge'])->name('add-charge');
        Route::post('/{folio}/add-payment', [FolioController::class, 'addPayment'])->name('add-payment');
        Route::post('/{reservation}/process-checkout', [FolioController::class, 'processCheckout'])->name('process-checkout');
        Route::get('/{reservation}/print', [FolioController::class, 'printReceipt'])->name('print-receipt');
    });

    // Rute untuk Laporan Pendapatan (Income)
    Route::get('/income', [PropertyIncomeController::class, 'index'])->name('income.index');
    Route::get('/income/create', [PropertyIncomeController::class, 'create'])->name('income.create');
    Route::post('/income', [PropertyIncomeController::class, 'store'])->name('income.store');
    Route::get('/income/{income}/edit', [PropertyIncomeController::class, 'edit'])->name('income.edit');
    Route::put('/income/{income}', [PropertyIncomeController::class, 'update'])->name('income.update');
    Route::delete('/income/{income}', [PropertyIncomeController::class, 'destroy'])->name('income.destroy');
    Route::post('/occupancy/update', [PropertyIncomeController::class, 'updateOccupancy'])->name('occupancy.update');
});

// Route E-commerce
Route::prefix('ecommerce')->middleware(['auth', 'verified', 'role:online_ecommerce'])->name('ecommerce.')->group(function () {
    Route::get('/dashboard', [EcommerceDashboardController::class, 'index'])->name('dashboard');
    Route::resource('reservations', EcommerceReservationController::class);
    Route::get('/bar-prices', [BarDisplayController::class, 'index'])->name('bar-prices.index');
});