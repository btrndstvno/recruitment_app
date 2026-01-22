<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            // Kolom unik untuk menghubungkan excel Applicant, Edukasi, & Psikotest
            if (!Schema::hasColumn('applicants', 'applicant_number')) {
                $table->string('applicant_number')->nullable()->unique()->after('id');
            }
            
            // Kolom untuk kode warna
            if (!Schema::hasColumn('applicants', 'color_code')) {
                $table->string('color_code')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['applicant_number', 'color_code']);
        });
    }
};