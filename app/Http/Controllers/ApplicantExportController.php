<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Support\Facades\Response;

class ApplicantExportController extends Controller
{
    public function export(Request $request)
    {
        // Only export applicants that are not archived
        $applicants = Applicant::whereNull('archived_at')->get();

        $filename = 'applicants_export_' . now()->format('Ymd_His') . '.xlsx';
        $path = storage_path('app/' . $filename);

        SimpleExcelWriter::create($path)
            ->addRows($applicants->map(function ($a) {
                return [
                    'Nama Lengkap' => $a->nama_lengkap,
                    'No KTP' => $a->no_ktp,
                    'Alamat' => $a->alamat,
                    'Kota' => $a->kota,
                    'Provinsi' => $a->provinsi,
                    'No HP 1' => $a->no_hp_1,
                    'No HP 2' => $a->no_hp_2,
                    'Tempat Lahir' => $a->tempat_lahir,
                    'Tanggal Lahir' => $a->tanggal_lahir ? date('d F Y', strtotime($a->tanggal_lahir)) : '',
                    'Gender' => $a->gender,
                    'Umur' => $a->umur,
                    'Tanggal Lamaran' => $a->tanggal_lamaran ? date('d F Y', strtotime($a->tanggal_lamaran)) : '',
                    'Tanggal Test' => $a->tanggal_test ? date('d F Y', strtotime($a->tanggal_test)) : '',
                    'Nama Sekolah' => $a->nama_sekolah,
                    'Jurusan' => $a->jurusan,
                    'Tahun Lulus' => $a->tahun_lulus,
                    'IPK' => $a->ipk,
                    'Status' => $a->status,
                    'Color Code' => $a->color_code === null || $a->color_code === '' ? 'abu-abu' : $a->color_code,
                ];
            })->toArray());

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
