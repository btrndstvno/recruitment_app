<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsikotestReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'report_type',
        'tanggal_test',
        'iq_score',
        'iq_category',
        'result',
        'kesimpulan_saran',
        'kemampuan_memecahkan_masalah',
        'ruang_lingkup_pengetahuan',
        'kemampuan_berfikir_analitis',
        'kemampuan_bekerja_dengan_angka',
        'kemampuan_berfikir_logis',
        'kemampuan_berfikir_abstrak',
        'kemampuan_mengingat',
        'xa_score',
        'kecepatan_dalam_bekerja',
        'ketelitian_dalam_bekerja',
        'kestabilan_dalam_bekerja',
        'ketahanan_kerja',
        'kemampuan_konsentrasi_persepsi',
        'kemampuan_berhitung',
        'kemampuan_mengemukakan_pendapat',
        'kemampuan_penalaran_non_verbal',
        'persepsi_ruang_bidang',
        'kemampuan_dasar_mekanik',
        'kemampuan_mengidentifikasi_komponen',
        'kemampuan_bekerja_dengan_angka_computational',
        'kemampuan_penalaran_mekanik',
        'kemampuan_membaca_memahami_logis',
        'kemampuan_administrasi_kompleks',
        'kemampuan_merakit_objek',
        'sistematika_dalam_bekerja',
        'xb_score',
        'motivasi',
        'kemampuan_membuat_keputusan',
        'kemampuan_kerja_sama_kelompok',
        'kemampuan_menjadi_pemimpin',
        'kemampuan_berfikir_positif',
        'ketekunan_dalam_bekerja',
        'kejujuran_berpendapat',
        'tanggung_jawab_dalam_bekerja',
        'motif_dalam_berprestasi',
        'afiliasi',
        'motif_menolong_orang_lain',
        'kestabilan_emosi',
        'kematangan_sosial',
        'rasa_percaya_diri',
        'penyesuaian_diri',
        'kejujuran_dalam_bekerja',
        'xc_score',
        'xt_score',
    ];

    /**
     * Calculate average scores for each section (static, reusable).
     */
    public static function calculateScores(array $data, string $reportType = '34')
    {
       // 1. Aspek A (XA) - Selalu 7 Soal
        $aspekA = [
            $data['kemampuan_memecahkan_masalah'] ?? 0,
            $data['ruang_lingkup_pengetahuan'] ?? 0,
            $data['kemampuan_berfikir_analitis'] ?? 0,
            $data['kemampuan_bekerja_dengan_angka'] ?? 0,
            $data['kemampuan_berfikir_logis'] ?? 0,
            $data['kemampuan_berfikir_abstrak'] ?? 0,
            $data['kemampuan_mengingat'] ?? 0,
        ];
        // Rumus: Total Nilai / 7
        $data['xa_score'] = self::calculateFixedAverage($aspekA, 7);

        // 2. Aspek B (XB) - Tergantung Tipe
        if ($reportType === '34') {
            $aspekB = [
                $data['kecepatan_dalam_bekerja'] ?? 0,
                $data['ketelitian_dalam_bekerja'] ?? 0,
                $data['kestabilan_dalam_bekerja'] ?? 0,
                $data['ketahanan_kerja'] ?? 0,
                $data['kemampuan_konsentrasi_persepsi'] ?? 0,
                $data['kemampuan_berhitung'] ?? 0,
                $data['kemampuan_mengemukakan_pendapat'] ?? 0,
                $data['kemampuan_penalaran_non_verbal'] ?? 0,
                $data['kemampuan_membaca_memahami_logis'] ?? 0,
                $data['kemampuan_administrasi_kompleks'] ?? 0,
                $data['sistematika_dalam_bekerja'] ?? 0,
            ];
            // Rumus XB Tipe 34: Total Nilai / 11
            $divisorB = 11;
        } else {
            // Tipe 38
            $aspekB = [
                $data['kecepatan_dalam_bekerja'] ?? 0,
                $data['ketelitian_dalam_bekerja'] ?? 0,
                $data['kestabilan_dalam_bekerja'] ?? 0,
                $data['ketahanan_kerja'] ?? 0,
                $data['kemampuan_konsentrasi_persepsi'] ?? 0,
                $data['kemampuan_mengemukakan_pendapat'] ?? 0,
                $data['kemampuan_penalaran_non_verbal'] ?? 0,
                $data['persepsi_ruang_bidang'] ?? 0,
                $data['kemampuan_dasar_mekanik'] ?? 0,
                $data['kemampuan_mengidentifikasi_komponen'] ?? 0,
                $data['kemampuan_bekerja_dengan_angka_computational'] ?? 0,
                $data['kemampuan_penalaran_mekanik'] ?? 0,
                $data['kemampuan_membaca_memahami_logis'] ?? 0,
                $data['kemampuan_merakit_objek'] ?? 0,
                $data['sistematika_dalam_bekerja'] ?? 0,
            ];
            // Rumus XB Tipe 38: Total Nilai / 15
            $divisorB = 15;
        }
        $data['xb_score'] = self::calculateFixedAverage($aspekB, $divisorB);

        // 3. Aspek C (XC) - Selalu 16 Soal
        $aspekC = [
            $data['motivasi'] ?? 0,
            $data['kemampuan_membuat_keputusan'] ?? 0,
            $data['kemampuan_kerja_sama_kelompok'] ?? 0,
            $data['kemampuan_menjadi_pemimpin'] ?? 0,
            $data['kemampuan_berfikir_positif'] ?? 0,
            $data['ketekunan_dalam_bekerja'] ?? 0,
            $data['kejujuran_berpendapat'] ?? 0,
            $data['tanggung_jawab_dalam_bekerja'] ?? 0,
            $data['motif_dalam_berprestasi'] ?? 0,
            $data['afiliasi'] ?? 0,
            $data['motif_menolong_orang_lain'] ?? 0,
            $data['kestabilan_emosi'] ?? 0,
            $data['kematangan_sosial'] ?? 0,
            $data['rasa_percaya_diri'] ?? 0,
            $data['penyesuaian_diri'] ?? 0,
            $data['kejujuran_dalam_bekerja'] ?? 0,
        ];
        // Rumus: Total Nilai / 16
        $data['xc_score'] = self::calculateFixedAverage($aspekC, 16);

        // 4. Aspek Total (XT)
        // Hitung total item keseluruhan: 7 + (11 atau 15) + 16
        $totalItems = 7 + $divisorB + 16; 
        
        // Gabungkan semua nilai
        $allScores = array_merge($aspekA, $aspekB, $aspekC);
        
        // Rumus XT: Total Seluruh Nilai / Total Jumlah Soal (34 atau 38)
        $data['xt_score'] = self::calculateFixedAverage($allScores, $totalItems);

        return $data;
    }

    /**
     * Calculate average of non-null values (static, reusable).
     */
    public static function calculateAverage(array $values, int $divisor)
    {
        if ($divisor <= 0) return 0;

        $sum = 0;
        foreach ($values as $v) {
            // Pastikan nilai numerik, null/empty string dianggap 0
            $val = floatval($v); 
            $sum += $val;
        }

        // Pembagian langsung dengan fixed divisor
        return round($sum / $divisor, 2);
    }

    protected $casts = [
        'tanggal_test' => 'date',
        'xa_score' => 'decimal:2',
        'xb_score' => 'decimal:2',
        'xc_score' => 'decimal:2',
        'xt_score' => 'decimal:2',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function getIqCategoryLabelAttribute()
    {
        $labels = [
            'borderline' => 'Borderline (70-79)',
            'dibawah_rata_rata' => 'Dibawah Rata-Rata (80-89)',
            'rata_rata' => 'Rata-Rata (90-109)',
            'diatas_rata_rata' => 'Diatas Rata-Rata (110-119)',
            'superior' => 'Superior (120-139)',
            'very_superior' => 'Very Superior (140-169)',
        ];

        return $labels[$this->iq_category] ?? '-';
    }

    // --- HELPER UNTUK IMPORT (Digunakan di Controller) ---

    public static function getAspekAFields()
    {
        return [
            'kemampuan_memecahkan_masalah' => '1. Kemampuan Memecahkan Masalah',
            'ruang_lingkup_pengetahuan' => '2. Ruang Lingkup Pengetahuan',
            'kemampuan_berfikir_analitis' => '3. Kemampuan Berfikir Analitis',
            'kemampuan_bekerja_dengan_angka' => '4. Kemampuan Bekerja Dengan Angka (Reasoning)',
            'kemampuan_berfikir_logis' => '5. Kemampuan Berfikir Logis',
            'kemampuan_berfikir_abstrak' => '6. Kemampuan Berfikir Abstrak',
            'kemampuan_mengingat' => '7. Kemampuan Mengingat',
        ];
    }

    // Mapping Soal Tipe 34 (Total 11 Soal Aspek B)
    public static function getAspekBFields()
    {
        return [
            'kecepatan_dalam_bekerja' => '8. Kecepatan Dalam Bekerja',
            'ketelitian_dalam_bekerja' => '9. Ketelitian Dalam Bekerja',
            'kestabilan_dalam_bekerja' => '10. Kestabilan Dalam Bekerja',
            'ketahanan_kerja' => '11. Ketahanan Kerja',
            'kemampuan_konsentrasi_persepsi' => '12. Kemampuan Konsentrasi & Persepsi',
            'kemampuan_berhitung' => '13. Kemampuan Berhitung (Computational)',
            'kemampuan_mengemukakan_pendapat' => '14. Kemampuan Mengemukakan Pendapat Dengan Bahasa yang Baik',
            'kemampuan_penalaran_non_verbal' => '15. Kemampuan Penalaran Non Verbal',
            'kemampuan_membaca_memahami_logis' => '16. Kemampuan Membaca dan Memahami Alasan Logis',
            'kemampuan_administrasi_kompleks' => '17. Kemampuan Administrasi Kompleks',
            'sistematika_dalam_bekerja' => '18. Sistematika Dalam Bekerja',
        ];
    }

    // Mapping Soal Tipe 38 (Total 15 Soal Aspek B)
    public static function getAspekBFields38()
    {
        return [
            'kecepatan_dalam_bekerja' => '8. Kecepatan Dalam Bekerja',
            'ketelitian_dalam_bekerja' => '9. Ketelitian Dalam Bekerja',
            'kestabilan_dalam_bekerja' => '10. Kestabilan Dalam Bekerja',
            'ketahanan_kerja' => '11. Ketahanan Kerja',
            'kemampuan_konsentrasi_persepsi' => '12. Kemampuan Konsentrasi & Persepsi',
            'kemampuan_mengemukakan_pendapat' => '13. Kemampuan Mengemukakan Pendapat Dengan Bahasa yang Baik',
            'kemampuan_penalaran_non_verbal' => '14. Kemampuan Penalaran Non Verbal',
            'persepsi_ruang_bidang' => '15. Persepsi Ruang Bidang',
            'kemampuan_dasar_mekanik' => '16. Kemampuan Dasar Mekanik',
            'kemampuan_mengidentifikasi_komponen' => '17. Kemampuan Mengidentifikasi Komponen',
            'kemampuan_bekerja_dengan_angka_computational' => '18. Kemampuan Bekerja Dengan Angka (Computational)',
            'kemampuan_penalaran_mekanik' => '19. Kemampuan Penalaran Mekanik',
            'kemampuan_membaca_memahami_logis' => '20. Kemampuan Membaca & Memahami Alasan Logis',
            'kemampuan_merakit_objek' => '21. Kemampuan Merakit Objek',
            'sistematika_dalam_bekerja' => '22. Sistematika Dalam Bekerja',
        ];
    }

    // Mapping Soal Tipe 34 (Total 16 Soal Aspek C)
    public static function getAspekCFields()
    {
        return [
            'motivasi' => '19. Motivasi',
            'kemampuan_membuat_keputusan' => '20. Kemampuan Membuat Keputusan',
            'kemampuan_kerja_sama_kelompok' => '21. Kemampuan Kerja Sama Dalam Kelompok',
            'kemampuan_menjadi_pemimpin' => '22. Kemampuan Menjadi Pemimpin Dalam Kelompok',
            'kemampuan_berfikir_positif' => '23. Kemampuan Berfikir Positif',
            'ketekunan_dalam_bekerja' => '24. Ketekunan Dalam Bekerja',
            'kejujuran_berpendapat' => '25. Kejujuran Berpendapat',
            'tanggung_jawab_dalam_bekerja' => '26. Tanggung Jawab Dalam Bekerja',
            'motif_dalam_berprestasi' => '27. Motif Dalam Berprestasi',
            'afiliasi' => '28. Afiliasi',
            'motif_menolong_orang_lain' => '29. Motif Untuk Menolong Orang Lain',
            'kestabilan_emosi' => '30. Kestabilan Emosi',
            'kematangan_sosial' => '31. Kematangan Sosial',
            'rasa_percaya_diri' => '32. Rasa Percaya Diri',
            'penyesuaian_diri' => '33. Penyesuaian Diri',
            'kejujuran_dalam_bekerja' => '34. Kejujuran Dalam Bekerja',
        ];
    }

    // Mapping Soal Tipe 38 (Total 16 Soal Aspek C)
    public static function getAspekCFields38()
    {
        return [
            'motivasi' => '23. Motivasi',
            'kemampuan_membuat_keputusan' => '24. Kemampuan Membuat Keputusan',
            'kemampuan_kerja_sama_kelompok' => '25. Kemampuan Kerja Sama Dalam Kelompok',
            'kemampuan_menjadi_pemimpin' => '26. Kemampuan Menjadi Pemimpin Dalam Kelompok',
            'kemampuan_berfikir_positif' => '27. Kemampuan Berfikir Positif',
            'ketekunan_dalam_bekerja' => '28. Ketekunan Dalam Bekerja',
            'kejujuran_berpendapat' => '29. Kejujuran Berpendapat',
            'tanggung_jawab_dalam_bekerja' => '30. Tanggung Jawab Dalam Bekerja',
            'motif_dalam_berprestasi' => '31. Motif Dalam Berprestasi',
            'afiliasi' => '32. Afiliasi',
            'motif_menolong_orang_lain' => '33. Motif Untuk Menolong Orang Lain',
            'kestabilan_emosi' => '34. Kestabilan Emosi',
            'kematangan_sosial' => '35. Kematangan Sosial',
            'rasa_percaya_diri' => '36. Rasa Percaya Diri',
            'penyesuaian_diri' => '37. Penyesuaian Diri',
            'kejujuran_dalam_bekerja' => '38. Kejujuran Dalam Bekerja',
        ];
    }
}