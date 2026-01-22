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
        // FIX: Disable snake_case to preserve "Applicant Number", "Status", "Color String" keys
        $rows = SimpleExcelReader::create($path, $type)
            ->headersToSnakeCase(false)
            ->getRows();

        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Ensure accessing with correct Case Sensitive keys
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

                // FIX Issue 4: Mapping status with correct casing
                $statusRaw = strtolower(trim($row['Status'] ?? ''));
                $statusMap = [
                    'hired' => 'accepted',
                    'accepted' => 'accepted',
                    'reject' => 'rejected',
                    'rejected' => 'rejected',
                    'tested' => 'tested',
                ];
                $status = $statusMap[$statusRaw] ?? 'pending';

                // FIX Issue 5: Mapping color code from "Color String" or "Color Code"
                $colorRaw = strtolower(trim($row['Color String'] ?? $row['Color Code'] ?? ''));
                $colorMap = [
                    'red' => 'merah',
                    'yellow' => 'kuning',
                    'blue' => 'biru',
                    'green' => 'hijau',
                    'black' => 'hitam',
                    'gray' => 'abu-abu',
                    'grey' => 'abu-abu',
                    // Indonesian names
                    'merah' => 'merah',
                    'kuning' => 'kuning',
                    'biru' => 'biru',
                    'hijau' => 'hijau',
                    'hitam' => 'hitam',
                    'abu-abu' => 'abu-abu',
                ];
                $colorCode = $colorMap[$colorRaw] ?? ($colorRaw ?: null);

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
                        // FIX Issue 6: TTL default to '-' if not in Excel (Removed Jakarta default)
                        'tempat_lahir' => '-',
                        'tanggal_lahir'=> null,
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
        // Fix education header case too
        $rows = SimpleExcelReader::create($path, $type)
            ->headersToSnakeCase(false)
            ->getRows();
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

    // --- MODUL 3: IMPORT PSIKOTEST (MULTI-SHEET SUPPORT) ---
    private function processPsikotest($path, $type)
    {
        // FIX Issue 8: Use OpenSpout directly to support Multiple Sheets
        $reader = null;
        if (str_ends_with(strtolower($path), '.xlsx')) {
            $reader = new \OpenSpout\Reader\XLSX\Reader();
        } elseif (str_ends_with(strtolower($path), '.csv')) {
            $reader = new \OpenSpout\Reader\CSV\Reader();
        } else {
            return back()->with('error', 'Format file harus XLSX atau CSV');
        }

        $reader->open($path);

        // 1. SIAPKAN DATA PELAMAR (Mapping ID)
        $applicantMap = Applicant::whereNotNull('applicant_number')
                        ->pluck('id', 'applicant_number')
                        ->toArray();

        $count = 0;
        $failedCount = 0;
        $sheetCount = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            $sheetCount++;
            $headers = [];
            $isFirstRow = true;

            foreach ($sheet->getRowIterator() as $rowObj) {
                // Konversi row object ke array
                $cells = $rowObj->getCells();
                $rowData = [];
                foreach ($cells as $cell) {
                    $rowData[] = $cell->getValue();
                }

                // Ambil Header
                if ($isFirstRow) {
                    $headers = $rowData;
                    $isFirstRow = false;
                    continue;
                }

                // Map Row Data dengan Header
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = $rowData[$index] ?? null;
                }

                // SKIP jika kosong
                if (empty($row['HRI_Applicant_Number_STR']) && empty($row['IQ'])) continue;

                // --- PROSES DATA PER ROW ---
                
                // --- STEP A: CARI PELAMAR ---
                $rawNum = $row['HRI_Applicant_Number_STR'] ?? '';
                $cleanNum = (string) intval(floatval($rawNum));

                $applicantId = $applicantMap[$cleanNum] ?? null; 
                if (!$applicantId) $applicantId = $applicantMap[$rawNum] ?? null;
                if (!$applicantId) {
                     $appWithPrefix = 'APP' . str_pad($cleanNum, 5, '0', STR_PAD_LEFT); 
                     $applicantId = $applicantMap[$appWithPrefix] ?? null;
                }

                if (!$applicantId) {
                    if ($failedCount < 5) Log::warning("GAGAL MATCH: Excel ID=$rawNum");
                    $failedCount++;
                    continue; 
                }

                // --- STEP B: SIAPKAN DATA DASAR ---
                $isType38 = isset($row['HR_AspekPsikologis38_RG_38']) || isset($row['HR_AspekPsikologis38_RG_1']);
                $prefix = $isType38 ? 'HR_AspekPsikologis38_RG_' : 'HR_AspekPsikologis34_RG_';

                $tanggalTest = now();
                if (!empty($row['tanggal'])) {
                    try {
                        if ($row['tanggal'] instanceof \DateTime) {
                             $tanggalTest = \Carbon\Carbon::instance($row['tanggal']);
                        } else {
                             $tanggalTest = \Carbon\Carbon::parse($row['tanggal']);
                        }
                    } catch (\Exception $e) {}
                }

                $data = [
                    'applicant_id' => $applicantId,
                    'report_type'  => $isType38 ? '38' : '34',
                    'tanggal_test' => $tanggalTest,
                    'kesimpulan_saran' => $row['Kesimpulan'] ?? null,
                ];

                $getValue = function($key) use ($row) {
                    $val = $row[$key] ?? null;
                    if ($val === null || $val === '' || $val === 0 || $val === '0') return null;
                    return floatval($val);
                };

                // Aspek A
                foreach (\App\Models\PsikotestReport::getAspekAFields() as $field => $label) {
                    $idx = array_search($field, array_keys(\App\Models\PsikotestReport::getAspekAFields()));
                    $data[$field] = $getValue($prefix . ($idx + 1));
                }
                
                // Aspek B
                $fieldsB = $isType38 ? \App\Models\PsikotestReport::getAspekBFields38() : \App\Models\PsikotestReport::getAspekBFields();
                $startB = 7 + 1; 
                foreach (array_keys($fieldsB) as $i => $field) {
                    $data[$field] = $getValue($prefix . ($startB + $i));
                }

                // Aspek C
                $fieldsC = $isType38 ? \App\Models\PsikotestReport::getAspekCFields38() : \App\Models\PsikotestReport::getAspekCFields();
                $startC = $startB + count($fieldsB);
                foreach (array_keys($fieldsC) as $i => $field) {
                    $data[$field] = $getValue($prefix . ($startC + $i));
                }

                // IQ Check
                $iqColumns = ['HR_Borderline', 'HR_Dibawah_Rata', 'HR_RataRata', 'HR_Diatas_Rata', 'HR_Superior', 'HR_VerySuperior', 'IQ'];
                $iqScore = 0;
                foreach ($iqColumns as $col) {
                    $val = floatval($row[$col] ?? 0);
                    if ($val > 0) { $iqScore = $val; break; }
                }

                $data['iq_score'] = $iqScore;
                $data['iq_category'] = match (true) {
                    $iqScore >= 140 => 'very_superior',
                    $iqScore >= 120 => 'superior',
                    $iqScore >= 110 => 'diatas_rata_rata',
                    $iqScore >= 90  => 'rata_rata',
                    $iqScore >= 80  => 'dibawah_rata_rata',
                    default         => 'borderline',
                };

                // SAVE
                $data = \App\Models\PsikotestReport::calculateScores($data, $data['report_type']);
                PsikotestReport::updateOrCreate(['applicant_id' => $applicantId], $data);
                Applicant::where('id', $applicantId)->update(['tanggal_test' => $tanggalTest]);
                $count++;
            }
        }
        
        $reader->close();
        
        $msg = "Selesai! Berhasil import $count data dari $sheetCount sheets. Gagal mencocokkan $failedCount data.";
        return back()->with('success', $msg);
    }
}