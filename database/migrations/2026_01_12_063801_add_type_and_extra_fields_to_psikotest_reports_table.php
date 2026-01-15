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
        Schema::table('psikotest_reports', function (Blueprint $table) {
            // Report type: 34 or 38
            $table->enum('report_type', ['34', '38'])->default('34')->after('applicant_id');
            
            // Extra fields for 38-question version (Aspek B additional)
            $table->tinyInteger('persepsi_ruang_bidang')->nullable()->after('kemampuan_penalaran_non_verbal');
            $table->tinyInteger('kemampuan_dasar_mekanik')->nullable()->after('persepsi_ruang_bidang');
            $table->tinyInteger('kemampuan_mengidentifikasi_komponen')->nullable()->after('kemampuan_dasar_mekanik');
            $table->tinyInteger('kemampuan_bekerja_dengan_angka_computational')->nullable()->after('kemampuan_mengidentifikasi_komponen');
            $table->tinyInteger('kemampuan_penalaran_mekanik')->nullable()->after('kemampuan_bekerja_dengan_angka_computational');
            $table->tinyInteger('kemampuan_merakit_objek')->nullable()->after('kemampuan_penalaran_mekanik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('psikotest_reports', function (Blueprint $table) {
            $table->dropColumn([
                'report_type',
                'persepsi_ruang_bidang',
                'kemampuan_dasar_mekanik',
                'kemampuan_mengidentifikasi_komponen',
                'kemampuan_bekerja_dengan_angka_computational',
                'kemampuan_penalaran_mekanik',
                'kemampuan_merakit_objek',
            ]);
        });
    }
};
