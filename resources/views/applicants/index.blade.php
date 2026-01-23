@extends('layouts.app')

@section('title', request('archived') == '1' ? 'Arsip Pelamar - Recruitment App' : 'Daftar Pelamar - Recruitment App')

@section('content')

{{-- Welcome Card --}}
<div class="welcome-card fade-in-up">
    <div class="d-flex align-items-center justify-content-between">
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
            <i class="bi bi-archive-fill text-warning"></i> Arsip Pelamar
        @else
            <i class="bi bi-people-fill"></i> Daftar Pelamar
        @endif
    </h2>
    <div class="d-flex gap-2">
        @if(request('archived') == '1')
            <a href="{{ route('applicants.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard
            </a>
        @else
            <a href="{{ route('applicants.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Tambah Pelamar
            </a> 
        @endif
    </div>
</div>

{{-- Filter & Search --}}
<div class="card filter-card mb-4 fade-in-up" style="animation-delay: 0.2s">
    <div class="card-body">
        <form id="filterForm" action="{{ route('applicants.index') }}" method="GET">
            <div class="filter-section">
                <div class="filter-section-title">
                    <i class="bi bi-funnel-fill"></i> Filter Pencarian
                </div>
                <div class="row g-3">
                    {{-- SEARCH INPUT & CHECKBOX --}}
                    <div class="col-lg-6 col-md-12">
                        <label for="search" class="form-label">
                            <i class="bi bi-search me-1"></i>Cari Pelamar
                        </label>
                        <div class="input-group input-group-search mb-2">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="Ketik kata kunci..."
                                   value="{{ request('search') }}">
                            <span class="input-group-text d-none" id="searchLoading">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </span>
                        </div>

                        @php
                            $sf = request('search_fields');
                            $isAll = is_null($sf); 
                        @endphp

                        {{-- Checkbox Search Fields --}}
                        <div class="d-flex gap-3 align-items-center flex-wrap">
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input search-field-cb" type="checkbox" id="sf_nama" name="search_fields[]" value="nama_lengkap" {{ $isAll || in_array('nama_lengkap', $sf ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="sf_nama">Nama</label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input search-field-cb" type="checkbox" id="sf_ktp" name="search_fields[]" value="no_ktp" {{ $isAll || in_array('no_ktp', $sf ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="sf_ktp">No. KTP</label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input search-field-cb" type="checkbox" id="sf_hp" name="search_fields[]" value="no_hp_1" {{ $isAll || in_array('no_hp_1', $sf ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="sf_hp">No. HP</label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input search-field-cb" type="checkbox" id="sf_alamat" name="search_fields[]" value="alamat" {{ $isAll || in_array('alamat', $sf ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="sf_alamat">Alamat</label>
                            </div>
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input search-field-cb" type="checkbox" id="sf_id" name="search_fields[]" value="applicant_number" {{ $isAll || in_array('applicant_number', $sf ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="sf_id">ID Pelamar</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-6">
                        <label for="tipe" class="form-label"><i class="bi bi-person-badge me-1"></i>Tipe Pelamar</label>
                        <select class="form-select" id="tipe" name="tipe">
                            <option value="">Semua Tipe</option>
                            <option value="guru" {{ request('tipe') == 'guru' ? 'selected' : '' }}>👨‍🏫 Guru</option>
                            <option value="pkl" {{ request('tipe') == 'pkl' ? 'selected' : '' }}>🎓PKL</option>
                            <option value="reguler" {{ request('tipe') == 'reguler' ? 'selected' : '' }}>👤 Reguler</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <label for="status" class="form-label"><i class="bi bi-flag me-1"></i>Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                            <option value="tested" {{ request('status') == 'tested' ? 'selected' : '' }}>✅ Sudah Test</option>
                            <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>🎉 Diterima</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>🚫 Ditolak</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr class="filter-divider">
            
            <div class="filter-section">
                <div class="filter-section-title"><i class="bi bi-calendar3"></i> Filter Tanggal</div>
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-4 col-12">
                        <label for="tanggal" class="form-label">Tanggal Spesifik</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ request('tanggal') }}">
                    </div>
                    <div class="col-auto d-flex align-items-center"><span class="filter-or-badge">atau</span></div>
                    <div class="col-lg-3 col-md-3 col-5">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select class="form-select" id="bulan" name="bulan" {{ request('tanggal') ? 'disabled' : '' }}>
                            <option value="">Semua Bulan</option>
                            <option value="1" {{ request('bulan') == '1' ? 'selected' : '' }}>Januari</option>
                            <option value="2" {{ request('bulan') == '2' ? 'selected' : '' }}>Februari</option>
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
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Action Bar --}}
<div class="bulk-action-bar" id="bulkActionBar">
    <div class="bulk-action-info">
        <i class="bi bi-check-circle-fill"></i>
        <span><strong id="selectedCount">0</strong> item dipilih</span>
    </div>
    <div class="bulk-action-buttons">
        <button type="button" class="btn btn-outline-light btn-sm" id="selectAllBtn"><i class="bi bi-check-all me-1"></i>Pilih Semua</button>
        <button type="button" class="btn btn-outline-light btn-sm" id="deselectAllBtn"><i class="bi bi-x-lg me-1"></i>Batal Pilih</button>
        @if(request('archived') == '1')
            <button type="button" class="btn btn-success btn-sm" id="bulkUnarchiveBtn"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
        @else
            <button type="button" class="btn btn-warning btn-sm" id="bulkArchiveBtn"><i class="bi bi-archive me-1"></i>Arsipkan</button>
        @endif
        <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn"><i class="bi bi-trash me-1"></i>Hapus</button>
    </div>
</div>

{{-- Data Table Container --}}
<div class="card fade-in-up" style="animation-delay: 0.3s">
    {{-- PERBAIKAN UTAMA: Tambahkan ID ini agar AJAX tahu dimana harus mengganti tabel --}}
    <div class="card-body p-0" id="applicant-list-container">
        @include('applicants._list')
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Setup Selektor
    const searchInput = document.getElementById('search');
    const tipeSelect = document.getElementById('tipe');
    const statusSelect = document.getElementById('status');
    const tanggalInput = document.getElementById('tanggal');
    const bulanSelect = document.getElementById('bulan');
    const tahunSelect = document.getElementById('tahun');
    const searchLoading = document.getElementById('searchLoading');
    
    // Setup Bulk Action
    const STORAGE_KEY = 'applicant_selected_ids';
    const selectedIds = new Set();
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCountEl = document.getElementById('selectedCount');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkArchiveBtn = document.getElementById('bulkArchiveBtn');
    const bulkUnarchiveBtn = document.getElementById('bulkUnarchiveBtn');

    // --- FUNGSI BULK SELECTION ---
    function loadSavedSelections() {
        const saved = sessionStorage.getItem(STORAGE_KEY);
        if (saved) {
            try {
                JSON.parse(saved).forEach(id => selectedIds.add(id));
            } catch (e) { sessionStorage.removeItem(STORAGE_KEY); }
        }
        updateSelectionUI();
    }

    function saveSelections() {
        if (selectedIds.size > 0) sessionStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(selectedIds)));
        else sessionStorage.removeItem(STORAGE_KEY);
    }

    function updateSelectionUI() {
        if(selectedCountEl) selectedCountEl.textContent = selectedIds.size;
        
        if (bulkActionBar) {
            selectedIds.size > 0 ? bulkActionBar.classList.add('show') : bulkActionBar.classList.remove('show');
        }

        // Highlight baris
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

    // Toggle Selection (Global agar aman)
    window.toggleSelection = function(id, element) {
        if (selectedIds.has(id)) selectedIds.delete(id);
        else selectedIds.add(id);
        saveSelections();
        updateSelectionUI();
    };

    function clearSelections() {
        selectedIds.clear();
        saveSelections();
        updateSelectionUI();
    }

    // --- FUNGSI AJAX FILTER ---
    window.submitFilter = function(pageUrl = null) {
        if(searchLoading) searchLoading.classList.remove('d-none');
        let url = pageUrl ? pageUrl : '{{ route("applicants.index") }}';
        
        const params = new URLSearchParams(pageUrl ? (new URL(pageUrl)).search : window.location.search);
        
        // Update Params
        if (searchInput && searchInput.value) params.set('search', searchInput.value); else params.delete('search');
        if (tipeSelect && tipeSelect.value) params.set('tipe', tipeSelect.value); else params.delete('tipe');
        if (statusSelect && statusSelect.value) params.set('status', statusSelect.value); else params.delete('status');

        // Logic Checkbox
        const keys = Array.from(params.keys());
        keys.forEach(k => { if(k.includes('search_fields')) params.delete(k); });
        
        const checkboxes = document.querySelectorAll('.search-field-cb:checked');
        const totalCb = document.querySelectorAll('.search-field-cb').length;
        if (checkboxes.length > 0 && checkboxes.length < totalCb) {
            checkboxes.forEach(cb => params.append('search_fields[]', cb.value));
        }

        // Logic Tanggal
        if (tanggalInput && tanggalInput.value) {
            params.set('tanggal', tanggalInput.value);
            params.delete('bulan'); params.delete('tahun');
        } else {
            params.delete('tanggal');
            if (bulanSelect && bulanSelect.value) params.set('bulan', bulanSelect.value); else params.delete('bulan');
            if (tahunSelect && tahunSelect.value) params.set('tahun', tahunSelect.value); else params.delete('tahun');
        }
        
        if ("{{ request('archived') }}" == "1") params.set('archived', '1');

        const finalUrl = `${url.split('?')[0]}?${params.toString()}`;

        fetch(finalUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            const container = document.getElementById('applicant-list-container');
            if (container) {
                container.innerHTML = html;
                window.history.pushState(null, '', finalUrl);
                reinitListeners(); // Pasang ulang listener
            }
        })
        .catch(err => console.error(err))
        .finally(() => { if(searchLoading) searchLoading.classList.add('d-none'); });
    }

    // --- RE-INIT LISTENERS (JANTUNG INTEGRASI) ---
    function reinitListeners() {
        // 1. Pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                submitFilter(this.href);
            });
        });

        // 2. Avatar Click - Clone Node untuk MENCEGAH LISTENER GANDA
        document.querySelectorAll('.selectable-avatar').forEach(avatar => {
            const newAvatar = avatar.cloneNode(true); 
            avatar.parentNode.replaceChild(newAvatar, avatar);
            
            newAvatar.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSelection(parseInt(this.dataset.id), this);
            });
        });

        // 3. Restore UI
        updateSelectionUI();
    }

    // --- SETUP LISTENER AWAL ---
    let debounceTimer;
    if(searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => submitFilter(), 400);
        });
        searchInput.addEventListener('keydown', function(e) {
            if(e.key === 'Enter') {
                submitFilter();
            }
        });
    }

    // Trigger submitFilter jika halaman di-load dari cache (back/forward)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            submitFilter();
        }
    });

    [tipeSelect, statusSelect, bulanSelect, tahunSelect, tanggalInput].forEach(el => {
        if(el) el.addEventListener('change', () => submitFilter());
    });

    document.querySelectorAll('.search-field-cb').forEach(cb => {
        cb.addEventListener('change', () => submitFilter());
    });

    // Handle Bulk Buttons
    function handleBulkAction(url, method, actionName, colorBtn) {
        if (selectedIds.size === 0) return;
        Swal.fire({
            title: `${actionName} ${selectedIds.size} Data?`, text: "Tindakan ini akan memproses data yang dipilih.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: method === 'DELETE' ? '#dc3545' : '#ffc107',
            confirmButtonText: `Ya, ${actionName}!`, cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                fetch(url, {
                    method: method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ ids: Array.from(selectedIds) })
                }).then(res => res.json()).then(data => {
                    if(data.success) {
                        clearSelections();
                        Swal.fire('Berhasil', data.message, 'success');
                        submitFilter();
                    } else Swal.fire('Gagal', data.message, 'error');
                }).catch(err => Swal.fire('Error', 'Kesalahan server', 'error'));
            }
        });
    }

    if (bulkDeleteBtn) bulkDeleteBtn.addEventListener('click', () => handleBulkAction('{{ route("applicants.bulkDelete") }}', 'DELETE', 'Hapus', 'danger'));
    if (bulkArchiveBtn) bulkArchiveBtn.addEventListener('click', () => handleBulkAction('{{ route("applicants.bulkArchive") }}', 'POST', 'Arsipkan', 'warning'));
    if (bulkUnarchiveBtn) bulkUnarchiveBtn.addEventListener('click', () => handleBulkAction('{{ route("applicants.bulkUnarchive") }}', 'POST', 'Pulihkan', 'success'));
    if (deselectAllBtn) deselectAllBtn.addEventListener('click', clearSelections);

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            selectAllBtn.disabled = true;
            selectAllBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memuat...';
            const params = new URLSearchParams(window.location.search);
            fetch('{{ route("applicants.getAllIds") }}?' + params.toString())
            .then(res => res.json())
            .then(data => {
                selectAllBtn.disabled = false;
                selectAllBtn.innerHTML = '<i class="bi bi-check-all me-1"></i>Pilih Semua';
                if(data.success) {
                    data.ids.forEach(id => selectedIds.add(id));
                    saveSelections();
                    updateSelectionUI();
                }
            }).catch(e => {
                selectAllBtn.disabled = false;
                selectAllBtn.innerHTML = '<i class="bi bi-check-all me-1"></i>Pilih Semua';
            });
        });
    }

    // Toggle Date Filter logic
    function toggleDateFilters() {
        if (!tanggalInput) return;
        const hasTanggal = tanggalInput.value !== '';
        if(bulanSelect) bulanSelect.disabled = hasTanggal;
        if(tahunSelect) tahunSelect.disabled = hasTanggal;
        if (hasTanggal) {
            if(bulanSelect) bulanSelect.value = '';
            if(tahunSelect) tahunSelect.value = '';
        }
    }
    if(tanggalInput) {
        toggleDateFilters();
        tanggalInput.addEventListener('change', toggleDateFilters);
    }

    // Clock Logic
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
        const dateString = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        
        const timeEl = document.getElementById('clockTime');
        const dateEl = document.getElementById('clockDate');
        
        if(timeEl) timeEl.textContent = timeString;
        if(dateEl) dateEl.textContent = dateString;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Init App
    loadSavedSelections();
    reinitListeners();
});
</script>
@endpush