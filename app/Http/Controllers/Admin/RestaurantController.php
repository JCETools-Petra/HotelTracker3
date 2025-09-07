<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Otorisasi: Bolehkan user melihat daftar restoran?
        $this->authorize('viewAny', Restaurant::class);

        $user = Auth::user();
        $query = Restaurant::with('property');

        // Jika user adalah manager_properti, filter restoran berdasarkan propertinya
        if ($user->role === 'manager_properti') {
            $query->where('property_id', $user->property_id);
        }

        $restaurants = $query->latest()->paginate(10);
        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        // Otorisasi: Bolehkan user membuat restoran?
        $this->authorize('create', Restaurant::class);
        
        $user = Auth::user();
        $properties = collect();

        if ($user->role === 'admin') {
            $properties = Property::orderBy('name')->get();
        } elseif ($user->role === 'manager_properti') {
            // Manajer hanya bisa memilih propertinya sendiri
            $properties = Property::where('id', $user->property_id)->get();
        }

        return view('admin.restaurants.create', compact('properties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Restaurant::class);
        // 1. Validasi input
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        // 2. Buat dan simpan data restoran baru
        Restaurant::create($validated);

        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Kode untuk menampilkan detail satu restoran
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Restaurant $restaurant) // 1. Terima objek Restaurant
    {
        $this->authorize('update', $restaurant);
        // 2. Ambil semua property untuk dropdown
        $properties = Property::orderBy('name')->get();

        // 3. Tampilkan view edit dan kirim data restaurant serta properties
        return view('admin.restaurants.edit', compact('restaurant', 'properties'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $this->authorize('update', $restaurant);
        // 1. Validasi input
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        // 2. Update data restoran
        $restaurant->update($validated);

        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant) // 1. Terima objek Restaurant
    {
        $this->authorize('delete', $restaurant);
        // 2. Hapus data dari database
        $restaurant->delete();

        // 3. Redirect kembali dengan pesan sukses
        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant deleted successfully.');
    }
}