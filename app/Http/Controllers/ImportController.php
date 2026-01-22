<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\PsikotestReport;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('import.index');
    }

    public function import(Request $request)
    {
        // 1. SETTING ANTI-TIMEOUT
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('excel_file');
        $path = $file->getPathname();
        $type = $file->getClientOriginalExtension(); 

        // 2. DETEKSI HEADER
        $reader = SimpleExcelReader::create($path, $type);
        $rows = $reader->getRows(); 
        $firstRow = $rows->first();
        
        if (!$firstRow) {
            return back()->with('error', 'File Excel kosong atau header tidak terbaca.');
        }

        $headers = array_keys($firstRow);

        // 3. LOGIKA PEMILAH OTOMATIS
        if (in_array('HRI_Applicant_Number_STR', $headers)) {
            return $this->processPsikotest($path, $type);
        } 
        elseif (in_array('University', $headers) && in_array('Graduation Year', $headers)) {
            return $this->processEducation($path, $type);
        } 
        elseif (in_array('Applicant Number', $headers) && (in_array('First Name', $headers))) {
            return $this->processApplicants($path, $type);
        } 
        else {
            $headerList = implode(', ', $headers);
            return back()->with('error', "Format file tidak dikenali. Header terbaca: [$headerList]");
        }
    }

    // --- MODUL 1: IMPORT APPLICANT ---
    private function processApplicants($path, $type)
    {
        $rows = SimpleExcelReader::create($path, $type)->getRows();
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $appNum = $row['Applicant Number'] ?? null;
                if (!$appNum) continue;

                $fullName = trim(
                    ($row['First Name'] ?? '') . ' ' . 
                    ($row['Middle Name'] ?? '') . ' ' . 
                    ($row['Last Name'] ?? '')
                );

                $gender = 'Laki-laki';
                if (isset($row['Gender GB'])) {
                    $g = strtoupper($row['Gender GB']);
                    if (str_contains($g, 'F') || str_contains($g, 'FEMALE')) $gender = 'Perempuan';
                }

                $applyDate = now();
                if (!empty($row['Apply Date'])) {
                    try {
                        $applyDate = Carbon::parse($row['Apply Date']);
                    } catch (\Exception $e) {}
                }

                $noKtp = $row['ISSN'] ?? $row['NIK'] ?? $row['No KTP'] ?? null;

                Applicant::updateOrCreate(
                    ['applicant_number' => $appNum],
                    [
                        'nama_lengkap'    => $fullName,
                        'no_ktp'          => $noKtp,
                        'tanggal_lamaran' => $applyDate,
                        'alamat'       => ($row['Address 1'] ?? '') . ' ' . ($row['Address 2'] ?? ''),
                        'kota'         => $row['City'] ?? '-',
                        'provinsi'     => $row['State'] ?? '-',
                        'no_hp_1'      => $row['Home Phone'] ?? '-',
                        'no_hp_2'      => $row['Work Phone'] ?? null,
                        'color_code'   => isset($row['Color Code']) ? strtolower($row['Color Code']) : null,
                        'gender'       => $gender,
                        'status'       => 'pending',
                        'tempat_lahir' => 'Jakarta',
                        'tanggal_lahir'=> now()->subYears(20),
                        'umur'         => 0,
                        'nama_sekolah' => '-',
                        'jurusan'      => '-',
                        'tahun_lulus'  => date('Y'),
                    ]
                );
                $count++;
            }
            DB::commit();
            return back()->with('success', "Berhasil import $count Data Pelamar!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error Import Applicant: ' . $e->getMessage());
        }
    }

    // --- MODUL 2: IMPORT PENDIDIKAN (DIPERBAIKI) ---
    private function processEducation($path, $type)
    {
        $rows = SimpleExcelReader::create($path, $type)->getRows();
        $candidates = [];

        foreach ($rows as $row) {
            $id = $row['Applicant Number'] ?? null;
            
            // --- FIX: Cegah Error "201" ---
            // Bersihkan input tahun (hanya angka)
            $yearRaw = $row['Graduation Year'] ?? 0;
            $year = (int) preg_replace('/\D/', '', $yearRaw); 

            if (!$id) continue;

            // VALIDASI: Tahun harus masuk akal (1900 - 2100)
            // Jika tahunnya "201" atau "0", baris ini otomatis DILEWATI (Skip)
            if ($year < 1900 || $year > 2100) {
                continue; 
            }

            if (!isset($candidates[$id]) || $year > $candidates[$id]['year']) {
                $candidates[$id] = [
                    'year' => $year,
                    'univ' => $row['University'] ?? '-',
                    'major'=> $row['Major'] ?? '-'
                ];
            }
        }

        $applicantIdsToCheck = array_keys($candidates);
        $applicantMap = Applicant::whereIn('applicant_number', $applicantIdsToCheck)
            ->pluck('id', 'applicant_number')
            ->toArray();

        $count = 0;
        foreach ($candidates as $appNum => $data) {
            $applicantId = $applicantMap[$appNum] ?? null;
            if ($applicantId) {
                Applicant::where('id', $applicantId)->update([
                    'nama_sekolah' => $data['univ'],
                    'jurusan'      => $data['major'],
                    'tahun_lulus'  => $data['year']
                ]);
                $count++;
            }
        }
        return back()->with('success', "Berhasil update pendidikan untuk $count Pelamar!");
    }

