<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        // Cek dulu: Jika kolom 'color_code' BELUM ADA, baru tambahkan.
        if (!Schema::hasColumn('applicants', 'color_code')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->string('color_code')->nullable()->after('status');
            });
        }
    }
};