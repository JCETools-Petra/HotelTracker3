<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menambahkan kolom baru ke tabel reservations
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('status')->default('Booked')->after('segment'); // Status: Booked, Checked-in, Checked-out
            $table->string('key_number')->nullable()->after('status'); // Nomor Kunci Kamar
            $table->timestamp('checked_in_at')->nullable()->after('key_number');
            $table->timestamp('checked_out_at')->nullable()->after('checked_in_at');
        });

        // Membuat tabel baru untuk Folio (tagihan utama per reservasi)
        Schema::create('folios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->decimal('total_charges', 15, 2)->default(0);
            $table->decimal('total_payments', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });

        // Membuat tabel baru untuk Folio Items (rincian item di dalam folio)
        Schema::create('folio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['charge', 'payment']); // Jenis item: tagihan atau pembayaran
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_items');
        Schema::dropIfExists('folios');
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['status', 'key_number', 'checked_in_at', 'checked_out_at']);
        });
    }
};