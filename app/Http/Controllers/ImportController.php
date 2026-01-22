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

                // Color code mapping dari kolom Color String/Color Code
                $colorRaw = $row['Color Code'] ?? $row['Color String'] ?? null;
                $colorCode = null;
                if ($colorRaw) {
                    $color = strtolower(trim($colorRaw));
                    $colorCode = match($color) {
                        'merah','red' => 'merah',
                        'kuning','yellow' => 'kuning',
                        'biru','blue' => 'biru',
                        'hijau','green' => 'hijau',
                        'hitam','black' => 'hitam',
                        default => null
                    };
                }

                // Status: jangan ubah jika sudah hired
                $existing = Applicant::where('applicant_number', $appNum)->first();
                $status = $existing && $existing->status === 'hired' ? 'hired' : 'pending';

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
                        'color_code'   => $colorCode,
                        'gender'       => $gender,
                        'status'       => $status,
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
        // Mapping: applicant_number (Excel) -> id (DB)
        $applicantMap = Applicant::whereNotNull('applicant_number')
            ->get()
            ->mapWithKeys(function($a) {
                // Map semua kemungkinan format
                $map = [];
                $map[(string) $a->applicant_number] = $a->id;
                $map[(string) intval($a->applicant_number)] = $a->id;
                $map['APP' . str_pad(intval($a->applicant_number), 5, '0', STR_PAD_LEFT)] = $a->id;
                return $map;
            })->toArray();

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

            // --- STEP B: PROSES SKOR & HITUNG OTOMATIS ---
            
            $isType38 = isset($row['HR_AspekPsikologis38_RG_38']);
            $prefix = $isType38 ? 'HR_AspekPsikologis38_RG_' : 'HR_AspekPsikologis34_RG_';
            
            // Variabel Penampung Total (Direset jadi 0 setiap baris)
            $scoreA = 0; 
            $scoreB = 0; 
            $scoreC = 0;
            
            $data = [
                'applicant_id' => $applicantId,
                'report_type'  => $isType38 ? '38' : '34',
                // Ambil tanggal dari Excel atau pakai hari ini
                'tanggal_test' => isset($row['tanggal']) ? \Carbon\Carbon::parse($row['tanggal']) : now(),
                'kesimpulan_saran' => $row['Kesimpulan'] ?? null, 
            ];

            // 1. Hitung Aspek A (Intelektual)
            foreach (array_keys(PsikotestReport::getAspekAFields()) as $i => $field) {
                $val = floatval($row[$prefix . ($i + 1)] ?? 0);
                $data[$field] = ($val > 0) ? $val : null; // Skor 0 jadi null (N/A)
                $scoreA += ($val > 0) ? $val : 0;
            }

            // 2. Hitung Aspek B (Sikap Kerja)
            $fieldsB = $isType38 ? array_keys(PsikotestReport::getAspekBFields38()) : array_keys(PsikotestReport::getAspekBFields());
            $startB = 7 + 1; // Aspek A ada 7 soal
            foreach ($fieldsB as $i => $field) {
                $val = floatval($row[$prefix . ($startB + $i)] ?? 0);
                $data[$field] = ($val > 0) ? $val : null;
                $scoreB += ($val > 0) ? $val : 0;
            }

            // 3. Hitung Aspek C (Kepribadian)
            $fieldsC = $isType38 ? array_keys(PsikotestReport::getAspekCFields38()) : array_keys(PsikotestReport::getAspekCFields());
            $startC = $startB + count($fieldsB);
            foreach ($fieldsC as $i => $field) {
                $val = floatval($row[$prefix . ($startC + $i)] ?? 0);
                $data[$field] = ($val > 0) ? $val : null;
                $scoreC += ($val > 0) ? $val : 0;
            }

            // --- STEP C: SIMPAN HASIL HITUNGAN (AUTO-CALCULATION) ---
            // Kita TIDAK mengambil dari Excel, tapi memasukkan hasil penjumlahan di atas
            $data['xa_score'] = ($scoreA > 0) ? $scoreA : null;
            $data['xb_score'] = ($scoreB > 0) ? $scoreB : null;
            $data['xc_score'] = ($scoreC > 0) ? $scoreC : null;
            $data['xt_score'] = ($scoreA + $scoreB + $scoreC > 0) ? ($scoreA + $scoreB + $scoreC) : null;

            // --- STEP D: IQ & AUTO CATEGORY ---
            // Ambil angka dari kolom 'IQ' di Excel
            $iqScore = floatval($row['IQ'] ?? null);
            $data['iq_score'] = ($iqScore > 0) ? $iqScore : null;
            $data['iq_category'] = ($iqScore > 0) ? (match (true) {
                $iqScore >= 140 => 'very_superior',
                $iqScore >= 120 => 'superior',
                $iqScore >= 110 => 'diatas_rata_rata',
                $iqScore >= 90  => 'rata_rata',
                $iqScore >= 80  => 'dibawah_rata_rata',
                default         => 'borderline',
            }) : null;
            // Update status applicant hanya jika status bukan 'hired'
            $applicant = Applicant::find($applicantId);
            if ($applicant && $applicant->status !== 'hired') {
                $applicant->status = 'tested';
                $applicant->save();
            }

            // Simpan ke Database
            PsikotestReport::updateOrCreate(
                ['applicant_id' => $applicantId], // Cari berdasarkan ID Pelamar
                $data // Update data baru
            );
            $count++;
        }
        
        $msg = "Selesai! Berhasil import $count data. Gagal mencocokkan $failedCount data.";
        return back()->with('success', $msg);
    }
}