// --- MODUL 3: IMPORT PSIKOTEST (FINAL VERSION) ---
    private function processPsikotest($path, $type)
    {
       $rows = \Spatie\SimpleExcel\SimpleExcelReader::create($path, $type)->getRows();
        
        // 1. SIAPKAN DATA PELAMAR (Mapping ID)
        $applicantMap = Applicant::whereNotNull('applicant_number')
                        ->pluck('id', 'applicant_number')
                        ->toArray();

        $count = 0;
        $failedCount = 0;

        foreach ($rows as $row) {
            // --- STEP A: CARI PELAMAR (LOGIKA JODOH) ---
            $rawNum = $row['HRI_Applicant_Number_STR'] ?? '';
            
            // Bersihkan angka (misal "1003.0000" jadi "1003")
            $cleanNum = (string) intval(floatval($rawNum));

            // Coba cari dengan berbagai variasi
            $applicantId = $applicantMap[$cleanNum] ?? null; // Cari "1003"
            
            if (!$applicantId) {
                $applicantId = $applicantMap[$rawNum] ?? null; // Cari mentah "1003.0000"
            }

            if (!$applicantId) {
                 // Cari dengan prefix "APP01003" (Sesuaikan padding 0 dengan seeder kamu)
                 $appWithPrefix = 'APP' . str_pad($cleanNum, 5, '0', STR_PAD_LEFT); 
                 $applicantId = $applicantMap[$appWithPrefix] ?? null;
            }

            if (!$applicantId) {
                if ($failedCount < 5) Log::warning("GAGAL MATCH: Excel ID=$rawNum");
                $failedCount++;
                continue; 
            }

            // --- STEP B: SIAPKAN DATA DASAR ---
            $isType38 = isset($row['HR_AspekPsikologis38_RG_38']);
            $prefix = $isType38 ? 'HR_AspekPsikologis38_RG_' : 'HR_AspekPsikologis34_RG_';

            $data = [
                'applicant_id' => $applicantId,
                'report_type'  => $isType38 ? '38' : '34',
                'tanggal_test' => isset($row['tanggal']) ? \Carbon\Carbon::parse($row['tanggal']) : now(),
                'kesimpulan_saran' => $row['Kesimpulan'] ?? null,
            ];

            // Ambil nilai aspek dari Excel, masukkan ke $data sesuai field PsikotestReport
            foreach (\App\Models\PsikotestReport::getAspekAFields() as $field => $label) {
                $idx = array_search($field, array_keys(\App\Models\PsikotestReport::getAspekAFields()));
                $data[$field] = floatval($row[$prefix . ($idx + 1)] ?? 0);
            }
            $fieldsB = $isType38 ? \App\Models\PsikotestReport::getAspekBFields38() : \App\Models\PsikotestReport::getAspekBFields();
            $startB = 7 + 1;
            foreach (array_keys($fieldsB) as $i => $field) {
                $data[$field] = floatval($row[$prefix . ($startB + $i)] ?? 0);
            }
            $fieldsC = $isType38 ? \App\Models\PsikotestReport::getAspekCFields38() : \App\Models\PsikotestReport::getAspekCFields();
            $startC = $startB + count($fieldsB);
            foreach (array_keys($fieldsC) as $i => $field) {
                $data[$field] = floatval($row[$prefix . ($startC + $i)] ?? 0);
            }

            // IQ & kategori
            $iqScore = floatval($row['IQ'] ?? 0);
            $data['iq_score'] = $iqScore;
            $data['iq_category'] = match (true) {
                $iqScore >= 140 => 'very_superior',
                $iqScore >= 120 => 'superior',
                $iqScore >= 110 => 'diatas_rata_rata',
                $iqScore >= 90  => 'rata_rata',
                $iqScore >= 80  => 'dibawah_rata_rata',
                default         => 'borderline',
            };

            // --- STEP C: HITUNG SKOR DENGAN SATU SUMBER (MODEL/CONTROLLER) ---
            $data = \App\Models\PsikotestReport::calculateScores($data, $data['report_type']);

            // Simpan ke Database
            PsikotestReport::updateOrCreate(
                ['applicant_id' => $applicantId],
                $data
            );
            $count++;
        }
        
        $msg = "Selesai! Berhasil import $count data. Gagal mencocokkan $failedCount data.";
        return back()->with('success', $msg);
    }
}