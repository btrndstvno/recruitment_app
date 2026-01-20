<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\PsikotestReport;
use Illuminate\Http\Request;

class PsikotestReportController extends Controller
{
    /**
     * Show the form for creating a new psikotest report.
     */
    public function create(Request $request, Applicant $applicant)
    {
        // Check if report already exists
        if ($applicant->psikotestReport) {
            return redirect()->route('psikotest.edit', $applicant)
                ->with('info', 'Laporan psikotest sudah ada, silakan edit.');
        }

        $reportType = $request->get('type', '34');
        if (!in_array($reportType, ['34', '38'])) {
            $reportType = '34';
        }

        return view('psikotest.create', compact('applicant', 'reportType'));
    }

    /**
     * Store a newly created psikotest report.
     */
    public function store(Request $request, Applicant $applicant)
    {
        $reportType = $request->input('report_type', '34');
        $validated = $this->validateReport($request, $reportType);
        $validated['applicant_id'] = $applicant->id;
        $validated['report_type'] = $reportType;

        // Calculate scores based on report type
        $validated = $this->calculateScores($validated, $reportType);

        PsikotestReport::create($validated);

        // Update applicant status
        $applicant->update(['status' => 'tested']);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Laporan psikotest berhasil disimpan!');
    }

    /**
     * Show the form for editing the psikotest report.
     */
    public function edit(Request $request, Applicant $applicant)
    {
        $report = $applicant->psikotestReport;

        if (!$report) {
            return redirect()->route('psikotest.create', $applicant)
                ->with('info', 'Laporan psikotest belum ada, silakan buat baru.');
        }

        // Ambil tipe dari query jika ada, default ke tipe report yang sudah ada
        $reportType = $request->get('type', $report->report_type ?? '34');
        if (!in_array($reportType, ['34', '38'])) {
            $reportType = '34';
        }

        // Untuk aspek B, siapkan field sesuai tipe
        $aspekBFields = $reportType == '38'
            ? \App\Models\PsikotestReport::getAspekBFields38()
            : \App\Models\PsikotestReport::getAspekBFields();

        // Untuk aspek C, siapkan field sesuai tipe
        $aspekCFields = $reportType == '38'
            ? \App\Models\PsikotestReport::getAspekCFields38()
            : \App\Models\PsikotestReport::getAspekCFields();

        return view('psikotest.edit', compact('applicant', 'report', 'reportType', 'aspekBFields', 'aspekCFields'));
    }

    /**
     * Update the specified psikotest report in storage.
     */
    public function update(Request $request, Applicant $applicant)
    {
        $report = $applicant->psikotestReport;

        if (!$report) {
            return redirect()->route('psikotest.create', $applicant);
        }

        // Ambil tipe dari request, default ke tipe lama
        $reportType = $request->input('report_type', $report->report_type ?? '34');
        if (!in_array($reportType, ['34', '38'])) {
            $reportType = '34';
        }

        $validated = $this->validateReport($request, $reportType);
        $validated['report_type'] = $reportType;

        // Calculate scores based on report type
        $validated = $this->calculateScores($validated, $reportType);

        $report->update($validated);

        return redirect()->route('applicants.show', $applicant)
            ->with('success', 'Laporan psikotest berhasil diperbarui!');
    }

    /**
     * Display the psikotest report.
     */
    public function show(Applicant $applicant)
    {
        $report = $applicant->psikotestReport;

        if (!$report) {
            return redirect()->route('applicants.show', $applicant)
                ->with('error', 'Laporan psikotest belum ada.');
        }

        return view('psikotest.show', compact('applicant', 'report'));
    }

