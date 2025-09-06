<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_fo_columns_to_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menambahkan kolom untuk menautkan reservasi ke kamar spesifik
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('hotel_room_id')->nullable()->constrained()->onDelete('set null')->after('room_type_id');
        });

        // Menambahkan kolom status untuk kamar
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->string('status')->default('Tersedia')->after('room_number'); // Contoh status: Tersedia, Terisi, Kotor, Perbaikan
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['hotel_room_id']);
            $table->dropColumn('hotel_room_id');
        });

        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};