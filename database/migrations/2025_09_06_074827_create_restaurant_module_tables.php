<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel untuk menyimpan data restoran
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('location')->nullable();
            $table->timestamps();
        });

        // Tabel untuk menyimpan daftar meja
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Cth: Meja 01, VIP A
            $table->string('status')->default('available'); // available, occupied
            $table->timestamps();
        });

        // Tabel untuk kategori menu
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Cth: Makanan Utama, Minuman, Appetizer
            $table->timestamps();
        });

        // Tabel untuk item menu
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // Tabel untuk pesanan
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained();
            $table->foreignId('table_id')->nullable()->constrained();
            $table->foreignId('reservation_id')->nullable()->constrained(); // Untuk tagihan ke kamar
            $table->string('status'); // Cth: new, completed, paid, cancelled
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('service_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->timestamps();
        });

        // Tabel untuk rincian item dalam pesanan
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('menu_id')->constrained();
            $table->integer('quantity');
            $table->decimal('price', 15, 2); // Harga saat dipesan
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menu_categories');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('restaurants');
    }
};