    /**
     * Validate the report request.
     */
    private function validateReport(Request $request, string $reportType = '34')
    {
        $rules = [
            'tanggal_test' => 'required|date',
            'iq_score' => 'nullable|integer|min:50|max:200',
            'iq_category' => 'nullable|in:borderline,dibawah_rata_rata,rata_rata,diatas_rata_rata,superior,very_superior',
            // A. Kemampuan Intelektual Umum
            'kemampuan_memecahkan_masalah' => 'nullable|integer|min:1|max:5',
            'ruang_lingkup_pengetahuan' => 'nullable|integer|min:1|max:5',
            'kemampuan_berfikir_analitis' => 'nullable|integer|min:1|max:5',
            'kemampuan_bekerja_dengan_angka' => 'nullable|integer|min:1|max:5',
            'kemampuan_berfikir_logis' => 'nullable|integer|min:1|max:5',
            'kemampuan_berfikir_abstrak' => 'nullable|integer|min:1|max:5',
            'kemampuan_mengingat' => 'nullable|integer|min:1|max:5',
            // B. Kemampuan Khusus (shared)
            'kecepatan_dalam_bekerja' => 'nullable|integer|min:1|max:5',
            'ketelitian_dalam_bekerja' => 'nullable|integer|min:1|max:5',
            'kestabilan_dalam_bekerja' => 'nullable|integer|min:1|max:5',
            'ketahanan_kerja' => 'nullable|integer|min:1|max:5',
            'kemampuan_konsentrasi_persepsi' => 'nullable|integer|min:1|max:5',
            'kemampuan_mengemukakan_pendapat' => 'nullable|integer|min:1|max:5',
            'kemampuan_penalaran_non_verbal' => 'nullable|integer|min:1|max:5',
            'kemampuan_membaca_memahami_logis' => 'nullable|integer|min:1|max:5',
            'sistematika_dalam_bekerja' => 'nullable|integer|min:1|max:5',
            // C. Kepribadian Dan Sikap Kerja
            'motivasi' => 'nullable|integer|min:1|max:5',
            'kemampuan_membuat_keputusan' => 'nullable|integer|min:1|max:5',
            'kemampuan_kerja_sama_kelompok' => 'nullable|integer|min:1|max:5',
            'kemampuan_menjadi_pemimpin' => 'nullable|integer|min:1|max:5',
            'kemampuan_berfikir_positif' => 'nullable|integer|min:1|max:5',
            'ketekunan_dalam_bekerja' => 'nullable|integer|min:1|max:5',
            'kejujuran_berpendapat' => 'nullable|integer|min:1|max:5',
            'tanggung_jawab_dalam_bekerja' => 'nullable|integer|min:1|max:5',
            'motif_dalam_berprestasi' => 'nullable|integer|min:1|max:5',
            'afiliasi' => 'nullable|integer|min:1|max:5',
            'motif_menolong_orang_lain' => 'nullable|integer|min:1|max:5',
            'kestabilan_emosi' => 'nullable|integer|min:1|max:5',
            'kematangan_sosial' => 'nullable|integer|min:1|max:5',
            'rasa_percaya_diri' => 'nullable|integer|min:1|max:5',
            'penyesuaian_diri' => 'nullable|integer|min:1|max:5',
            'kejujuran_dalam_bekerja' => 'nullable|integer|min:1|max:5',
            'kesimpulan_saran' => 'nullable|string',
        ];

        // Add specific rules based on report type
        if ($reportType === '34') {
            $rules['kemampuan_berhitung'] = 'nullable|integer|min:1|max:5';
            $rules['kemampuan_administrasi_kompleks'] = 'nullable|integer|min:1|max:5';
        } else {
            // 38-question version extra fields
            $rules['persepsi_ruang_bidang'] = 'nullable|integer|min:1|max:5';
            $rules['kemampuan_dasar_mekanik'] = 'nullable|integer|min:1|max:5';
            $rules['kemampuan_mengidentifikasi_komponen'] = 'nullable|integer|min:1|max:5';
            $rules['kemampuan_bekerja_dengan_angka_computational'] = 'nullable|integer|min:1|max:5';
            $rules['kemampuan_penalaran_mekanik'] = 'nullable|integer|min:1|max:5';
            $rules['kemampuan_merakit_objek'] = 'nullable|integer|min:1|max:5';
        }

        return $request->validate($rules);
    }

