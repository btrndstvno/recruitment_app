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
        // 1. SETTING PERFORMANCE
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $file = $request->file('excel_file');
        $path = $file->getPathname();
        
        // Ambil ekstensi asli file (xlsx/csv)
        $type = $file->getClientOriginalExtension();

        // Cek apakah file XLSX dan ekstensi zip tersedia
        if (strtolower($type) === 'xlsx' && !class_exists('ZipArchive')) {
            return back()->with('error', 'Server tidak mendukung file XLSX (ekstensi PHP zip tidak aktif). Silakan convert file ke format CSV terlebih dahulu, atau aktifkan ekstensi zip di php.ini server.');
        }

        // 2. BACA HEADER & FORCE SNAKE CASE
        $reader = SimpleExcelReader::create($path, $type)->headersToSnakeCase(true);
        $firstRow = $reader->getRows()->first();
        
        if (!$firstRow) {
            return back()->with('error', 'File Excel kosong atau header tidak terbaca.');
        }

        $headers = array_keys($firstRow);
        
        // DEBUG LOG
        Log::info('Header Terdeteksi (V9):', $headers);

        // 3. LOGIKA PEMILAH CERDAS
        
        // Cek Indikator Psikotest (Cari 'hri_', 'iq', 'kesimpulan')
        $isPsikotest = false;
        foreach ($headers as $h) {
            if (str_contains($h, 'hri_') || str_contains($h, 'hr_aspek') || $h === 'kesimpulan' || $h === 'iq') {
                $isPsikotest = true;
                break;
            }
        }

        if ($isPsikotest) {
            return $this->processPsikotest($path, $type);
        } elseif (in_array('university', $headers) && in_array('graduation_year', $headers)) {
            return $this->processEducation($path, $type);
        } elseif (in_array('applicant_number', $headers) && in_array('first_name', $headers)) {
            return $this->processApplicants($path, $type);
        } else {
            $headerList = implode(', ', $headers);
            return back()->with('error', "Format file tidak dikenali. Header terbaca: [$headerList].");
        }
    }

   // --- MODUL 1: IMPORT APPLICANT ---
    private function processApplicants($path, $type)
    {
        $rows = SimpleExcelReader::create($path, $type)->headersToSnakeCase(true)->getRows();
        $count = 0;

        // Helper untuk membersihkan No HP
        $cleanPhone = function($number) {
            if (empty($number)) return null;
            $cleaned = preg_replace('/[^0-9]/', '', (string)$number);
            return $cleaned ?: null;
        };

        // Helper untuk ambil kolom dengan beberapa kemungkinan nama
        $findColumn = function($row, $candidates) {
            foreach ($candidates as $col) {
                if (isset($row[$col]) && $row[$col] !== '') return $row[$col];
            }
            return null;
        };

        Log::info('--- DEBUG IMPORT APPLICANT ---');
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                Log::info('IMPORT ROW', [
                    'applicant_number' => $row['applicant_number'] ?? null,
                    'Nomor KTP' => $row['Nomor KTP'] ?? null,
                    'no_ktp' => $row['no_ktp'] ?? null,
                    'ktp' => $row['ktp'] ?? null,
                    'Tanggal Lahir' => $row['Tanggal Lahir'] ?? null,
                    'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                ]);
                $appNum = $row['applicant_number'] ?? null;
                if (!$appNum) continue;

                // Validasi: applicant_number harus berupa angka
                // Jika nilai float dari Excel (misal 6034.00), konversi ke int dulu sebelum preg_replace
                // agar tidak menjadi 603400
                if (is_numeric($appNum)) {
                    $appNum = (int) $appNum;
                } else {
                    // Bersihkan dari karakter non-numerik (Rp, koma, titik, spasi)
                    $appNum = preg_replace('/[^0-9]/', '', (string)$appNum);
                }
                
                if (empty($appNum) || !is_numeric($appNum)) {
                    Log::warning('SKIP ROW: applicant_number bukan angka', ['raw' => $row['applicant_number'] ?? null]);
                    continue;
                }
                $appNum = (int) $appNum;

                $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                
                // Skip jika nama kosong (kemungkinan baris kosong/header)
                if (empty($fullName) || $fullName === '  ') {
                    continue;
                }

                // Sanitasi No HP
                $noHp1 = $cleanPhone($row['home_phone'] ?? null) ?? '-';
                $noHp2 = $cleanPhone($row['work_phone'] ?? null);

                $gender = 'Laki-laki';
                if (isset($row['gender_gb'])) {
                    $g = strtoupper($row['gender_gb']);
                    if (str_contains($g, 'F') || str_contains($g, 'FEMALE')) $gender = 'Perempuan';
                }

                $applyDate = now();
                if (!empty($row['apply_date'])) {
                    try { 
                        $parsedApply = \Carbon\Carbon::parse($row['apply_date']);
                        // Abaikan tanggal tidak valid (1899/1900 = Excel null date)
                        if ($parsedApply->year >= 1950) {
                            $applyDate = $parsedApply;
                        }
                    } catch (\Exception $e) {}
                }


                // Ambil dari kolom Excel yang benar (toleran header)
                // Cari kolom KTP lebih toleran (case-insensitive, trim)
                $noKtp = null;
                foreach ($row as $k => $v) {
                    if (is_string($k) && preg_match('/ktp|nik/i', $k)) {
                        $noKtp = trim($v);
                        break;
                    }
                }
                if (is_string($noKtp)) {
                    $noKtp = ltrim($noKtp, "' ."); // hapus petik satu, titik, spasi di depan
                }
                if (!$noKtp) $noKtp = '-';

                // Tanggal lahir dari kolom yang benar (toleran header)
                $birthRaw = $findColumn($row, ['Tanggal Lahir', 'tanggal_lahir', 'tgl_lahir', 'dob', 'birthdate']);
                $birthDate = null;
                if (!empty($birthRaw)) {
                    $tgl = $birthRaw;
                    if (is_numeric($tgl)) {
                        try {
                            $birthDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tgl);
                        } catch (\Exception $e) {
                            Log::warning('Gagal konversi serial Excel ke tanggal', ['value' => $tgl]);
                        }
                    } else {
                        // Jika sudah DateTime/Carbon, langsung pakai
                        if ($tgl instanceof \DateTimeInterface) {
                            $birthDate = \Carbon\Carbon::instance($tgl);
                        } else {
                            // Coba beberapa format umum
                            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y', 'd M Y', 'd F Y'];
                            foreach ($formats as $fmt) {
                                try {
                                    $birthDate = \Carbon\Carbon::createFromFormat($fmt, (string)$tgl);
                                    break;
                                } catch (\Exception $e) {}
                            }
                            if (!$birthDate) {
                                try {
                                    $birthDate = \Carbon\Carbon::parse((string)$tgl);
                                } catch (\Exception $e2) {
                                    Log::warning('Gagal parsing tanggal lahir', ['value' => $tgl]);
                                }
                            }
                        }
                    }
                    
                    // Validasi: abaikan tanggal tidak valid (1899/1900 = Excel null date)
                    if ($birthDate && $birthDate->year < 1950) {
                        $birthDate = null;
                    }
                }

                // PKL: jika kolom pkl/PKL/Is PKL/PKL Status/PKL_YN isinya 'yes', set is_pkl=true
                $pklRaw = $findColumn($row, ['pkl', 'PKL', 'is_pkl', 'Is PKL', 'PKL Status', 'PKL_YN']);
                $isPkl = false;
                if ($pklRaw !== null && strtolower(trim($pklRaw)) === 'yes') {
                    $isPkl = true;
                }

                // Data PKL: periode, asal sekolah, jurusan/tempat boleh kosong
                $pklAwal = $findColumn($row, ['Awal PKL', 'awal_pkl', 'pkl_awal', 'start_pkl', 'mulai_pkl']);
                $pklAkhir = $findColumn($row, ['Berakhir PKL', 'berakhir_pkl', 'pkl_akhir', 'end_pkl', 'selesai_pkl']);
                $pklAsalSekolah = $findColumn($row, ['Asal Sekolah', 'asal_sekolah', 'pkl_asal_sekolah', 'school_pkl']);
                $pklJurusan = $findColumn($row, ['pkl_jurusan', 'jurusan_pkl', 'jurusan', 'major_pkl']);
                $pklTempat = $findColumn($row, ['pkl_tempat', 'tempat_pkl', 'tempat', 'place_pkl']);


                // Status dari Excel
                $statusRaw = strtolower(trim($row['status'] ?? ''));
                $statusExcel = ($statusRaw == 'hired' || $statusRaw == 'accepted') ? 'accepted' :
                               (($statusRaw == 'reject' || $statusRaw == 'rejected') ? 'rejected' : 'pending');

                $colorRaw = strtolower(trim($row['color_string'] ?? $row['color_code'] ?? ''));
                $colorMap = ['red'=>'merah', 'yellow'=>'kuning', 'blue'=>'biru', 'green'=>'hijau', 'black'=>'hitam'];
                $colorCode = $colorMap[$colorRaw] ?? null;

                $applicant = Applicant::firstOrNew(['applicant_number' => $appNum]);

                // 1. Update Data Pribadi (Selalu di-update)
                $applicant->nama_lengkap = $fullName;
                $applicant->no_ktp = $noKtp;
                $applicant->tanggal_lamaran = $applyDate;
                $applicant->alamat = ($row['address_1'] ?? '') . ' ' . ($row['address_2'] ?? '');
                $applicant->kota = $row['city'] ?? '-';
                $applicant->provinsi = $row['state'] ?? '-';
                $applicant->no_hp_1 = $noHp1;
                $applicant->no_hp_2 = $noHp2;

                $applicant->color_code = $colorCode;
                $applicant->gender = $gender;
                $applicant->tempat_lahir = $row['birth_place'] ?? null;
                $applicant->tanggal_lahir = $birthDate;
                $applicant->umur = null;

                // PKL
                $applicant->is_pkl = $isPkl;
                
                // Parsing tanggal PKL dengan penanganan error
                $parseDateSafe = function($dateVal) {
                    if (empty($dateVal)) return null;
                    try {
                        $parsed = \Carbon\Carbon::parse($dateVal);
                        // Abaikan tanggal yang tidak valid (1899/1900 = Excel null date)
                        if ($parsed->year < 1950) return null;
                        return $parsed;
                    } catch (\Exception $e) {
                        return null;
                    }
                };
                
                $applicant->pkl_awal = $parseDateSafe($pklAwal);
                $applicant->pkl_akhir = $parseDateSafe($pklAkhir);
                $applicant->pkl_asal_sekolah = $pklAsalSekolah;
                $applicant->pkl_jurusan = $pklJurusan;
                $applicant->pkl_tempat = $pklTempat;

                // 2. Cek apakah ini Data Baru atau Update
                if (!$applicant->exists) {
                    // --- JIKA PELAMAR BARU ---
                    // Set status sesuai excel (default pending)
                    $applicant->status = $statusExcel;

                    // Set default pendidikan (wajib diisi agar database tidak error)
                    $applicant->nama_sekolah = '-';
                    $applicant->jurusan = '-';
                    $applicant->tahun_lulus = date('Y');
                } else {
                    // --- JIKA PELAMAR LAMA (UPDATE) ---
                    // JANGAN update nama_sekolah/jurusan/tahun_lulus (biarkan data lama)

                    // Logika :
                    // Hanya update status jika Excel secara eksplisit bilang 'accepted'/'rejected'
                    // ATAU jika status saat ini masih 'pending'.
                    // Jika status saat ini sudah 'tested', JANGAN ditimpa jadi 'pending'.
                    if ($statusExcel !== 'pending' || $applicant->status === 'pending') {
                         $applicant->status = $statusExcel;
                    }
                }

                $applicant->save();
                $count++;
            }
            DB::commit();
            if ($count == 0) return back()->with('error', "Import 0 data.");
            return back()->with('success', "Berhasil import $count Data Pelamar!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error Import Applicant: ' . $e->getMessage());
        }
    }
    
    // --- MODUL 2: IMPORT PENDIDIKAN ---
    private function processEducation($path, $type)
    {
        $rows = SimpleExcelReader::create($path, $type)->headersToSnakeCase(true)->getRows();
        $candidates = [];

        foreach ($rows as $row) {
            $id = $row['applicant_number'] ?? null;
            // Konversi float dari Excel ke integer (misal 6034.00 -> 6034)
            if (is_numeric($id)) {
                $id = (int) $id;
            }
            $year = (int) preg_replace('/\D/', '', $row['graduation_year'] ?? 0); 
            if (!$id || $year < 1900 || $year > 2100) continue; 

            if (!isset($candidates[$id]) || $year > $candidates[$id]['year']) {
                $candidates[$id] = ['year' => $year, 'univ' => $row['university'] ?? '-', 'major'=> $row['major'] ?? '-'];
            }
        }

        $applicantMap = Applicant::whereIn('applicant_number', array_keys($candidates))->pluck('id', 'applicant_number')->toArray();
        $count = 0;
        foreach ($candidates as $appNum => $data) {
            if (isset($applicantMap[$appNum])) {
                Applicant::where('id', $applicantMap[$appNum])->update([
                    'nama_sekolah' => $data['univ'], 'jurusan' => $data['major'], 'tahun_lulus' => $data['year']
                ]);
                $count++;
            }
        }
        return back()->with('success', "Berhasil update pendidikan untuk $count Pelamar!");
    }

    // --- MODUL 3: IMPORT PSIKOTEST (PERBAIKAN DETEKSI FILE) ---
    private function processPsikotest($path, $type)
    {
        // PERBAIKAN V9: Cek $type (ekstensi asli), BUKAN $path (nama file sementara)
        $reader = null;
        $ext = strtolower($type);

        if ($ext === 'xlsx') {
            $reader = new \OpenSpout\Reader\XLSX\Reader();
        } elseif ($ext === 'csv') {
            $reader = new \OpenSpout\Reader\CSV\Reader();
        } else {
            // Jika format lain (misal xls), kembalikan error
            return back()->with('error', "Format file tidak dikenali: $type. Harus XLSX atau CSV.");
        }

        $reader->open($path);

        $applicantMap = Applicant::whereNotNull('applicant_number')
                        ->pluck('id', 'applicant_number')
                        ->toArray();

        $count = 0;
        $failedCount = 0;
        $sheetCount = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            $sheetCount++;
            $headers = [];
            $headerFound = false;

            foreach ($sheet->getRowIterator() as $rowObj) {
                $cells = $rowObj->getCells();
                $rowData = [];
                foreach ($cells as $cell) {
                    $rowData[] = $cell->getValue();
                }

                // 1. CARI HEADER ROW
                if (!$headerFound) {
                    foreach ($rowData as $cellValue) {
                        $v = strtolower(trim((string)$cellValue));
                        if ($v === 'hri_applicant_number_str' || $v === 'applicant number' || $v === 'applicant_number') {
                            $headers = array_map(function($h) {
                                return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', (string)$h)));
                            }, $rowData);
                            $headerFound = true;
                            break;
                        }
                    }
                    continue; 
                }

                // 2. MAPPING DATA
                $row = [];
                foreach ($headers as $index => $header) {
                    if (!empty($header)) $row[$header] = $rowData[$index] ?? null;
                }

                $rawNum = $row['hri_applicant_number_str'] ?? $row['applicant_number'] ?? null;
                if (!$rawNum && empty($row['iq'])) continue;

                // --- STEP A: MATCHING ---
                $cleanNum = (string) intval(floatval($rawNum));
                $applicantId = $applicantMap[$cleanNum] ?? null; 
                if (!$applicantId) $applicantId = $applicantMap[$rawNum] ?? null;
                if (!$applicantId) {
                     $appWithPrefix = 'APP' . str_pad($cleanNum, 5, '0', STR_PAD_LEFT); 
                     $applicantId = $applicantMap[$appWithPrefix] ?? null;
                }

                if (!$applicantId) {
                    if ($failedCount < 5) Log::warning("GAGAL MATCH PSIKOTEST: ID=$rawNum");
                    $failedCount++;
                    continue; 
                }

                // --- STEP B: PROSES DATA ---
                $isType38 = isset($row['hr_aspekpsikologis38_rg_38']) || isset($row['hr_aspekpsikologis38_rg_1']);
                $prefix = $isType38 ? 'hr_aspekpsikologis38_rg_' : 'hr_aspekpsikologis34_rg_';

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


                // Gabungkan semua kolom setelah kolom 'kesimpulan' yang masih berisi data ke dalam field kesimpulan_saran
                $kesimpulanSaran = '';
                $foundKesimpulan = false;
                foreach (array_keys($row) as $colKey) {
                    if ($foundKesimpulan) {
                        $val = trim((string)($row[$colKey] ?? ''));
                        if ($val !== '') {
                            $kesimpulanSaran .= ($kesimpulanSaran !== '' ? "\n" : '') . $val;
                        }
                    }
                    if (!$foundKesimpulan && (strtolower($colKey) === 'kesimpulan' || strtolower($colKey) === 'kesimpulan_saran')) {
                        $val = trim((string)($row[$colKey] ?? ''));
                        if ($val !== '') {
                            $kesimpulanSaran .= $val;
                        }
                        $foundKesimpulan = true;
                    }
                }
                // Pisahkan setiap kalimat yang diakhiri titik menjadi baris baru
                if ($kesimpulanSaran !== '') {
                    $kesimpulanSaran = preg_replace('/\.(?!\n|$)\s*/', ".\n", $kesimpulanSaran);
                }
                $data = [
                    'applicant_id' => $applicantId,
                    'report_type'  => $isType38 ? '38' : '34',
                    'tanggal_test' => $tanggalTest,
                    'kesimpulan_saran' => $kesimpulanSaran !== '' ? $kesimpulanSaran : null,
                ];

                $getValue = function($key) use ($row) {
                    $val = $row[$key] ?? null;
                    if ($val === null || $val === '' || $val === 0 || $val === '0') return null;
                    return floatval($val);
                };

                foreach (\App\Models\PsikotestReport::getAspekAFields() as $field => $label) {
                    $idx = array_search($field, array_keys(\App\Models\PsikotestReport::getAspekAFields()));
                    $data[$field] = $getValue($prefix . ($idx + 1));
                }
                
                $fieldsB = $isType38 ? \App\Models\PsikotestReport::getAspekBFields38() : \App\Models\PsikotestReport::getAspekBFields();
                $startB = 7 + 1; 
                foreach (array_keys($fieldsB) as $i => $field) {
                    $data[$field] = $getValue($prefix . ($startB + $i));
                }

                $fieldsC = $isType38 ? \App\Models\PsikotestReport::getAspekCFields38() : \App\Models\PsikotestReport::getAspekCFields();
                $startC = $startB + count($fieldsB);
                foreach (array_keys($fieldsC) as $i => $field) {
                    $data[$field] = $getValue($prefix . ($startC + $i));
                }

                // IQ Check
                $iqColumns = ['hr_borderline', 'hr_dibawah_rata', 'hr_ratarata', 'hr_diatas_rata', 'hr_superior', 'hr_verysuperior', 'iq'];
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

                $data = \App\Models\PsikotestReport::calculateScores($data, $data['report_type']);
                PsikotestReport::updateOrCreate(['applicant_id' => $applicantId], $data);
                // Update tanggal_test dan status jika perlu
                $applicant = Applicant::find($applicantId);
                $updateData = ['tanggal_test' => $tanggalTest];
                if ($applicant && ($applicant->status !== 'accepted' && $applicant->status !== 'rejected')) {
                    // Jika ada tanggal_test & laporan psikotest, status jadi 'tested'
                    $updateData['status'] = 'tested';
                }
                Applicant::where('id', $applicantId)->update($updateData);
                $count++;
                
            }
        }
        
        $reader->close();
        
        $msg = "Selesai! Berhasil import $count Hasil Psikotest. Gagal Match: $failedCount.";
        return back()->with('success', $msg);
    }
}   