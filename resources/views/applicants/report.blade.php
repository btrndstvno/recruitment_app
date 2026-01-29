@extends('layouts.app')

@section('title', 'Reporting View - Recruitment App')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-table me-2"></i>Reporting View</h2>
        <p class="text-muted mb-0">Tampilan data lengkap (Table View) sesuai format export.</p>
    </div>
    <div>
        <a href="{{ route('applicants.index') }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke List
        </a>
        <button type="button" class="btn btn-success" onclick="alert('Silakan gunakan fitur Export di halaman utama untuk mengunduh Excel.')">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </button>
    </div>
</div>
<form method="GET" action="" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-auto">
            <label for="simple_search" class="form-label mb-0">Cari ID atau Nama:</label>
        </div>
        <div class="col-auto">
            <input type="text" name="simple_search" id="simple_search" class="form-control" value="{{ request('simple_search') }}" placeholder="ID atau Nama...">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
        </div>
    </div>
</form>
<div class="card border-0 shadow-sm">
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 80vh; overflow-y: auto;">
            <table class="table table-bordered table-striped table-hover mb-0 w-100" style="font-size: 0.85rem; white-space: nowrap;">
                <thead class="bg-light text-secondary sticky-top" style="z-index: 1;">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">No. Pelamar</th>
                        <th class="p-3 bg-warning bg-opacity-10">Email</th>
                        <th class="p-3">Nama Lengkap</th>
                        <th class="p-3">No. KTP</th>
                        <th class="p-3">No. HP 1</th>
                        <th class="p-3">No. HP 2</th>
                        <th class="p-3">Tempat Lahir</th>
                        <th class="p-3">Tanggal Lahir</th>
                        <th class="p-3 bg-info bg-opacity-10">Umur / Age</th>
                        <th class="p-3">Gender</th>
                        <th class="p-3">Alamat</th>
                        <th class="p-3">Kota</th>
                        <th class="p-3">Provinsi</th>
                        <th class="p-3">Pendidikan</th>
                        <th class="p-3">Jurusan</th>
                        <th class="p-3">Thn Lulus</th>
                        <th class="p-3">IPK</th>
                        <th class="p-3">Tgl Lamaran</th>
                        <th class="p-3">Tgl Test</th>
                        <th class="p-3">Tgl Mulai PKL</th>
                        <th class="p-3">Tgl Selesai PKL</th>
                        <th class="p-3">Asal Sekolah PKL</th>
                        <th class="p-3">Jurusan PKL</th>
                        <th class="p-3">Tempat PKL</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Tipe</th>
                        <th class="p-3">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applicants as $applicant)
                    <tr>
                        <td class="p-2 text-center">{{ $applicant->id }}</td>
                        <td class="p-2 text-center fw-bold">{{ $applicant->applicant_number }}</td>
                        <td class="p-2 fw-bold text-primary">{{ $applicant->email ?? '-' }}</td>
                        <td class="p-2">{{ $applicant->nama_lengkap }}</td>
                        <td class="p-2">{{ $applicant->no_ktp }}</td>
                        <td class="p-2">{{ $applicant->no_hp_1 }}</td>
                        <td class="p-2">{{ $applicant->no_hp_2 ?? '-' }}</td>
                        <td class="p-2">{{ $applicant->tempat_lahir }}</td>
                        <td class="p-2">{{ $applicant->tanggal_lahir ? $applicant->tanggal_lahir->format('d-m-Y') : '-' }}</td>
                        <td class="p-2 text-center fw-bold">{{ $applicant->umur }} Tahun</td>
                        <td class="p-2">{{ $applicant->gender }}</td>
                        <td class="p-2 text-wrap" style="min-width: 200px;">{{ $applicant->alamat }}</td>
                        <td class="p-2">{{ $applicant->kota }}</td>
                        <td class="p-2">{{ $applicant->provinsi }}</td>
                        <td class="p-2">{{ $applicant->nama_sekolah }}</td>
                        <td class="p-2">{{ $applicant->jurusan }}</td>
                        <td class="p-2">{{ $applicant->tahun_lulus }}</td>
                        <td class="p-2">{{ $applicant->ipk }}</td>
                        <td class="p-2">{{ $applicant->tanggal_lamaran ? $applicant->tanggal_lamaran->format('d-m-Y') : '-' }}</td>
                        <td class="p-2">{{ $applicant->tanggal_test ? $applicant->tanggal_test->format('d-m-Y') : '-' }}</td>
                        <td class="p-2">{{ $applicant->is_pkl && $applicant->pkl_awal ? $applicant->pkl_awal->format('d-m-Y') : '' }}</td>
                        <td class="p-2">{{ $applicant->is_pkl && $applicant->pkl_akhir ? $applicant->pkl_akhir->format('d-m-Y') : '' }}</td>
                        <td class="p-2">{{ $applicant->is_pkl ? $applicant->pkl_asal_sekolah : '' }}</td>
                        <td class="p-2">{{ $applicant->is_pkl ? $applicant->pkl_jurusan : '' }}</td>
                        <td class="p-2">{{ $applicant->is_pkl ? $applicant->pkl_tempat : '' }}</td>
                        <td class="p-2">
                            @if($applicant->status == 'pending') <span class="badge bg-secondary">Pending</span>
                            @elseif($applicant->status == 'tested') <span class="badge bg-info">Tested</span>
                            @elseif($applicant->status == 'accepted') <span class="badge bg-success">Accepted</span>
                            @elseif($applicant->status == 'rejected') <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($applicant->is_guru) <span class="badge bg-primary">Guru</span>
                            @elseif($applicant->is_pkl) <span class="badge bg-warning text-dark">PKL</span>
                            @else <span class="badge bg-light text-dark border">Reguler</span>
                            @endif
                        </td>
                        <td class="p-2 text-wrap" style="min-width: 150px;">{{ $applicant->catatan }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="25" class="text-center p-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            Belum ada data pelamar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
