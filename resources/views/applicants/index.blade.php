@extends('layouts.app')

@section('title', request('archived') == '1' ? 'Arsip Pelamar - Recruitment App' : 'Daftar Pelamar - Recruitment App')

@section('content')
    @php
        function getSortUrl($column) {
            $currentSort = request('sort');
            $currentDir = request('direction', 'desc');
            
            // Jika kolom sama diklik, balik arahnya (asc <-> desc). Jika beda, default asc.
            $newDir = ($currentSort == $column && $currentDir == 'asc') ? 'desc' : 'asc';
            
            // Merge query string yang ada (filter) dengan sort baru
            return route('applicants.index', array_merge(request()->query(), ['sort' => $column, 'direction' => $newDir]));
        }

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
    @endphp
{{-- Welcome Card --}}
<div class="welcome-card fade-in-up">
    <div class="d-flex align-items-center justify-content-between">
        {{-- Sisi Kiri: Ucapan Selamat Datang --}}
        <div class="d-flex align-items-center">
            <span class="welcome-icon">👋</span>
            <div>
                <h4 class="mb-0">Hallo, {{ Auth::user()->name }}!</h4>
                <small class="text-muted">Selamat datang di Recruitment Adiputro</small>
            </div>
        </div>

        {{-- Jam Realtime --}}
        <div class="realtime-clock text-end">
            <div class="clock-time" id="clockTime">00:00:00</div>
            <div class="clock-date" id="clockDate">Senin, 13 Januari 2026</div>
        </div>  
    </div>
</div>

{{-- Page Header --}}
<div class="page-header fade-in-up" style="animation-delay: 0.1s">
    <h2 class="page-title">
        @if(request('archived') == '1')
            <i class="bi bi-archive-fill text-warning"></i>
            Arsip Pelamar
        @else
            <i class="bi bi-people-fill"></i>
            Daftar Pelamar
        @endif
    </h2>
    <div class="d-flex gap-2">
        @if(request('archived') == '1')
            <a href="{{ route('applicants.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard
            </a>
        @else
            <!-- <a href="{{ route('import.form') }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-arrow-up me-1"></i>Import Excel
            </a> -->
            <a href="{{ route('applicants.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Tambah Pelamar
            </a>
        @endif
    </div>
</div>

{{-- Filter & Search --}}
<div class="card filter-card mb-4 fade-in-up" style="animation-delay: 0.2s">
    <div class="card-body">
        <form id="filterForm" action="{{ route('applicants.index', request('archived') == '1' ? ['archived' => 1] : []) }}" method="GET">
            {{-- Row 1: Search, Type, Status --}}
            <div class="filter-section">
                <div class="filter-section-title">
                    <i class="bi bi-funnel-fill"></i> Filter Pencarian
                </div>
                <div class="row g-3">
                    <div class="col-lg-6 col-md-12">
                        <label for="search" class="form-label">
                            <i class="bi bi-search me-1"></i>Cari Pelamar
                        </label>
                        <div class="input-group input-group-search">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="Ketik nama, no. telp, alamat, atau no. KTP..."
                                   value="{{ request('search') }}">
                            <span class="input-group-text d-none" id="searchLoading">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <label for="tipe" class="form-label">
                            <i class="bi bi-person-badge me-1"></i>Tipe Pelamar
                        </label>
                        <select class="form-select" id="tipe" name="tipe">
                            <option value="">Semua Tipe</option>
                            <option value="guru" {{ request('tipe') == 'guru' ? 'selected' : '' }}>👨‍🏫 Guru</option>
                            <option value="pkl" {{ request('tipe') == 'pkl' ? 'selected' : '' }}>🎓PKL</option>
                            <option value="reguler" {{ request('tipe') == 'reguler' ? 'selected' : '' }}>👤 Reguler</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <label for="status" class="form-label">
                            <i class="bi bi-flag me-1"></i>Status
                        </label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                            <option value="tested" {{ request('status') == 'tested' ? 'selected' : '' }}>✅ Sudah Test</option>
                            <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>🎉 Diterima</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}> Ditolak</option>
                        </select>
                    </div>
                    <!-- <div class="col-lg-2 col-md-6 col-6">
                        <label for="archived" class="form-label">
                            <i class="bi bi-archive me-1"></i>Archive
                        </label>
                        <select class="form-select" id="archived" name="archived">
                            <option value="" {{ !request('archived') ? 'selected' : '' }}> Aktif</option>
                            <option value="1" {{ request('archived') == '1' ? 'selected' : '' }}> Diarsipkan</option>
                        </select>
                    </div> -->
                </div>
            </div>
            
            {{-- Divider --}}
            <hr class="filter-divider">
            
            {{-- Row 2: Date Filters --}}
            <div class="filter-section">
                <div class="filter-section-title">
                    <i class="bi bi-calendar3"></i> Filter Tanggal
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-4 col-12">
                        <label for="tanggal" class="form-label">Tanggal Spesifik</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ request('tanggal') }}">
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <span class="filter-or-badge">atau</span>
                    </div>
                    <div class="col-lg-3 col-md-3 col-5">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select class="form-select" id="bulan" name="bulan" {{ request('tanggal') ? 'disabled' : '' }}>
                            <option value="">Semua Bulan</option>
                            <option value="1" {{ request('bulan') == '1' ? 'selected' : '' }}>Januari</option>
                            <option value="2" {{ request('bulan') == '2' ? 'selected' : '' }}>Febuari</option>
                            <option value="3" {{ request('bulan') == '3' ? 'selected' : '' }}>Maret</option>
                            <option value="4" {{ request('bulan') == '4' ? 'selected' : '' }}>April</option>
                            <option value="5" {{ request('bulan') == '5' ? 'selected' : '' }}>Mei</option>
                            <option value="6" {{ request('bulan') == '6' ? 'selected' : '' }}>Juni</option>
                            <option value="7" {{ request('bulan') == '7' ? 'selected' : '' }}>Juli</option>
                            <option value="8" {{ request('bulan') == '8' ? 'selected' : '' }}>Agustus</option>
                            <option value="9" {{ request('bulan') == '9' ? 'selected' : '' }}>September</option>
                            <option value="10" {{ request('bulan') == '10' ? 'selected' : '' }}>Oktober</option>
                            <option value="11" {{ request('bulan') == '11' ? 'selected' : '' }}>November</option>
                            <option value="12" {{ request('bulan') == '12' ? 'selected' : '' }}>Desember</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-5">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select class="form-select" id="tahun" name="tahun" {{ request('tanggal') ? 'disabled' : '' }}>
                            <option value="">Semua Tahun</option>
                            @for($y = date('Y'); $y >= 2000; $y--)
                                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-2 col-12 ms-auto">
                        <a href="{{ route('applicants.index', request('archived') == '1' ? ['archived' => 1] : []) }}" class="btn btn-reset w-100">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Action Bar (Hidden by default) --}}
