<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Menu Item') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.menus.store') }}" method="POST">
                        @csrf

                        {{-- ====================================================== --}}
                        {{-- HAPUS ATAU BERI KOMENTAR BLOK KODE DI BAWAH INI --}}
                        {{-- ====================================================== --}}
                        {{-- 
                        <div class="mb-4">
                            <label for="restaurant_id" class="block text-sm font-medium text-gray-700">Restaurant</label>
                            <select id="restaurant_id" name="restaurant_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select a Restaurant</option>
                                @foreach ($restaurants as $restaurant)
                                    <option value="{{ $restaurant->id }}" {{ old('restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                                        {{ $restaurant->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('restaurant_id')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        --}}
                        {{-- ====================================================== --}}
                        {{-- AKHIR DARI BLOK YANG DIHAPUS --}}
                        {{-- ====================================================== --}}


                        <div class="mb-4">
                            <label for="menu_category_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Menu Category</label>
                            <select id="menu_category_id" name="menu_category_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select a Category</option>
                                @foreach ($menuCategories as $category)
                                    <option value="{{ $category->id }}" {{ old('menu_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }} ({{ $category->restaurant->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('menu_category_id')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Item Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="e.g., Nasi Goreng Spesial">
                            @error('name')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="price" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Price</label>
                            <input type="number" name="price" id="price" value="{{ old('price') }}" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="e.g., 50000">
                             @error('price')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.menus.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
                                Cancel
                            </a>
                            <x-primary-button>
                                Save Item
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>