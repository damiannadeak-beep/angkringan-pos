<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `absensis` MODIFY COLUMN `status` ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alpa') DEFAULT 'hadir'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `absensis` MODIFY COLUMN `status` ENUM('hadir', 'izin', 'sakit', 'alpa') DEFAULT 'hadir'");
        }
    }
};
