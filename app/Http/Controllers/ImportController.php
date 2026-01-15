<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportController extends Controller
{
    /**
     * Show the import form
     */
    public function showForm()
    {
        return view('import.index');
    }

    /**
     * Handle the Excel import
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // max 10MB
        ], [
            'excel_file.required' => 'File Excel wajib diupload.',
            'excel_file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'excel_file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $fullPath = null;
        
        try {
            $file = $request->file('excel_file');
            
            // Store file temporarily with correct extension
            $extension = $file->getClientOriginalExtension();
            $fileName = 'import_' . time() . '.' . $extension;
            $path = $file->storeAs('imports', $fileName, 'local');
            $fullPath = storage_path('app/private/' . $path);

            // Read rows with headers from file
            $reader = SimpleExcelReader::create($fullPath);
            $rows = $reader->getRows()->toArray();
            
            // Close the reader to release the file
            unset($reader);
            gc_collect_cycles();

            $imported = 0;
            $skipped = 0;
            $errors = [];

            if (empty($rows)) {
                // Clean up temp file
                $this->deleteFile($fullPath);
                
                return redirect()->route('import.form')
                    ->with('error', 'File Excel kosong atau tidak dapat dibaca.');
            }

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                try {
                    // Get values flexibly - try different possible column names
                    $firstName = $row['First Name'] ?? $row['first_name'] ?? $row['FirstName'] ?? '';
                    $lastName = $row['Last Name'] ?? $row['last_name'] ?? $row['LastName'] ?? '';
                    
                    // Skip empty rows
                    if (empty(trim($firstName)) && empty(trim($lastName))) {
                        $skipped++;
                        continue;
                    }

                    // Combine first name and last name
                    $namaLengkap = trim($firstName . ' ' . $lastName);
                    
                    if (empty($namaLengkap)) {
                        $skipped++;
                        continue;
                    }

                    // Get other fields flexibly
                    $address = $row['Address 1'] ?? $row['Address'] ?? $row['address'] ?? '-';
                    $city = $row['City'] ?? $row['city'] ?? '-';
                    $state = $row['State'] ?? $row['state'] ?? $row['Province'] ?? '-';
                    $phone1 = $row['Home Phone 1'] ?? $row['Phone 1'] ?? $row['phone1'] ?? $row['Phone'] ?? '-';
                    $phone2 = $row['Home Phone 2'] ?? $row['Phone 2'] ?? $row['phone2'] ?? null;
                    $applyDate = $row['Apply Date'] ?? $row['ApplyDate'] ?? $row['apply_date'] ?? '';
                    $changeDate = $row['Change Date'] ?? $row['ChangeDate'] ?? $row['change_date'] ?? '';
                    $age = $row['EEO Age'] ?? $row['Age'] ?? $row['age'] ?? $row['Umur'] ?? '';
                    $gender = $row['Gender GB'] ?? $row['Gender'] ?? $row['gender'] ?? '';

                    // Parse gender
                    $parsedGender = $this->parseGender($gender);

                    // Parse age from EEO Age (range umur seperti "20-25" atau angka langsung)
                    $umur = $this->parseAge($age);

                    // Parse dates
                    $tanggalLamaran = $this->parseDate($applyDate);
                    $parsedChangeDate = $this->parseDate($changeDate);

                    // Create applicant with required fields
                    // Fields yang tidak ada di Excel akan diisi default
                    Applicant::create([
                        'nama_lengkap' => $namaLengkap,
                        'alamat' => !empty($address) ? $address : '-',
                        'kota' => !empty($city) ? $city : '-',
                        'provinsi' => !empty($state) ? $state : '-',
                        'no_hp_1' => $this->cleanPhoneNumber($phone1) ?: '-',
                        'no_hp_2' => $this->cleanPhoneNumber($phone2),
                        'no_ktp' => '-', // Default karena tidak ada di Excel
                        'tempat_lahir' => '-', // Default karena tidak ada di Excel
                        'tanggal_lahir' => now()->subYears($umur ?? 20)->format('Y-m-d'), // Estimasi dari umur
                        'gender' => $parsedGender,
                        'umur' => $umur ?? 0,
                        'tanggal_lamaran' => $tanggalLamaran ?? now()->format('Y-m-d'),
                        'nama_sekolah' => '-', // Default karena tidak ada di Excel
                        'jurusan' => '-', // Default karena tidak ada di Excel
                        'tahun_lulus' => '-', // Default karena tidak ada di Excel
                        'catatan' => 'Imported from Excel. Change Date: ' . ($parsedChangeDate ?? '-'),
                        'status' => 'pending',
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $rowNumber = $index + 2; // +2 karena header dan index dimulai dari 0
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                    Log::warning("Import error at row {$rowNumber}: " . $e->getMessage());
                }
            }

            DB::commit();
            
            // Clean up temp file after successful import
            $this->deleteFile($fullPath);

            $message = "Berhasil import {$imported} data pelamar.";
            if ($skipped > 0) {
                $message .= " {$skipped} baris dilewati (kosong).";
            }

            if (!empty($errors)) {
                return redirect()->route('import.form')
                    ->with('warning', $message)
                    ->with('import_errors', array_slice($errors, 0, 10)); // Tampilkan max 10 error
            }

            return redirect()->route('applicants.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel import failed: ' . $e->getMessage());
            
            // Clean up temp file if exists
            $this->deleteFile($fullPath);
            
            return redirect()->route('import.form')
                ->with('error', 'Gagal import file: ' . $e->getMessage());
        }
    }

    /**
     * Parse gender from various formats
     */
    private function parseGender(?string $value): string
    {
        if (empty($value)) {
            return 'Laki-laki'; // Default
        }

        $value = strtolower(trim($value));

        // Check for female indicators
        if (in_array($value, ['f', 'female', 'perempuan', 'wanita', 'p', 'w'])) {
            return 'Perempuan';
        }

        // Default to male
        return 'Laki-laki';
    }

    /**
     * Parse age from EEO Age field (could be range like "20-25" or direct number)
     */
    private function parseAge(?string $value): ?int
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        // If it's a range like "20-25", take the average
        if (preg_match('/(\d+)\s*[-–]\s*(\d+)/', $value, $matches)) {
            return (int) round(((int) $matches[1] + (int) $matches[2]) / 2);
        }

        // If it's a direct number
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Try to extract any number
        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            // If it's already a DateTime object
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }
            
            // Convert to string if not already
            $value = (string) $value;
            
            if (empty(trim($value))) {
                return null;
            }

            // Try common formats
            $formats = [
                'Y-m-d',
                'd/m/Y',
                'm/d/Y',
                'd-m-Y',
                'm-d-Y',
                'Y/m/d',
                'd M Y',
                'M d, Y',
                'F d, Y',
            ];

            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, trim($value));
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            }

            // Try strtotime as fallback
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }

        } catch (\Exception $e) {
            // Return null if parsing fails
        }

        return null;
    }

    /**
     * Clean phone number
     */
    private function cleanPhoneNumber(?string $value): ?string
    {
        if (empty($value) || $value === '-') {
            return $value;
        }

        // Remove non-numeric characters except + at the beginning
        $cleaned = preg_replace('/[^0-9+]/', '', $value);
        
        return $cleaned ?: $value;
    }

    /**
     * Download sample template
     */
    public function downloadTemplate()
    {
        $headers = [
            'First Name',
            'Last Name',
            'Address 1',
            'City',
            'State',
            'Home Phone 1',
            'Home Phone 2',
            'Apply Date',
            'Change Date',
            'EEO Age',
            'Gender GB'
        ];

        $sampleData = [
            ['John', 'Doe', 'Jl. Contoh No. 123', 'Surabaya', 'Jawa Timur', '081234567890', '081234567891', '2026-01-01', '2026-01-10', '25', 'M'],
            ['Jane', 'Smith', 'Jl. Sample No. 456', 'Malang', 'Jawa Timur', '082345678901', '', '2026-01-05', '2026-01-12', '22-25', 'F'],
        ];

        $filename = 'template_import_pelamar.csv';
        
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Safely delete a file
     */
    private function deleteFile(?string $path): void
    {
        if ($path && file_exists($path)) {
            try {
                // Try to delete, but don't fail if it doesn't work
                @unlink($path);
            } catch (\Exception $e) {
                // Log but don't throw - file will be cleaned up later
                Log::warning("Could not delete temp file: {$path}");
            }
        }
    }
}
