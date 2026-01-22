@extends('layouts.app')

@section('title', 'Import Data Pelamar')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Import Data Pelamar
                </h2>
                <p class="text-muted mb-0">Upload file Excel untuk import data pelamar secara massal</p>
            </div>
            <a href="{{ route('applicants.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

        <!-- {{-- Info Card --}}
        <div class="card border-info mb-4">
            <div class="card-body">
                <h5 class="card-title text-info">
                    <i class="bi bi-info-circle me-2"></i>Format File Excel
                </h5>
                <p class="card-text mb-2">File Excel harus memiliki kolom-kolom berikut (sesuai urutan):</p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-3">
                        <thead class="table-light">
                            <tr>
                                <th>Kolom Excel</th>
                                <th>Keterangan</th>
                                <th>Contoh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>First Name</code></td>
                                <td>Nama depan</td>
                                <td>John</td>
                            </tr>
                            <tr>
                                <td><code>Last Name</code></td>
                                <td>Nama belakang</td>
                                <td>Doe</td>
                            </tr>
                            <tr>
                                <td><code>Address 1</code></td>
                                <td>Alamat lengkap</td>
                                <td>Jl. Contoh No. 123</td>
                            </tr>
                            <tr>
                                <td><code>City</code></td>
                                <td>Kota</td>
                                <td>Surabaya</td>
                            </tr>
                            <tr>
                                <td><code>State</code></td>
                                <td>Provinsi</td>
                                <td>Jawa Timur</td>
                            </tr>
                            <tr>
                                <td><code>Home Phone 1</code></td>
                                <td>No. HP utama</td>
                                <td>081234567890</td>
                            </tr>
                            <tr>
                                <td><code>Home Phone 2</code></td>
                                <td>No. HP alternatif (opsional)</td>
                                <td>082345678901</td>
                            </tr>
                            <tr>
                                <td><code>Apply Date</code></td>
                                <td>Tanggal lamaran</td>
                                <td>2026-01-12 atau 12/01/2026</td>
                            </tr>
                            <tr>
                                <td><code>Change Date</code></td>
                                <td>Tanggal perubahan (akan masuk catatan)</td>
                                <td>2026-01-13</td>
                            </tr>
                            <tr>
                                <td><code>EEO Age</code></td>
                                <td>Umur atau range umur</td>
                                <td>25 atau 20-25</td>
                            </tr>
                            <tr>
                                <td><code>Gender GB</code></td>
                                <td>Jenis kelamin (M/F/Male/Female)</td>
                                <td>M atau F</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('import.template') }}" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-download me-1"></i>Download Template CSV
                </a>
            </div>
        </div> -->

        {{-- Upload Form --}}
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-upload me-2"></i>Upload File
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="excel_file" class="form-label fw-bold">Pilih File Excel/CSV</label>
                        <input type="file" 
                               class="form-control form-control-lg @error('excel_file') is-invalid @enderror" 
                               id="excel_file" 
                               name="excel_file"
                               accept=".xlsx,.xls,.csv"
                               required>
                        @error('excel_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Format yang didukung: .xlsx, .xls, .csv (Maksimal 10MB)
                        </div>
                    </div>

                    {{-- File Preview --}}
                    <div id="filePreview" class="mb-4 d-none">
                        <div class="alert alert-secondary d-flex align-items-center">
                            <i class="bi bi-file-earmark-spreadsheet fs-3 me-3"></i>
                            <div>
                                <strong id="fileName">-</strong>
                                <br>
                                <small class="text-muted" id="fileSize">-</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" id="removeFile">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Pastikan format kolom Excel sesuai dengan template</li>
                            <li>Baris pertama harus berisi header/nama kolom</li>
                            <li>Data yang tidak memiliki nama akan dilewati</li>
                            <li>Kolom yang tidak ada di Excel akan diisi nilai default</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Import Errors --}}
        @if(session('import_errors'))
        <div class="card mt-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>Beberapa baris tidak dapat diimport:
                </h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    @foreach(session('import_errors') as $error)
                        <li class="text-danger">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('excel_file');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');
    const importForm = document.getElementById('importForm');
    const submitBtn = document.getElementById('submitBtn');

    // File input change
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.classList.remove('d-none');
        } else {
            filePreview.classList.add('d-none');
        }
    });

    // Remove file
    removeFile.addEventListener('click', function() {
        fileInput.value = '';
        filePreview.classList.add('d-none');
    });

    // Form submit with loading
    importForm.addEventListener('submit', function(e) {
        if (!fileInput.files || !fileInput.files[0]) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'File Belum Dipilih',
                text: 'Silakan pilih file Excel/CSV terlebih dahulu',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        // Confirm before import
        e.preventDefault();
        
        Swal.fire({
            title: 'Konfirmasi Import',
            html: `Anda akan mengimport data dari file:<br><strong>${fileInput.files[0].name}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-lg"></i> Ya, Import',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Mengimport Data...',
                    html: 'Mohon tunggu, sedang memproses file Excel',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form
                importForm.submit();
            }
        });
    });

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endpush
