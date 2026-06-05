<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->foreignId('promo_id')->nullable()->constrained('promos')->onDelete('set null');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_hpp', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropColumn(['promo_id', 'discount_amount', 'total_hpp']);
        });
    }
};
