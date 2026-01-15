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
        Schema::create('psikotest_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')->constrained()->onDelete('cascade');
            $table->date('tanggal_test');
            
            // Intelligence Quotient
            $table->integer('iq_score')->nullable();
            $table->enum('iq_category', [
                'borderline',
                'dibawah_rata_rata',
                'rata_rata',
                'diatas_rata_rata',
                'superior',
                'very_superior'
            ])->nullable();
            
            // A. Kemampuan Intelektual Umum (1-7)
            $table->tinyInteger('kemampuan_memecahkan_masalah')->nullable(); // 1-5
            $table->tinyInteger('ruang_lingkup_pengetahuan')->nullable();
            $table->tinyInteger('kemampuan_berfikir_analitis')->nullable();
            $table->tinyInteger('kemampuan_bekerja_dengan_angka')->nullable();
            $table->tinyInteger('kemampuan_berfikir_logis')->nullable();
            $table->tinyInteger('kemampuan_berfikir_abstrak')->nullable();
            $table->tinyInteger('kemampuan_mengingat')->nullable();
            $table->decimal('xa_score', 4, 2)->nullable(); // Skor rata-rata bagian A
            
            // B. Kemampuan Khusus (8-18)
            $table->tinyInteger('kecepatan_dalam_bekerja')->nullable();
            $table->tinyInteger('ketelitian_dalam_bekerja')->nullable();
            $table->tinyInteger('kestabilan_dalam_bekerja')->nullable();
            $table->tinyInteger('ketahanan_kerja')->nullable();
            $table->tinyInteger('kemampuan_konsentrasi_persepsi')->nullable();
            $table->tinyInteger('kemampuan_berhitung')->nullable();
            $table->tinyInteger('kemampuan_mengemukakan_pendapat')->nullable();
            $table->tinyInteger('kemampuan_penalaran_non_verbal')->nullable();
            $table->tinyInteger('kemampuan_membaca_memahami_logis')->nullable();
            $table->tinyInteger('kemampuan_administrasi_kompleks')->nullable();
            $table->tinyInteger('sistematika_dalam_bekerja')->nullable();
            $table->decimal('xb_score', 4, 2)->nullable(); // Skor rata-rata bagian B
            
            // C. Kepribadian Dan Sikap Kerja (19-34)
            $table->tinyInteger('motivasi')->nullable();
            $table->tinyInteger('kemampuan_membuat_keputusan')->nullable();
            $table->tinyInteger('kemampuan_kerja_sama_kelompok')->nullable();
            $table->tinyInteger('kemampuan_menjadi_pemimpin')->nullable();
            $table->tinyInteger('kemampuan_berfikir_positif')->nullable();
            $table->tinyInteger('ketekunan_dalam_bekerja')->nullable();
            $table->tinyInteger('kejujuran_berpendapat')->nullable();
            $table->tinyInteger('tanggung_jawab_dalam_bekerja')->nullable();
            $table->tinyInteger('motif_dalam_berprestasi')->nullable();
            $table->tinyInteger('afiliasi')->nullable();
            $table->tinyInteger('motif_menolong_orang_lain')->nullable();
            $table->tinyInteger('kestabilan_emosi')->nullable();
            $table->tinyInteger('kematangan_sosial')->nullable();
            $table->tinyInteger('rasa_percaya_diri')->nullable();
            $table->tinyInteger('penyesuaian_diri')->nullable();
            $table->tinyInteger('kejujuran_dalam_bekerja')->nullable();
            $table->decimal('xc_score', 4, 2)->nullable(); // Skor rata-rata bagian C
            
            // Total Score
            $table->decimal('xt_score', 4, 2)->nullable(); // Skor total
            
            // Kesimpulan dan Saran
            $table->text('kesimpulan_saran')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psikotest_reports');
    }
};
