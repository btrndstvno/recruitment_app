<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Kolom TTL menjadi nullable (gunakan try-catch agar aman)
        try {
            Schema::table('applicants', function (Blueprint $table) {
                // Ubah hanya jika kolomnya ada
                if (Schema::hasColumn('applicants', 'tempat_lahir')) {
                    $table->string('tempat_lahir')->nullable()->change();
                }
                if (Schema::hasColumn('applicants', 'tanggal_lahir')) {
                    $table->date('tanggal_lahir')->nullable()->change();
                }
            });
        } catch (\Exception $e) {
            // Abaikan error jika sudah nullable
        }

        // 2. Update Kolom UMUR (Metode Reset Total)
        // Kita hapus dulu kolom umur lama (jika ada) agar tidak konflik tipe data
        if (Schema::hasColumn('applicants', 'umur')) {
            try {
                Schema::table('applicants', function (Blueprint $table) {
                    $table->dropColumn('umur');
                });
            } catch (\Exception $e) {}
        }

        // Lalu buat ulang sebagai string untuk EEO Age
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('umur', 20)->nullable();
        });
    }

    public function down(): void
    {
        // Rollback: Kembalikan ke integer
        Schema::table('applicants', function (Blueprint $table) {
            if (Schema::hasColumn('applicants', 'umur')) {
                 $table->dropColumn('umur');
            }
        });
        
        Schema::table('applicants', function (Blueprint $table) {
             $table->integer('umur')->default(0); 
        });
    }
};