<div class="bulk-action-bar" id="bulkActionBar">
    <div class="bulk-action-info">
        <i class="bi bi-check-circle-fill"></i>
        <span><strong id="selectedCount">0</strong> item dipilih</span>
    </div>
    <div class="bulk-action-buttons">
        <button type="button" class="btn btn-outline-light btn-sm" id="selectAllBtn">
            <i class="bi bi-check-all me-1"></i><span class="btn-text">Pilih Semua</span>
        </button>
        <button type="button" class="btn btn-outline-light btn-sm" id="deselectAllBtn">
            <i class="bi bi-x-lg me-1"></i><span class="btn-text">Batal Pilih</span>
        </button>
        @if(request('archived') == '1')
        <button type="button" class="btn btn-success btn-sm" id="bulkUnarchiveBtn">
            <i class="bi bi-arrow-counterclockwise me-1"></i><span class="btn-text">Restore</span>
        </button>
        @else
        <button type="button" class="btn btn-warning btn-sm" id="bulkArchiveBtn">
            <i class="bi bi-archive me-1"></i><span class="btn-text">Arsipkan</span>
        </button>
        @endif
        <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn">
            <i class="bi bi-trash me-1"></i><span class="btn-text">Hapus</span>
        </button>
    </div>
</div>

{{-- Data Table --}}
<div class="card fade-in-up" style="animation-delay: 0.3s">
    <div class="card-body p-0">
        @if($applicants->count() > 0)
            {{-- Desktop Table View --}}
            <div class="d-none d-md-block">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px; min-width: 60px;">No</th>
                            <th>
                                <a href="{{ getSortUrl('nama_lengkap') }}" 
                                    class="text-decoration-none text-dark d-flex align-items-center justify-content-between w-100">
                                    <span>Nama Pelamar</span>
                                    {!! getSortIcon('nama_lengkap') !!}
                                </a>
                            </th>
                            <th>No. Telp</th>
                            <th class="d-none d-lg-table-cell">
                                <a href="{{ getSortUrl('tanggal_test') }}" 
                                    class="text-decoration-none text-dark d-flex align-items-center justify-content-between w-100">
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
                                        <div class="applicant-avatar selectable-avatar {{ $applicant->gender == 'Laki-laki' ? 'male' : 'female' }}" 
                                             data-id="{{ $applicant->id }}" title="Klik untuk memilih">
                                            <span class="avatar-initials">{{ strtoupper(substr($applicant->nama_lengkap, 0, 2)) }}</span>
                                            <span class="avatar-check"><i class="bi bi-check-lg"></i></span>
                                        </div>
                                        <div>
                                            <div class="applicant-name">{{ $applicant->nama_lengkap }}</div>
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
                                    {{ $applicant->tanggal_test ? $applicant->tanggal_test->format('d M Y') : '-' }}
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
                                <div class="applicant-card-meta">
                                    <span><i class="bi bi-geo-alt"></i> {{ $applicant->kota }}</span>
                                    <span><i class="bi bi-calendar3"></i> {{ $applicant->tanggal_test ? $applicant->tanggal_test->format('d M Y') : '-' }}</span>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const tipeSelect = document.getElementById('tipe');
    const statusSelect = document.getElementById('status');
    const tanggalInput = document.getElementById('tanggal');
    const bulanSelect = document.getElementById('bulan');
    const tahunSelect = document.getElementById('tahun');
    const filterForm = document.getElementById('filterForm');
    const searchLoading = document.getElementById('searchLoading');
    
    let debounceTimer;
    
    function submitFilter() {
        // Show loading indicator
        searchLoading.classList.remove('d-none');
        
        // Build URL with query parameters
        const params = new URLSearchParams();
        if (searchInput.value) params.set('search', searchInput.value);
        if (tipeSelect.value) params.set('tipe', tipeSelect.value);
        if (statusSelect.value) params.set('status', statusSelect.value);
        // Date filtering: tanggal spesifik OR bulan/tahun
        if (tanggalInput.value) {
            params.set('tanggal', tanggalInput.value);
        } else {
            if (bulanSelect.value) params.set('bulan', bulanSelect.value);
            if (tahunSelect.value) params.set('tahun', tahunSelect.value);
        }
        // Always keep archived=1 if in archive page
        if ("{{ request('archived') }}" == "1") {
            params.set('archived', '1');
        }
        // Navigate to filtered URL
        window.location.href = '{{ route("applicants.index") }}' + (params.toString() ? '?' + params.toString() : '');
    }
    
    // Toggle bulan/tahun disabled state based on tanggal
    function toggleDateFilters() {
        const hasTanggal = tanggalInput.value !== '';
        bulanSelect.disabled = hasTanggal;
        tahunSelect.disabled = hasTanggal;
        
        if (hasTanggal) {
            bulanSelect.value = '';
            tahunSelect.value = '';
        }
    }
    
    tanggalInput.addEventListener('change', function() {
        toggleDateFilters();
        submitFilter();
    });
    
    // Debounce search input (wait 400ms after user stops typing)
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(submitFilter, 400);
    });
    
    // Immediate filter on dropdown/date change
    tipeSelect.addEventListener('change', submitFilter);
    statusSelect.addEventListener('change', submitFilter);
    bulanSelect.addEventListener('change', submitFilter);
    tahunSelect.addEventListener('change', submitFilter);
    
    // Prevent form submission (we handle it via JS)
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitFilter();
    });

    // Real-time Clock
    function updateClock() {
        const now = new Date();
        
        // Format time HH:MM:SS
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeString = `${hours}:${minutes}:${seconds}`;
        
        const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const months = ['Januari','Febuari','Maret','April','Mei','Juni',
                        'Juli','Agustus','September','Oktober','November','Desember'];
        
        const dayName = days[now.getDay()];
        const date = now.getDate();
        const monthName = months[now.getMonth()];
        const year = now.getFullYear();
        const dateString = `${dayName}, ${date} ${monthName} ${year}`;
        
        document.getElementById('clockTime').textContent = timeString;
        document.getElementById('clockDate').textContent = dateString;
    }
    
    // Update clock immediately and every second
    updateClock();
    setInterval(updateClock, 1000);

    // ============ BULK SELECT & DELETE ============
    const STORAGE_KEY = 'applicant_selected_ids';
    const selectedIds = new Set();
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCountEl = document.getElementById('selectedCount');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkArchiveBtn = document.getElementById('bulkArchiveBtn');
    const bulkUnarchiveBtn = document.getElementById('bulkUnarchiveBtn');
    const archivedSelect = document.getElementById('archived');

    // Load saved selections from sessionStorage
    function loadSavedSelections() {
        const saved = sessionStorage.getItem(STORAGE_KEY);
        if (saved) {
            try {
                const ids = JSON.parse(saved);
                ids.forEach(id => selectedIds.add(id));
                updateSelectionUI();
            } catch (e) {
                sessionStorage.removeItem(STORAGE_KEY);
            }
        }
    }

    // Save selections to sessionStorage
    function saveSelections() {
        if (selectedIds.size > 0) {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(selectedIds)));
        } else {
            sessionStorage.removeItem(STORAGE_KEY);
        }
    }

    // Clear saved selections (call when navigating away from applicants list)
    function clearSavedSelections() {
        sessionStorage.removeItem(STORAGE_KEY);
        selectedIds.clear();
    }

    // Load selections on page load
    loadSavedSelections();

    // Add archived filter event listener
    if (archivedSelect) {
        archivedSelect.addEventListener('change', submitFilter);
    }

    // Function to update UI based on selection
    function updateSelectionUI() {
        const count = selectedIds.size;
        selectedCountEl.textContent = count;

        if (count > 0) {
            bulkActionBar.classList.add('show');
        } else {
            bulkActionBar.classList.remove('show');
        }

        // Update visual state for visible avatars
        document.querySelectorAll('.selectable-avatar').forEach(avatar => {
            const id = parseInt(avatar.dataset.id);
            const row = avatar.closest('.selectable-row') || avatar.closest('.selectable-card');
            if (selectedIds.has(id)) {
                avatar.classList.add('selected');
                row?.classList.add('selected');
            } else {
                avatar.classList.remove('selected');
                row?.classList.remove('selected');
            }
        });
    }

    // Toggle selection for an item
    function toggleSelection(id, element) {
        const row = element.closest('.selectable-row') || element.closest('.selectable-card');
        
        if (selectedIds.has(id)) {
            selectedIds.delete(id);
            row?.classList.remove('selected');
            element.classList.remove('selected');
        } else {
            selectedIds.add(id);
            row?.classList.add('selected');
            element.classList.add('selected');
        }
        saveSelections();
        updateSelectionUI();
    }

    // Handle avatar click for selection (Desktop Table)
    document.querySelectorAll('.selectable-avatar').forEach(avatar => {
        avatar.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = parseInt(this.dataset.id);
            toggleSelection(id, this);
        });
    });

    // Select All Button - Fetch ALL IDs from server
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            // Disable button while loading
            selectAllBtn.disabled = true;
            selectAllBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span><span class="btn-text">Memuat...</span>';

            // Build query params from current filters
            const params = new URLSearchParams(window.location.search);
            
            fetch('{{ route("applicants.getAllIds") }}?' + params.toString(), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Restore button
                selectAllBtn.disabled = false;
                selectAllBtn.innerHTML = '<i class="bi bi-check-all me-1"></i><span class="btn-text">Pilih Semua</span>';
                
                if (data.success) {
                    // Add all IDs to selectedIds
                    data.ids.forEach(id => selectedIds.add(id));
                    saveSelections();
                    updateSelectionUI();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Restore button
                selectAllBtn.disabled = false;
                selectAllBtn.innerHTML = '<i class="bi bi-check-all me-1"></i><span class="btn-text">Pilih Semua</span>';
            });
        });
    }

    // Deselect All Button
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            document.querySelectorAll('.selectable-avatar.selected').forEach(avatar => {
                avatar.classList.remove('selected');
                const row = avatar.closest('.selectable-row') || avatar.closest('.selectable-card');
                row?.classList.remove('selected');
            });
            clearSavedSelections();
            updateSelectionUI();
        });
    }

    // Bulk Delete Button
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            if (selectedIds.size === 0) return;

            Swal.fire({
                title: 'Hapus Data Terpilih?',
                html: `Anda akan menghapus <strong>${selectedIds.size}</strong> data pelamar.<br>Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Menghapus...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send delete request
                    fetch('{{ route("applicants.bulkDelete") }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ids: Array.from(selectedIds)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            clearSavedSelections();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message || 'Terjadi kesalahan saat menghapus data.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada server.'
                        });
                    });
                }
            });
        });
    }

    // Bulk Archive Button
    if (bulkArchiveBtn) {
        bulkArchiveBtn.addEventListener('click', function() {
            if (selectedIds.size === 0) return;

            Swal.fire({
                title: 'Arsipkan Data Terpilih?',
                html: `Anda akan mengarsipkan <strong>${selectedIds.size}</strong> data pelamar.<br>Data dapat dipulihkan dari arsip.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-archive me-1"></i>Ya, Arsipkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengarsipkan...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('{{ route("applicants.bulkArchive") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ids: Array.from(selectedIds)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            clearSavedSelections();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message || 'Terjadi kesalahan saat mengarsipkan data.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada server.'
                        });
                    });
                }
            });
        });
    }

    // Bulk Unarchive Button
    if (bulkUnarchiveBtn) {
        bulkUnarchiveBtn.addEventListener('click', function() {
            if (selectedIds.size === 0) return;

            Swal.fire({
                title: 'Pulihkan Data Terpilih?',
                html: `Anda akan memulihkan <strong>${selectedIds.size}</strong> data pelamar dari arsip.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i>Ya, Pulihkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memulihkan...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('{{ route("applicants.bulkUnarchive") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            ids: Array.from(selectedIds)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            clearSavedSelections();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message || 'Terjadi kesalahan saat memulihkan data.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada server.'
                        });
                    });
                }
            });
        });
    }
});
</script>
@endpush
