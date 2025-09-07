<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Pengguna Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        {{-- Nama --}}
                        <div>
                            <x-input-label for="name" :value="__('Nama')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        {{-- Email --}}
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        {{-- Peran (Role) --}}
                        <div class="mt-4">
                            <x-input-label for="role" :value="__('Peran (Role)')" />
                            <select name="role" id="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required onchange="toggleConditionalFields(this.value)">
                                <option value="">-- Pilih Peran --</option>
                                @foreach($roles as $roleValue => $roleLabel)
                                    <option value="{{ $roleValue }}" {{ old('role') == $roleValue ? 'selected' : '' }}>
                                        {{ $roleLabel }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        {{-- Pilihan Properti (Conditional) --}}
                        <div class="mt-4" id="property-select-container" style="display: none;">
                            <x-input-label for="property_id" :value="__('Properti yang Dikelola')" />
                            <select name="property_id" id="property_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">-- Pilih Properti --</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('property_id')" class="mt-2" />
                        </div>
                        
                        {{-- Pilihan Restoran (Conditional) --}}
                        <div class="mt-4" id="restaurant-select-container" style="display: none;">
                            <x-input-label for="restaurant_id" :value="__('Restoran Tempat Bekerja')" />
                            <select name="restaurant_id" id="restaurant_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">-- Pilih Restoran --</option>
                                @foreach($restaurants as $restaurant)
                                    <option value="{{ $restaurant->id }}" {{ old('restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                                        {{ $restaurant->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('restaurant_id')" class="mt-2" />
                        </div>
                        
                        {{-- Password --}}
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        
                        {{-- Konfirmasi Password --}}
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">Batal</a>
                            <x-primary-button>
                                {{ __('Simpan Pengguna') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleConditionalFields(role) {
            const propertyContainer = document.getElementById('property-select-container');
            const propertySelect = document.getElementById('property_id');
            const restaurantContainer = document.getElementById('restaurant-select-container');
            const restaurantSelect = document.getElementById('restaurant_id');
            
            // Definisikan peran mana yang butuh field apa
            const rolesRequiringProperty = ['pengguna_properti', 'sales', 'online_ecommerce', 'hk', 'manager_properti'];
            const rolesRequiringRestaurant = ['restaurant'];

            // Logika untuk menampilkan/menyembunyikan field Properti
            if (rolesRequiringProperty.includes(role)) {
                propertyContainer.style.display = 'block';
                propertySelect.required = true;
            } else {
                propertyContainer.style.display = 'none';
                propertySelect.required = false;
                propertySelect.value = '';
            }

            // Logika untuk menampilkan/menyembunyikan field Restoran
            if (rolesRequiringRestaurant.includes(role)) {
                restaurantContainer.style.display = 'block';
                restaurantSelect.required = true;
            } else {
                restaurantContainer.style.display = 'none';
                restaurantSelect.required = false;
                restaurantSelect.value = '';
            }
        }

        // Panggil fungsi ini saat halaman dimuat untuk menangani jika ada error validasi (old value)
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            if (roleSelect.value) {
                toggleConditionalFields(roleSelect.value);
            }
        });
    </script>
    @endpush
</x-app-layout>