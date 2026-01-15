<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_lengkap',
        'alamat',
        'kota',
        'provinsi',
        'no_hp_1',
        'no_hp_2',
        'no_ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'gender',
        'umur',
        'tanggal_lamaran',
        'nama_sekolah',
        'jurusan',
        'tahun_lulus',
        'ipk',
        'is_guru',
        'is_pkl',
        'pkl_awal',
        'pkl_akhir',
        'pkl_asal_sekolah',
        'pkl_jurusan',
        'pkl_tempat',
        'catatan',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_lamaran' => 'date',
        'pkl_awal' => 'date',
        'pkl_akhir' => 'date',
        'is_guru' => 'boolean',
        'is_pkl' => 'boolean',
        'ipk' => 'decimal:2',
    ];

    /**
     * Get the psikotest report for this applicant.
     */
    public function psikotestReport()
    {
        return $this->hasOne(PsikotestReport::class);
    }

    /**
     * Get full TTL (Tempat Tanggal Lahir)
     */
    public function getTtlAttribute()
    {
        return $this->tempat_lahir . ', ' . $this->tanggal_lahir->format('d F Y');
    }

    /**
     * Get pendidikan info
     */
    public function getPendidikanAttribute()
    {
        return $this->nama_sekolah . ', ' . $this->jurusan . ', ' . $this->tahun_lulus;
    }
}
