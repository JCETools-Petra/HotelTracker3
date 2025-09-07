<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek dulu sebelum menambahkan kolom
            if (!Schema::hasColumn('users', 'restaurant_id')) {
                $table->foreignId('restaurant_id')->nullable()->constrained()->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cek dulu sebelum menghapus
            if (Schema::hasColumn('users', 'restaurant_id')) {
                $table->dropForeign(['restaurant_id']);
                $table->dropColumn('restaurant_id');
            }
        });
    }
};