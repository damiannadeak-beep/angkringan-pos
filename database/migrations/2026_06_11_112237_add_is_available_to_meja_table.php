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
        Schema::table('meja', function (Blueprint $table) {
            $table->boolean('is_available')->default(true)->after('nama_meja_atau_nomor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meja', function (Blueprint $table) {
            $table->dropColumn('is_available');
        });
    }
};
