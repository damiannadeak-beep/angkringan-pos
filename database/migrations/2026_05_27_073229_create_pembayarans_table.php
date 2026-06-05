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
        Schema::create('pembayaran', function (Blueprint $table) {
    $table->id();
    $table->foreignId('id_pesanan')->constrained('pesanan')->onDelete('cascade');
    $table->enum('metode', ['cash', 'qris'])->nullable();
    $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
    $table->decimal('total_bayar', 12, 2)->default(0);
    $table->string('snap_token')->nullable();
    $table->dateTime('tanggal')->nullable();
    $table->timestamps();

    $table->index(['id_pesanan', 'status']); // Index untuk pengecekan status bayar
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
