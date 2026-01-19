@extends('layouts.app')

@section('title', 'Detail Pelamar - ' . $applicant->nama_lengkap)

@section('content')
{{-- Profile Header --}}
<div class="profile-header fade-in-up">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="profile-avatar-large {{ $applicant->gender == 'Laki-laki' ? 'male' : 'female' }}">
                {{ strtoupper(substr($applicant->nama_lengkap, 0, 2)) }}
            </div>
            <div class="profile-info">
                <h1 class="profile-name">{{ $applicant->nama_lengkap }}</h1>
                <div class="profile-meta">
                    <span class="profile-meta-item">
                        <i class="bi bi-geo-alt-fill"></i> {{ $applicant->kota }}, {{ $applicant->provinsi }}
                    </span>
                    <span class="profile-meta-item">
                        <i class="bi bi-telephone-fill"></i> {{ $applicant->no_hp_1 }}
                    </span>
                    <span class="profile-meta-item">
                        <i class="bi bi-calendar3"></i> {{ $applicant->ttl }}
                    </span>
                </div>
                <div class="profile-badges">
                    @if($applicant->is_guru)
                        <span class="profile-badge"><i class="bi bi-mortarboard"></i> Guru</span>
                    @endif
                    @if($applicant->is_pkl)
                        <span class="profile-badge"><i class="bi bi-briefcase"></i> PKL</span>
                    @endif
                    @if(!$applicant->is_guru && !$applicant->is_pkl)
                        <span class="profile-badge"><i class="bi bi-person"></i> Reguler</span>
                    @endif
                    <span class="profile-badge">
                        @switch($applicant->status)
                            @case('pending')
                                <i class="bi bi-clock"></i> Pending
                                @break
                            @case('tested')
                                <i class="bi bi-check-circle"></i> Sudah Test
                                @break
                            @case('accepted')
                                <i class="bi bi-trophy"></i> Diterima
                                @break
                            @case('rejected')
                                <i class="bi bi-x-circle"></i> Ditolak
                                @break
                        @endswitch
                    </span>
                </div>
            </div>
        </div>
        <div class="profile-actions">
            <a href="{{ route('applicants.index') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('applicants.edit', $applicant) }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Data Pribadi --}}
        <div class="section-card fade-in-up" style="animation-delay: 0.1s">
            <div class="section-header">
                <div class="section-header-icon primary">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h5 class="section-title">Data Pribadi</h5>
            </div>
            <div class="section-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nama Lengkap</span>
                        <span class="info-value">{{ $applicant->nama_lengkap }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">No. KTP</span>
                        <span class="info-value">{{ $applicant->no_ktp }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Alamat</span>
                        <span class="info-value">{{ $applicant->alamat }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Kota & Provinsi</span>
                        <span class="info-value">{{ $applicant->kota }}, {{ $applicant->provinsi }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">No. HP 1</span>
                        <span class="info-value">
                            <a href="tel:{{ $applicant->no_hp_1 }}">
                                <i class="bi bi-telephone me-1"></i>{{ $applicant->no_hp_1 }}
                            </a>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">No. HP 2</span>
                        <span class="info-value">
                            @if($applicant->no_hp_2)
                                <a href="tel:{{ $applicant->no_hp_2 }}">
                                    <i class="bi bi-telephone me-1"></i>{{ $applicant->no_hp_2 }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal Lamaran</span>
                        <span class="info-value">{{ $applicant->tanggal_lamaran->format('d M Y') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal Test</span>
                        <span class="info-value">{{ $applicant->tanggal_test?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jenis Kelamin</span>
                        <span class="info-value">
                            <i class="bi bi-{{ $applicant->gender == 'Laki-laki' ? 'gender-male text-primary' : 'gender-female text-danger' }} me-1"></i>
                            {{ $applicant->gender }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Umur</span>
                        <span class="info-value">{{ $applicant->umur }} tahun</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Pendidikan --}}
        <div class="section-card fade-in-up" style="animation-delay: 0.2s">
            <div class="section-header">
                <div class="section-header-icon success">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h5 class="section-title">Data Pendidikan</h5>
            </div>
            <div class="section-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nama Sekolah/Universitas</span>
                        <span class="info-value">{{ $applicant->nama_sekolah }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jurusan</span>
                        <span class="info-value">{{ $applicant->jurusan }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tahun Lulus</span>
                        <span class="info-value">{{ $applicant->tahun_lulus }}</span>
                    </div>
                    @if($applicant->ipk)
                    <div class="info-item">
                        <span class="info-label">IPK</span>
                        <span class="info-value">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            {{ number_format($applicant->ipk, 2) }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Data PKL (jika ada) --}}
        @if($applicant->is_pkl)
        <div class="section-card fade-in-up" style="animation-delay: 0.25s">
            <div class="section-header">
                <div class="section-header-icon secondary">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <h5 class="section-title">Data PKL</h5>
            </div>
            <div class="section-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Periode PKL</span>
                        <span class="info-value">
                            <i class="bi bi-calendar-range me-1 text-muted"></i>
                            {{ $applicant->pkl_awal?->format('d M Y') }} - {{ $applicant->pkl_akhir?->format('d M Y') }}
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Asal Sekolah</span>
                        <span class="info-value">{{ $applicant->pkl_asal_sekolah }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jurusan</span>
                        <span class="info-value">{{ $applicant->pkl_jurusan }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tempat PKL</span>
                        <span class="info-value">{{ $applicant->pkl_tempat }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Catatan --}}
        @if($applicant->catatan)
        <div class="section-card fade-in-up" style="animation-delay: 0.3s">
            <div class="section-header">
                <div class="section-header-icon light">
                    <i class="bi bi-journal-text"></i>
                </div>
                <h5 class="section-title">Catatan</h5>
            </div>
            <div class="section-body">
                <div class="notes-content">
                    {{ $applicant->catatan }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        {{-- Status Update --}}
        @if($applicant->status !== 'pending' || $applicant->psikotestReport)
        <div class="action-card slide-in-right" style="animation-delay: 0.15s">
            <div class="action-header gradient-warning">
                <i class="bi bi-flag-fill"></i>
                Ubah Status
            </div>
            <div class="action-body">
                <div class="status-current">
                    <span class="status-label">Status saat ini:</span>
                    @switch($applicant->status)
                        @case('pending')
                            <span class="badge-status badge-pending">
                                <i class="bi bi-clock"></i> Pending
                            </span>
                            @break
                        @case('tested')
                            <span class="badge-status badge-tested">
                                <i class="bi bi-check-circle"></i> Sudah Test
                            </span>
                            @break
                        @case('accepted')
                            <span class="badge-status badge-accepted">
                                <i class="bi bi-check-circle-fill"></i> Diterima
                            </span>
                            @break
                        @case('rejected')
                            <span class="badge-status badge-rejected">
                                <i class="bi bi-x-circle"></i> Ditolak
                            </span>
                            @break
                    @endswitch
                </div>
                
                <div class="d-grid gap-2">
                    @if($applicant->status === 'tested')
                        <form action="{{ route('applicants.updateStatus', $applicant) }}" method="POST" class="status-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="btn-action-lg btn-accept">
                                <i class="bi bi-check-circle-fill"></i> Terima Pelamar
                            </button>
                        </form>
                        <form action="{{ route('applicants.updateStatus', $applicant) }}" method="POST" class="status-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="btn-action-lg btn-reject">
                                <i class="bi bi-x-circle-fill"></i> Tolak Pelamar
                            </button>
                        </form>
                    @elseif($applicant->status === 'pending' && $applicant->psikotestReport)
                        <form action="{{ route('applicants.updateStatus', $applicant) }}" method="POST" class="status-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="tested">
                            <button type="submit" class="btn-action-lg" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white;">
                                <i class="bi bi-clipboard-check-fill"></i> Tandai Sudah Test
                            </button>
                        </form>
                    @elseif($applicant->status === 'accepted' || $applicant->status === 'rejected')
                        <form action="{{ route('applicants.updateStatus', $applicant) }}" method="POST" class="status-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="tested">
                            <button type="submit" class="btn-action-outline secondary">
                                <i class="bi bi-arrow-counterclockwise"></i> Ubah Status menjadi Sudah Test
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Aksi --}}
        <div class="action-card slide-in-right" style="animation-delay: 0.2s">
            <div class="action-header gradient-dark">
                <i class="bi bi-gear-fill"></i>
                Aksi
            </div>
            <div class="action-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('applicants.edit', $applicant) }}" class="btn-action-outline primary">
                        <i class="bi bi-pencil-square"></i> Edit Data Pelamar
                    </a>
                    
                    @if($applicant->psikotestReport)
                        <a href="{{ route('psikotest.show', $applicant) }}" class="btn-action-outline info">
                            <i class="bi bi-file-earmark-text"></i> Lihat Laporan Psikotest
                        </a>
                        <a href="{{ route('psikotest.edit', $applicant) }}" class="btn-action-outline info">
                            <i class="bi bi-pencil"></i> Edit Laporan Psikotest
                        </a>
                    @else
                        <a href="{{ route('psikotest.create', $applicant) }}" class="btn-action-outline success">
                            <i class="bi bi-clipboard-plus"></i> Isi Laporan Psikotest
                        </a>
                    @endif

                    <hr class="my-2">

                    <form action="{{ route('applicants.destroy', $applicant) }}" method="POST" data-confirm="true">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action-outline danger">
                            <i class="bi bi-trash3"></i> Hapus Data Pelamar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Info Tambahan --}}
        <div class="info-footer slide-in-right" style="animation-delay: 0.25s">
            <div class="info-footer-title">
                <i class="bi bi-info-circle"></i> Info
            </div>
            <div class="info-footer-item">
                <strong>Tipe:</strong>
                @if($applicant->is_guru)
                    <span class="type-badge guru"><i class="bi bi-mortarboard-fill"></i> Guru</span>
                @elseif($applicant->is_pkl)
                    <span class="type-badge pkl"><i class="bi bi-briefcase-fill"></i> PKL</span>
                @else
                    <span class="type-badge reguler"><i class="bi bi-person-fill"></i> Reguler</span>
                @endif
            </div>
            <div class="info-footer-item">
                <i class="bi bi-plus-circle text-success"></i>
                <strong>Dibuat:</strong> {{ $applicant->created_at->format('d M Y H:i') }}
            </div>
            <div class="info-footer-item">
                <i class="bi bi-pencil-square text-primary"></i>
                <strong>Diperbarui:</strong> {{ $applicant->updated_at->format('d M Y H:i') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const status = form.querySelector('input[name="status"]').value;
            let title, text, icon, confirmButtonColor;
            
            switch(status) {
                case 'accepted':
                    title = 'Terima Pelamar?';
                    text = 'Pelamar akan ditandai sebagai DITERIMA';
                    icon = 'question';
                    confirmButtonColor = '#198754';
                    break;
                case 'rejected':
                    title = 'Tolak Pelamar?';
                    text = 'Pelamar akan ditandai sebagai DITOLAK. Pelamar tidak dapat melamar lagi selama 1 tahun.';
                    icon = 'warning';
                    confirmButtonColor = '#dc3545';
                    break;
                case 'tested':
                    title = 'Ubah Status?';
                    text = 'Status akan diubah menjadi SUDAH TEST';
                    icon = 'question';
                    confirmButtonColor = '#0dcaf0';
                    break;
                default:
                    title = 'Ubah Status?';
                    text = 'Apakah Anda yakin ingin mengubah status?';
                    icon = 'question';
                    confirmButtonColor = '#0d6efd';
            }
            
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Ubah!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
