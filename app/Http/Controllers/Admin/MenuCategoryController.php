<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class MenuCategoryController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', MenuCategory::class);
        $user = Auth::user();
        $query = MenuCategory::with('restaurant');

        // Filter berdasarkan properti manajer
        if ($user->role === 'manager_properti') {
            $query->whereHas('restaurant', function ($q) use ($user) {
                $q->where('property_id', $user->property_id);
            });
        }

        $menuCategories = $query->latest()->paginate(10);
        return view('admin.menu_categories.index', compact('menuCategories'));
    }

    public function create()
    {
        $this->authorize('create', MenuCategory::class);
        $user = Auth::user();
        
        // Ambil restoran hanya dari properti milik manajer
        $restaurants = Restaurant::query()
            ->when($user->role === 'manager_properti', function ($query) use ($user) {
                return $query->where('property_id', $user->property_id);
            })
            ->orderBy('name')
            ->get();
            
        return view('admin.menu_categories.create', compact('restaurants'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', MenuCategory::class);
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
        ]);
        
        // Otorisasi tambahan: pastikan manajer tidak membuat kategori di restoran orang lain
        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);
        $this->authorize('update', $restaurant); // Memakai policy Restaurant untuk cek kepemilikan

        MenuCategory::create($validated);
        return redirect()->route('admin.menu-categories.index')->with('success', 'Menu category created successfully.');
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
    public function edit(MenuCategory $menuCategory) // 1. Terima objek MenuCategory
    {
        $this->authorize('update', $menuCategory);
        // 2. Ambil semua restoran untuk dropdown
        $restaurants = Restaurant::orderBy('name')->get();

        // 3. Tampilkan view edit dan kirim data yang diperlukan
        return view('admin.menu_categories.edit', compact('menuCategory', 'restaurants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MenuCategory $menuCategory)
    {
        $this->authorize('update', $menuCategory);
        // 1. Validasi input
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
        ]);

        // 2. Update data yang ada
        $menuCategory->update($validated);

        // 3. Redirect ke halaman index dengan pesan sukses
        return redirect()->route('admin.menu-categories.index')->with('success', 'Menu category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MenuCategory $menuCategory)
    {
        $this->authorize('delete', $menuCategory);
        $menuCategory->delete();
        return redirect()->route('admin.menu-categories.index')->with('success', 'Menu category deleted successfully.');
    }
}
