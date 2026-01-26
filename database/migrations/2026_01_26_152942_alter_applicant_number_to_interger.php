<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->integer('applicant_number')->nullable()->unique()->change();
        });
    }
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('applicant_number')->nullable()->unique()->change();
        });
    }
};
