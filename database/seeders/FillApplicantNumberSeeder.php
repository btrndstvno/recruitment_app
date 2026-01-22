<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillApplicantNumberSeeder extends Seeder
{
    public function run(): void
    {
        // Cari pelamar yang belum punya nomor
        $applicants = DB::table('applicants')
            ->whereNull('applicant_number')
            ->orWhere('applicant_number', '')
            ->orderBy('id')
            ->get();

        foreach ($applicants as $app) {
            // Generate nomor otomatis: APP + ID (Contoh: APP00005)
            $newNumber = 'APP' . str_pad($app->id, 5, '0', STR_PAD_LEFT);
            
            DB::table('applicants')
                ->where('id', $app->id)
                ->update(['applicant_number' => $newNumber]);
        }
    }
}