    /**
     * Calculate average scores for each section.
     */
    private function calculateScores(array $data, string $reportType = '34')
    {
        // Calculate XA Score (Aspek A) - same for both types
        $aspekA = [
            $data['kemampuan_memecahkan_masalah'] ?? null,
            $data['ruang_lingkup_pengetahuan'] ?? null,
            $data['kemampuan_berfikir_analitis'] ?? null,
            $data['kemampuan_bekerja_dengan_angka'] ?? null,
            $data['kemampuan_berfikir_logis'] ?? null,
            $data['kemampuan_berfikir_abstrak'] ?? null,
            $data['kemampuan_mengingat'] ?? null,
        ];
        $data['xa_score'] = $this->calculateAverage($aspekA);

        // Calculate XB Score (Aspek B) - different based on report type
        if ($reportType === '34') {
            $aspekB = [
                $data['kecepatan_dalam_bekerja'] ?? null,
                $data['ketelitian_dalam_bekerja'] ?? null,
                $data['kestabilan_dalam_bekerja'] ?? null,
                $data['ketahanan_kerja'] ?? null,
                $data['kemampuan_konsentrasi_persepsi'] ?? null,
                $data['kemampuan_berhitung'] ?? null,
                $data['kemampuan_mengemukakan_pendapat'] ?? null,
                $data['kemampuan_penalaran_non_verbal'] ?? null,
                $data['kemampuan_membaca_memahami_logis'] ?? null,
                $data['kemampuan_administrasi_kompleks'] ?? null,
                $data['sistematika_dalam_bekerja'] ?? null,
            ];
        } else {
            $aspekB = [
                $data['kecepatan_dalam_bekerja'] ?? null,
                $data['ketelitian_dalam_bekerja'] ?? null,
                $data['kestabilan_dalam_bekerja'] ?? null,
                $data['ketahanan_kerja'] ?? null,
                $data['kemampuan_konsentrasi_persepsi'] ?? null,
                $data['kemampuan_mengemukakan_pendapat'] ?? null,
                $data['kemampuan_penalaran_non_verbal'] ?? null,
                $data['persepsi_ruang_bidang'] ?? null,
                $data['kemampuan_dasar_mekanik'] ?? null,
                $data['kemampuan_mengidentifikasi_komponen'] ?? null,
                $data['kemampuan_bekerja_dengan_angka_computational'] ?? null,
                $data['kemampuan_penalaran_mekanik'] ?? null,
                $data['kemampuan_membaca_memahami_logis'] ?? null,
                $data['kemampuan_merakit_objek'] ?? null,
                $data['sistematika_dalam_bekerja'] ?? null,
            ];
        }
        $data['xb_score'] = $this->calculateAverage($aspekB);

        // Calculate XC Score (Aspek C) - same fields for both types
        $aspekC = [
            $data['motivasi'] ?? null,
            $data['kemampuan_membuat_keputusan'] ?? null,
            $data['kemampuan_kerja_sama_kelompok'] ?? null,
            $data['kemampuan_menjadi_pemimpin'] ?? null,
            $data['kemampuan_berfikir_positif'] ?? null,
            $data['ketekunan_dalam_bekerja'] ?? null,
            $data['kejujuran_berpendapat'] ?? null,
            $data['tanggung_jawab_dalam_bekerja'] ?? null,
            $data['motif_dalam_berprestasi'] ?? null,
            $data['afiliasi'] ?? null,
            $data['motif_menolong_orang_lain'] ?? null,
            $data['kestabilan_emosi'] ?? null,
            $data['kematangan_sosial'] ?? null,
            $data['rasa_percaya_diri'] ?? null,
            $data['penyesuaian_diri'] ?? null,
            $data['kejujuran_dalam_bekerja'] ?? null,
        ];
        $data['xc_score'] = $this->calculateAverage($aspekC);

        // Calculate XT Score (Total)
        $allScores = array_merge($aspekA, $aspekB, $aspekC);
        $data['xt_score'] = $this->calculateAverage($allScores);

        return $data;
    }

    /**
     * Calculate average of non-null values.
     */
    private function calculateAverage(array $values)
    {
        // Filter out null, empty string, and 0 values (only count valid 1-5 scores)
        $filtered = array_filter($values, fn($v) => $v !== null && $v !== '' && $v > 0);
        if (count($filtered) === 0) {
            return null;
        }
        return round(array_sum($filtered) / count($filtered), 2);
    }
}
