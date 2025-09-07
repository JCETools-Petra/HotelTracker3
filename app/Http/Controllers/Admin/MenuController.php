<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Tambahkan

class MenuController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Menu::class);
        $user = Auth::user();
        $query = Menu::with('menuCategory.restaurant');

        if ($user->role === 'manager_properti') {
            $query->whereHas('menuCategory.restaurant', function ($q) use ($user) {
                $q->where('property_id', $user->property_id);
            });
        }

        $menus = $query->latest()->paginate(15);
        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        $this->authorize('create', Menu::class);
        $user = Auth::user();

        $menuCategories = MenuCategory::with('restaurant')
            ->when($user->role === 'manager_properti', function ($query) use ($user) {
                $query->whereHas('restaurant', function ($q) use ($user) {
                    $q->where('property_id', $user->property_id);
                });
            })
            ->orderBy('name')
            ->get();
            
        // =======================================================
        // TAMBAHKAN BARIS DEBUG INI:
        //dd(config('view.paths'));
        // =======================================================
            
        return view('admin.menus.create', compact('menuCategories'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Menu::class);
        // 1. Validasi input
        $validated = $request->validate([
            'menu_category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $menuCategory = MenuCategory::findOrFail($validated['menu_category_id']);
        $this->authorize('update', $menuCategory); // Gunakan MenuCategoryPolicy

        Menu::create($validated);

        // 3. Redirect ke halaman index dengan pesan sukses
        return redirect()->route('admin.menus.index')->with('success', 'Menu item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu) // 1. Terima objek Menu
    {
        $this->authorize('update', $menu);
        // 2. Ambil semua kategori menu untuk dropdown
        $menuCategories = MenuCategory::with('restaurant')->orderBy('name')->get();

        // 3. Tampilkan view edit dan kirim data yang diperlukan
        return view('admin.menus.edit', compact('menu', 'menuCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $this->authorize('update', $menu);
        // 1. Validasi input
        $validated = $request->validate([
            'menu_category_id' => 'required|exists:menu_categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_available' => 'required|boolean',
        ]);

        // 2. Update data yang ada
        $menu->update($validated);

        // 3. Redirect ke halaman index dengan pesan sukses
        return redirect()->route('admin.menus.index')->with('success', 'Menu item updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        $this->authorize('delete', $menu);
        $menu->delete();
        return redirect()->route('admin.menus.index')->with('success', 'Menu item deleted successfully.');
    }
}
