<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\PsikotestReport;
use Barryvdh\DomPDF\Facade\Pdf;
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

        // Sinkronisasi tanggal_test dengan data applicant
        if (empty($applicant->tanggal_test)) {
            // Jika applicant belum punya tanggal_test, ambil dari input atau gunakan hari ini
            $validated['tanggal_test'] = $request->input('tanggal_test') ?? now()->toDateString();
            $applicant->update([
                'tanggal_test' => $validated['tanggal_test'],
                'status' => 'tested',
            ]);
        } else {
            // Jika sudah ada, gunakan tanggal_test dari applicant
            $validated['tanggal_test'] = $applicant->tanggal_test;
            if ($applicant->status !== 'tested') {
                $applicant->update(['status' => 'tested']);
            }
        }

        // Menggunakan method static di Model untuk memastikan pembagi tetap (Fixed Divisor)
        $validated = PsikotestReport::calculateScores($validated, $reportType);

        PsikotestReport::create($validated);

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

        // Untuk aspek B, siapkan field sesuai tipe (mengambil helper dari Model)
        $aspekBFields = $reportType == '38'
            ? \App\Models\PsikotestReport::getAspekBFields38()
            : \App\Models\PsikotestReport::getAspekBFields();

        // Untuk aspek C, siapkan field sesuai tipe (mengambil helper dari Model)
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

        // Ambil tipe dari request form (hidden input) atau fallback ke database
        $reportType = $request->input('report_type', $report->report_type ?? '34');
        if (!in_array($reportType, ['34', '38'])) {
            $reportType = '34';
        }

        $validated = $this->validateReport($request, $reportType);
        $validated['report_type'] = $reportType;

        // Sinkronisasi tanggal_test
        if (empty($applicant->tanggal_test)) {
            $validated['tanggal_test'] = $request->input('tanggal_test');
            $applicant->update([
                'tanggal_test' => $validated['tanggal_test'],
                'status' => 'tested',
            ]);
        } else {
            $validated['tanggal_test'] = $applicant->tanggal_test;
            if ($applicant->status !== 'tested') {
                $applicant->update(['status' => 'tested']);
            }
        }

        // PENGHITUNGAN SKOR (Perbaikan):
        $validated = PsikotestReport::calculateScores($validated, $reportType);

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
     * Download the psikotest report as PDF.
     */
    public function downloadPdf(Applicant $applicant)
    {
        $report = $applicant->psikotestReport;

        if (!$report) {
            return redirect()->route('applicants.show', $applicant)
                ->with('error', 'Laporan psikotest belum ada.');
        }

        $pdf = Pdf::loadView('psikotest.pdf', compact('applicant', 'report'));
        $pdf->setPaper([0, 0, 609.449, 935.433]); // 215mm x 330mm in points

        $filename = 'Psikotest_' . str_replace(' ', '_', $applicant->nama_lengkap) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Validate the report request.
     */
    private function validateReport(Request $request, string $reportType = '34')
    {
        $rules = [
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
}