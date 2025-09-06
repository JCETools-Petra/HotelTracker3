<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_xxxxxx_add_segment_to_reservations_table.php

    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('segment')->default('Walk In')->after('source'); // Tambahkan kolom ini
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('segment');
        });
    }
};
