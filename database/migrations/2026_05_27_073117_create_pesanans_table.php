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
        Schema::create('pesanan', function (Blueprint $table) {
    $table->id();
    // Menggunakan strict constrained untuk integritas data
    $table->foreignId('id_konsumen')->constrained('users')->onDelete('restrict');
    $table->foreignId('id_meja')->constrained('meja')->onDelete('restrict');
    $table->foreignId('id_kasir')->nullable()->constrained('users')->onDelete('set null');
    
    $table->enum('tipe_pesanan', ['dine_in', 'takeaway']);
    $table->dateTime('tanggal');
    $table->decimal('total', 12, 2)->default(0);
    $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
    $table->timestamps();

    // Composite Index: Sangat krusial untuk fitur "Open Bill" mencari pesanan aktif di meja tertentu
    $table->index(['id_meja', 'status']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
