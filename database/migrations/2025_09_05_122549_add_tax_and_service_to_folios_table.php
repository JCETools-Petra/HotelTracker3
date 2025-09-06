<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folios', function (Blueprint $table) {
            // Nama kolom 'total_charges' diubah menjadi 'grand_total' agar lebih jelas
            $table->renameColumn('total_charges', 'grand_total');

            // Tambahkan kolom baru setelah reservation_id
            $table->decimal('subtotal', 15, 2)->default(0)->after('reservation_id');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('service_amount', 15, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('folios', function (Blueprint $table) {
            $table->renameColumn('grand_total', 'total_charges');
            $table->dropColumn(['subtotal', 'tax_amount', 'service_amount']);
        });
    }
};