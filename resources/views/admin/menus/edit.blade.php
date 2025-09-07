<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Menu Item') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.menus.update', $menu) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="menu_category_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Menu Category</label>
                            <select id="menu_category_id" name="menu_category_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Select a Category</option>
                                @foreach ($menuCategories as $category)
                                    <option value="{{ $category->id }}" {{ old('menu_category_id', $menu->menu_category_id) == $category->id ? 'selected' : '' }}>
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
                            <input type="text" name="name" id="name" value="{{ old('name', $menu->name) }}" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            @error('name')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="price" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Price</label>
                            <input type="number" name="price" id="price" value="{{ old('price', $menu->price) }}" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                             @error('price')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="is_available" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Availability</label>
                            <select id="is_available" name="is_available" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="1" {{ old('is_available', $menu->is_available) == 1 ? 'selected' : '' }}>Available</option>
                                <option value="0" {{ old('is_available', $menu->is_available) == 0 ? 'selected' : '' }}>Not Available</option>
                            </select>
                            @error('is_available')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.menus.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
                                Cancel
                            </a>
                            <x-primary-button>
                                Update Item
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>