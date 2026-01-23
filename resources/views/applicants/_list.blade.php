{{-- DEFINISI FUNGSI HELPER (Wajib ada di file ini agar terbaca saat AJAX) --}}
@php
    if (!function_exists('getSortUrl')) {
        function getSortUrl($column) {
            $currentSort = request('sort');
            $currentDir = request('direction', 'desc');
            $newDir = ($currentSort == $column && $currentDir == 'asc') ? 'desc' : 'asc';
            return route('applicants.index', array_merge(request()->query(), ['sort' => $column, 'direction' => $newDir]));
        }
    }

    if (!function_exists('getSortIcon')) {
        function getSortIcon($column) {
            $currentSort = request('sort');
            $currentDir = request('direction', 'desc');

            if ($currentSort !== $column) {
                return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size: 0.8em; opacity: 0.5;"></i>';
            }

            return $currentDir == 'asc' 
                ? '<i class="bi bi-sort-alpha-down ms-1 text-primary"></i>' 
                : '<i class="bi bi-sort-alpha-down-alt ms-1 text-primary"></i>';
        }
    }
@endphp

@if($applicants->count() > 0)
    {{-- Desktop Table View --}}
    <div class="d-none d-md-block">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 60px; min-width: 60px;">No</th>
                    <th>
                        <a href="{{ getSortUrl('nama_lengkap') }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between w-100 request-sort">
                            <span>Nama Pelamar</span>
                            {!! getSortIcon('nama_lengkap') !!}
                        </a>
                    </th>
                    <th>No. Telp</th>
                    <th class="d-none d-lg-table-cell">
                        <a href="{{ getSortUrl('tanggal_test') }}" class="text-decoration-none text-dark d-flex align-items-center justify-content-between w-100 request-sort">
                            <span>Tanggal test</span>
                            {!! getSortIcon('tanggal_test') !!}
                        </a>
                    </th>
                    <th>Status</th>
                    <th style="width: 80px">Aksi</th>
                </tr>
            </thead>    

            <tbody>
                @foreach($applicants as $index => $applicant)
                    <tr data-id="{{ $applicant->id }}" class="selectable-row">
                        <td class="text-center">
                            <span class="text-muted fw-medium">{{ $applicants->firstItem() + $index }}</span>
                        </td>
                        <td>
                            <div class="applicant-info">
                                {{-- Avatar & Checkbox Logic --}}
                                <div class="applicant-avatar selectable-avatar {{ $applicant->gender == 'Laki-laki' ? 'male' : 'female' }}" 
                                     data-id="{{ $applicant->id }}" title="Klik untuk memilih">
                                    <span class="avatar-initials">{{ strtoupper(substr($applicant->nama_lengkap, 0, 2)) }}</span>
                                    <span class="avatar-check"><i class="bi bi-check-lg"></i></span>
                                </div>
                                <div>
                                    <div class="applicant-name d-flex align-items-center gap-2">
                                        <span title="Color Code" class="flex-shrink-0" style="display:inline-block;width:16px;height:16px;border-radius:50%;border:1.5px solid #ffffff;
                                            background:
                                                {{
                                                    $applicant->color_code == 'merah' ? '#e74c3c' :
                                                    ($applicant->color_code == 'kuning' ? '#f1c40f' :
                                                    ($applicant->color_code == 'biru' ? '#3498db' :
                                                    ($applicant->color_code == 'hijau' ? '#27ae60' :
                                                    ($applicant->color_code == 'hitam' ? '#222222' :
                                                    ($applicant->color_code == 'abu-abu' ? '#95a5a6' : '#95a5a6') ))))
                                                }};"></span>
                                        <span class="text-truncate">{{ $applicant->nama_lengkap }}</span>
                                    </div>
                                    <div class="applicant-meta">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $applicant->kota }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="tel:{{ $applicant->no_hp_1 }}" class="text-decoration-none">
                                <i class="bi bi-telephone me-1 text-muted"></i>{{ $applicant->no_hp_1 }}
                            </a>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            <i class="bi bi-calendar3 me-1 text-muted"></i>
                            {{ $applicant->tanggal_test ? \Carbon\Carbon::parse($applicant->tanggal_test)->format('d M Y') : '-' }}
                        </td>
                        <td>
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
                        </td>
                        <td class="text-end">
                            <a href="{{ route('applicants.show', $applicant) }}?{{ http_build_query(request()->query()) }}" class="btn btn-info btn-action btn-sm text-white">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile Card View --}}
    <div class="d-md-none">
        <div class="applicant-list-mobile">
            @foreach($applicants as $index => $applicant)
                <div class="applicant-card-mobile selectable-card" data-id="{{ $applicant->id }}">
                    <div class="applicant-card-left">
                        <div class="applicant-avatar selectable-avatar {{ $applicant->gender == 'Laki-laki' ? 'male' : 'female' }}" 
                             data-id="{{ $applicant->id }}" title="Klik untuk memilih">
                            <span class="avatar-initials">{{ strtoupper(substr($applicant->nama_lengkap, 0, 2)) }}</span>
                            <span class="avatar-check"><i class="bi bi-check-lg"></i></span>
                        </div>
                    </div>
                    <a href="{{ route('applicants.show', $applicant) }}?{{ http_build_query(request()->query()) }}" class="applicant-card-content">
                        <div class="applicant-card-name">{{ $applicant->nama_lengkap }}</div>
                        <div class="applicant-card-name d-flex align-items-center gap-2">
                            @if($applicant->color_code)
                                <span title="Warna Penanda" class="flex-shrink-0" style="display:inline-block;width:14px;height:14px;border-radius:50%;border:1.5px solid #eee;
                                    background:
                                        {{
                                            $applicant->color_code == 'merah' ? '#e74c3c' :
                                            ($applicant->color_code == 'kuning' ? '#f1c40f' :
                                            ($applicant->color_code == 'biru' ? '#3498db' :
                                            ($applicant->color_code == 'hijau' ? '#27ae60' :
                                            ($applicant->color_code == 'hitam' ? '#222222' :
                                            ($applicant->color_code == 'abu-abu' ? '#95a5a6' : 'transparent') ))))
                                        }};"></span>
                            @endif
                            <span class="text-truncate">{{ $applicant->nama_lengkap }}</span>
                        </div>
                        <div class="applicant-card-meta">
                            <span><i class="bi bi-geo-alt"></i> {{ $applicant->kota }}</span>
                            <span><i class="bi bi-calendar3"></i> {{ $applicant->tanggal_test ? \Carbon\Carbon::parse($applicant->tanggal_test)->format('d M Y') : '-' }}</span>
                        </div>
                    </a>
                    <div class="applicant-card-right">
                        @switch($applicant->status)
                            @case('pending')
                                <span class="badge-mobile badge-pending">Pending</span>
                                @break
                            @case('tested')
                                <span class="badge-mobile badge-tested">Sudah Test</span>
                                @break
                            @case('accepted')
                                <span class="badge-mobile badge-accepted">Diterima</span>
                                @break
                            @case('rejected')
                                <span class="badge-mobile badge-rejected">Ditolak</span>
                                @break
                        @endswitch
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if($applicants->hasPages())
        <div class="d-flex justify-content-center py-3 border-top">
            {{ $applicants->links() }}
        </div>
    @endif
@else
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="bi bi-inbox"></i>
        </div>
        @if(request('archived') == '1')
            <h5>Belum ada pelamar yang diarsipkan</h5>
            <p>Belum ada data pelamar yang masuk ke arsip.</p>
        @else
            <h5>Belum Ada Data Pelamar</h5>
            <p>Mulai tambahkan data pelamar pertama Anda</p>
            <a href="{{ route('applicants.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Tambah Pelamar
            </a>
        @endif
    </div>
@endif

{{-- SCRIPT KHUSUS UTK SORTING AJAX --}}
<script>
    // Pastikan link sorting menggunakan AJAX juga
    document.querySelectorAll('.request-sort').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if(typeof submitFilter === 'function') {
                submitFilter(this.href);
            }
        });
    });
</script>