<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Menambahkan kolom untuk tipe diskon (persen atau nominal)
            $table->string('discount_type')->nullable()->after('grand_total');
            // Menambahkan kolom untuk nilai diskon
            $table->decimal('discount_value', 15, 2)->default(0)->after('discount_type');
            // Menambahkan kolom untuk jumlah diskon yang sudah dihitung
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'discount_amount']);
        });
    }
};