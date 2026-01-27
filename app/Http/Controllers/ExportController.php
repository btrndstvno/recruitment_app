<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Applicant;

class ExportController extends Controller
{
    /**
     * Export all applicants (default export all, bisa diubah sesuai kebutuhan)
     */
    public function export(Request $request)
    {
        // Export semua data applicant
        $applicants = Applicant::all();

        // Jika sudah ada fitur export Excel (Maatwebsite\Excel), gunakan itu
        if (class_exists('Maatwebsite\\Excel\\Facades\\Excel') && class_exists('App\\Exports\\ApplicantsExport')) {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ApplicantsExport($applicants), 'applicants.xlsx');
        }

        // Fallback: Export CSV sederhana
        $filename = 'applicants.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $columns = [
            'id', 'applicant_number', 'nama_lengkap', 'alamat', 'kota', 'provinsi', 'no_hp_1', 'no_hp_2', 'no_ktp',
            'tempat_lahir', 'tanggal_lahir', 'gender', 'EEO Age', 'tanggal_lamaran', 'tanggal_test', 'nama_sekolah',
            'jurusan', 'tahun_lulus', 'ipk', 'is_guru', 'is_pkl', 'pkl_awal', 'pkl_akhir', 'pkl_asal_sekolah',
            'pkl_jurusan', 'pkl_tempat', 'catatan', 'color_code', 'status', 'archived_at', 'created_at', 'updated_at'
        ];
        $callback = function() use ($applicants, $columns) {
            $file = fopen('php://output', 'w');
            // Header
            fputcsv($file, $columns);
            // Data
            foreach ($applicants as $applicant) {
                $row = [];
                foreach ($columns as $col) {
                    $row[] = $applicant[$col] ?? '';
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
