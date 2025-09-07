<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Tambahkan
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Table::class);
        $user = Auth::user();
        $query = Table::with('restaurant');

        if ($user->role === 'manager_properti') {
            $query->whereHas('restaurant', function ($q) use ($user) {
                $q->where('property_id', $user->property_id);
            });
        }

        $tables = $query->latest()->paginate(10);
        return view('admin.tables.index', compact('tables'));
    }

    public function create()
    {
        $this->authorize('create', Table::class);
        $user = Auth::user();

        $restaurants = Restaurant::query()
            ->when($user->role === 'manager_properti', function ($query) use ($user) {
                return $query->where('property_id', $user->property_id);
            })
            ->orderBy('name')
            ->get();

        return view('admin.tables.create', compact('restaurants'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Table::class);
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
        ]);

        $restaurant = Restaurant::findOrFail($validated['restaurant_id']);
        $this->authorize('update', $restaurant);

        Table::create($validated);
        return redirect()->route('admin.tables.index')->with('success', 'Table created successfully.');
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
    public function edit(Table $table) // 1. Terima objek Table
    {
        $this->authorize('update', $table);
        // 2. Ambil semua restoran untuk dropdown
        $restaurants = Restaurant::orderBy('name')->get();

        // 3. Tampilkan view edit dan kirim data yang diperlukan
        return view('admin.tables.edit', compact('table', 'restaurants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Table $table)
    {
        $this->authorize('update', $table);
        // 1. Validasi input
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'status' => ['required', Rule::in(['available', 'occupied'])],
        ]);

        // 2. Update data yang ada
        $table->update($validated);

        // 3. Redirect ke halaman index dengan pesan sukses
        return redirect()->route('admin.tables.index')->with('success', 'Table updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Table $table)
    {
        $this->authorize('delete', $table);
        $table->delete();
        return redirect()->route('admin.tables.index')->with('success', 'Table deleted successfully.');
    }
}
