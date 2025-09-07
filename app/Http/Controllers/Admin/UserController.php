<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Restaurant; // <-- Pastikan ini diimpor
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Menambahkan relasi restaurant untuk ditampilkan jika ada
        $users = User::with(['property', 'restaurant'])->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $properties = Property::orderBy('name')->get();
        $restaurants = Restaurant::orderBy('name')->get(); // <-- Mengambil data restoran
        
        // Menambahkan peran baru
        $roles = [
            'admin' => 'Admin',
            'owner' => 'Owner',
            'pengurus' => 'Pengurus',
            'manager_properti' => 'Manager Properti', // <-- Peran baru
            'restaurant' => 'Restaurant Staff',      // <-- Peran baru
            'pengguna_properti' => 'Pengguna Properti',
            'sales' => 'Sales',
            'hk' => 'Housekeeping',
            'online_ecommerce' => 'E-Commerce',
        ];
        return view('admin.users.create', compact('properties', 'restaurants', 'roles'));
    }

    public function store(Request $request)
    {
        // Menentukan peran mana yang butuh property_id atau restaurant_id
        $rolesRequiringProperty = ['pengguna_properti', 'sales', 'online_ecommerce', 'hk', 'manager_properti'];
        $rolesRequiringRestaurant = ['restaurant'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            // Menambahkan peran baru ke validasi
            'role' => ['required', Rule::in(['admin', 'owner', 'pengurus', 'manager_properti', 'restaurant', 'pengguna_properti', 'sales', 'online_ecommerce', 'hk'])],
            'property_id' => [Rule::requiredIf(in_array($request->input('role'), $rolesRequiringProperty)), 'nullable', 'exists:properties,id'],
            'restaurant_id' => [Rule::requiredIf(in_array($request->input('role'), $rolesRequiringRestaurant)), 'nullable', 'exists:restaurants,id'],
        ]);
        
        $property_id = in_array($validated['role'], $rolesRequiringProperty) ? $validated['property_id'] : null;
        $restaurant_id = in_array($validated['role'], $rolesRequiringRestaurant) ? $validated['restaurant_id'] : null;

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'property_id' => $property_id,
            'restaurant_id' => $restaurant_id, // <-- Menyimpan restaurant_id
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user)
    {
        $properties = Property::orderBy('name')->get();
        $restaurants = Restaurant::orderBy('name')->get(); // <-- Mengambil data restoran
        
        // Menambahkan peran baru
        $roles = [
            'admin' => 'Admin',
            'owner' => 'Owner',
            'pengurus' => 'Pengurus',
            'manager_properti' => 'Manager Properti', // <-- Peran baru
            'restaurant' => 'Restaurant Staff',      // <-- Peran baru
            'pengguna_properti' => 'Pengguna Properti',
            'sales' => 'Sales',
            'hk' => 'Housekeeping',
            'online_ecommerce' => 'E-Commerce',
        ];
        return view('admin.users.edit', compact('user', 'properties', 'restaurants', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $rolesRequiringProperty = ['pengguna_properti', 'sales', 'online_ecommerce', 'hk', 'manager_properti'];
        $rolesRequiringRestaurant = ['restaurant'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'owner', 'pengurus', 'manager_properti', 'restaurant', 'pengguna_properti', 'sales', 'online_ecommerce', 'hk'])],
            'property_id' => [Rule::requiredIf(in_array($request->input('role'), $rolesRequiringProperty)), 'nullable', 'exists:properties,id'],
            'restaurant_id' => [Rule::requiredIf(in_array($request->input('role'), $rolesRequiringRestaurant)), 'nullable', 'exists:restaurants,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        
        // Logika untuk memastikan hanya satu ID yang disimpan
        $user->property_id = in_array($validated['role'], $rolesRequiringProperty) ? $validated['property_id'] : null;
        $user->restaurant_id = in_array($validated['role'], $rolesRequiringRestaurant) ? $validated['restaurant_id'] : null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // $this->authorize('manage-data'); // Anda mungkin perlu menyesuaikan ini jika ada
        if ($user->id === 1) { // Asumsi ID 1 adalah Super Admin
            return redirect()->route('admin.users.index')->with('error', 'Super Admin tidak dapat dihapus.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dipindahkan ke sampah.');
    }
    
    public function trashed()
    {
        $users = User::onlyTrashed()->with(['property', 'restaurant'])->latest()->paginate(10);
        return view('admin.users.trashed', compact('users'));
    }

    public function restore($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.users.trashed')->with('success', 'Pengguna berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        User::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('admin.users.trashed')->with('success', 'Pengguna berhasil dihapus permanen.');
    }
}