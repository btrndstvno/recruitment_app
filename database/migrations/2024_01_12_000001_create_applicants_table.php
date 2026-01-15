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
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            
            // Data Pribadi
            $table->string('nama_lengkap');
            $table->text('alamat');
            $table->string('kota');
            $table->string('provinsi');
            $table->string('no_hp_1');
            $table->string('no_hp_2')->nullable();
            $table->string('no_ktp');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('gender', ['Laki-laki', 'Perempuan']);
            $table->integer('umur');
            
            // Data Lamaran
            $table->date('tanggal_lamaran');
            
            // Data Pendidikan
            $table->string('nama_sekolah');
            $table->string('jurusan');
            $table->year('tahun_lulus')->nullable();
            $table->decimal('ipk', 3, 2)->nullable();
            
            // Pilihan Tambahan
            $table->boolean('is_guru')->default(false);
            $table->boolean('is_pkl')->default(false);
            
            // Data PKL (jika PKL)
            $table->date('pkl_awal')->nullable();
            $table->date('pkl_akhir')->nullable();
            $table->string('pkl_asal_sekolah')->nullable();
            $table->string('pkl_jurusan')->nullable();
            $table->string('pkl_tempat')->nullable();
            
            // Extra field untuk catatan
            $table->text('catatan')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'tested', 'accepted', 'rejected'])->default('pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
