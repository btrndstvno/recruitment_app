@extends('layouts.app')

@section('title', 'Edit Pelamar - ' . $applicant->nama_lengkap)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square me-2"></i>Edit Data Pelamar</h2>
    <a href="{{ route('applicants.show', $applicant) }}?{{ http_build_query(request()->query()) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('applicants.update', $applicant) }}" method="POST" id="applicantForm" data-loading="true">
            @csrf
            @method('PUT')

            {{-- Data Pribadi --}}
            <h5 class="border-bottom pb-2 mb-3 text-primary">
                <i class="bi bi-person me-2"></i>Data Pribadi
            </h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="color_code" class="form-label">Color Code</label>
                    <select class="form-select @error('color_code') is-invalid @enderror" id="color_code" name="color_code">
                        <option value="abu-abu" {{ old('color_code', $applicant->color_code ?? 'abu-abu') == 'abu-abu' ? 'selected' : '' }}>Abu-abu (default)</option>
                        <option value="merah" style="color:#000;" {{ old('color_code', $applicant->color_code) == 'merah' ? 'selected' : '' }}>🟥Merah</option>
                        <option value="kuning" style="color:#000;" {{ old('color_code', $applicant->color_code) == 'kuning' ? 'selected' : '' }}>🟨Kuning</option>
                        <option value="biru" style="color:#000;" {{ old('color_code', $applicant->color_code) == 'biru' ? 'selected' : '' }}>🟦Biru</option>
                        <option value="hijau" style="color:#000;" {{ old('color_code', $applicant->color_code) == 'hijau' ? 'selected' : '' }}>🟩Hijau</option>
                        <option value="hitam" style="color:#000;" {{ old('color_code', $applicant->color_code) == 'hitam' ? 'selected' : '' }}>⬛Hitam</option>
                        
                    </select>
                    @error('color_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" 
                           id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $applicant->nama_lengkap) }}" required>
                    @error('nama_lengkap')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="no_ktp" class="form-label">No. KTP <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('no_ktp') is-invalid @enderror" 
                           id="no_ktp" name="no_ktp" value="{{ old('no_ktp', $applicant->no_ktp) }}" required>
                    @error('no_ktp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="alamat" class="form-label">Alamat Domisili<span class="text-danger">*</span></label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror" 
                              id="alamat" name="alamat" rows="2" required>{{ old('alamat', $applicant->alamat) }}</textarea>
                    @error('alamat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="kota" class="form-label">Kota <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('kota') is-invalid @enderror" 
                           id="kota" name="kota" value="{{ old('kota', $applicant->kota) }}" required>
                    @error('kota')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="provinsi" class="form-label">Provinsi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('provinsi') is-invalid @enderror" 
                           id="provinsi" name="provinsi" value="{{ old('provinsi', $applicant->provinsi) }}" required>
                    @error('provinsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="no_hp_1" class="form-label">No. HP 1 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('no_hp_1') is-invalid @enderror" 
                           id="no_hp_1" name="no_hp_1" value="{{ old('no_hp_1', $applicant->no_hp_1) }}" required>
                    @error('no_hp_1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="no_hp_2" class="form-label">No. HP 2</label>
                    <input type="text" class="form-control @error('no_hp_2') is-invalid @enderror" 
                           id="no_hp_2" name="no_hp_2" value="{{ old('no_hp_2', $applicant->no_hp_2) }}">
                    @error('no_hp_2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="tempat_lahir" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" 
                           id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $applicant->tempat_lahir) }}" required>
                    @error('tempat_lahir')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                           id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $applicant->tanggal_lahir->format('Y-m-d')) }}" required>
                    @error('tanggal_lahir')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="umur" class="form-label">Umur <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('umur') is-invalid @enderror" 
                           id="umur" name="umur" value="{{ old('umur', $applicant->umur) }}" min="15" max="100" required>
                    @error('umur')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="gender" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                        <option value="">-- Pilih --</option>
                        <option value="Laki-laki" {{ old('gender', $applicant->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('gender', $applicant->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tanggal_lamaran" class="form-label">Tanggal Melamar <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_lamaran') is-invalid @enderror" 
                           id="tanggal_lamaran" name="tanggal_lamaran" value="{{ old('tanggal_lamaran', $applicant->tanggal_lamaran->format('Y-m-d')) }}" required>
                    @error('tanggal_lamaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tanggal_test" class="form-label">Tanggal Test</label>
                    <input type="date" class="form-control @error('tanggal_test') is-invalid @enderror" 
                           id="tanggal_test" name="tanggal_test" value="{{ old('tanggal_test', $applicant->tanggal_test ? $applicant->tanggal_test->format('Y-m-d') : '') }}">
                    @error('tanggal_test')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="pending"  {{ old('status', $applicant->status) == 'pending'  ? 'selected' : '' }}>Pending</option>
                        <option value="tested"   {{ old('status', $applicant->status) == 'tested'   ? 'selected' : '' }}>Sudah Test</option>
                        <option value="accepted" {{ old('status', $applicant->status) == 'accepted' ? 'selected' : '' }}>Diterima</option>
                        <option value="rejected" {{ old('status', $applicant->status) == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Data Pendidikan --}}
            <h5 class="border-bottom pb-2 mb-3 mt-4 text-primary">
                <i class="bi bi-mortarboard me-2"></i>Data Pendidikan
            </h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama_sekolah" class="form-label">Nama Sekolah/Universitas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_sekolah') is-invalid @enderror" 
                           id="nama_sekolah" name="nama_sekolah" value="{{ old('nama_sekolah', $applicant->nama_sekolah) }}" required>
                    @error('nama_sekolah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="jurusan" class="form-label">Jurusan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('jurusan') is-invalid @enderror" 
                           id="jurusan" name="jurusan" value="{{ old('jurusan', $applicant->jurusan) }}" required>
                    @error('jurusan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tahun_lulus" class="form-label">Tahun Lulus/Perkiraan Lulus <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('tahun_lulus') is-invalid @enderror" 
                           id="tahun_lulus" name="tahun_lulus" value="{{ old('tahun_lulus', $applicant->tahun_lulus) }}" 
                           min="1950" required>
                    <small class="text-muted">Untuk PKL/magang yang belum lulus, isi perkiraan tahun lulus</small>
                    @error('tahun_lulus')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="ipk" class="form-label">IPK (jika S1)</label>
                    <input type="number" step="0.01" class="form-control @error('ipk') is-invalid @enderror" 
                           id="ipk" name="ipk" value="{{ old('ipk', $applicant->ipk) }}" min="0" max="5">
                    @error('ipk')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Pilihan Tambahan --}}
            <h5 class="border-bottom pb-2 mb-3 mt-4 text-primary">
                <i class="bi bi-plus-circle me-2"></i>Pilihan Tambahan
            </h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_guru" name="is_guru" value="1" 
                               {{ old('is_guru', $applicant->is_guru) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_guru">
                            <i class="bi bi-person-workspace me-1"></i>Guru
                        </label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_pkl" name="is_pkl" value="1" 
                               {{ old('is_pkl', $applicant->is_pkl) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_pkl">
                            <i class="bi bi-briefcase me-1"></i>PKL (Praktik Kerja Lapangan)
                        </label>
                    </div>
                </div>
            </div>

            {{-- Data PKL (Hidden by default) --}}
            <div id="pklFields" class="border rounded p-3 mb-3 bg-light" style="display: none;">
                <h6 class="text-secondary mb-3"><i class="bi bi-briefcase me-2"></i>Data PKL</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pkl_awal" class="form-label">Tanggal Mulai PKL</label>
                        <input type="date" class="form-control @error('pkl_awal') is-invalid @enderror" 
                               id="pkl_awal" name="pkl_awal" value="{{ old('pkl_awal', $applicant->pkl_awal?->format('Y-m-d')) }}">
                        @error('pkl_awal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pkl_akhir" class="form-label">Tanggal Selesai PKL</label>
                        <input type="date" class="form-control @error('pkl_akhir') is-invalid @enderror" 
                               id="pkl_akhir" name="pkl_akhir" value="{{ old('pkl_akhir', $applicant->pkl_akhir?->format('Y-m-d')) }}">
                        @error('pkl_akhir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pkl_asal_sekolah" class="form-label">Asal Sekolah PKL</label>
                        <input type="text" class="form-control @error('pkl_asal_sekolah') is-invalid @enderror" 
                               id="pkl_asal_sekolah" name="pkl_asal_sekolah" value="{{ old('pkl_asal_sekolah', $applicant->pkl_asal_sekolah) }}">
                        @error('pkl_asal_sekolah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="pkl_jurusan" class="form-label">Jurusan PKL</label>
                        <input type="text" class="form-control @error('pkl_jurusan') is-invalid @enderror" 
                               id="pkl_jurusan" name="pkl_jurusan" value="{{ old('pkl_jurusan', $applicant->pkl_jurusan) }}">
                        @error('pkl_jurusan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="pkl_tempat" class="form-label">Tempat PKL</label>
                        <input type="text" class="form-control @error('pkl_tempat') is-invalid @enderror" 
                               id="pkl_tempat" name="pkl_tempat" value="{{ old('pkl_tempat', $applicant->pkl_tempat) }}">
                        @error('pkl_tempat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Catatan --}}
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="catatan" class="form-label">Catatan Tambahan</label>
                    <textarea class="form-control @error('catatan') is-invalid @enderror" 
                              id="catatan" name="catatan" rows="3">{{ old('catatan', $applicant->catatan) }}</textarea>
                    @error('catatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('applicants.show', $applicant) }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isGuruCheckbox = document.getElementById('is_guru');
        const isPklCheckbox = document.getElementById('is_pkl');
        const pklFields = document.getElementById('pklFields');

        function togglePklFields() {
            pklFields.style.display = isPklCheckbox.checked ? 'block' : 'none';
        }

        // Mutual exclusive: Guru dan PKL hanya bisa pilih salah satu
        isGuruCheckbox.addEventListener('change', function() {
            if (this.checked) {
                isPklCheckbox.checked = false;
                togglePklFields();
            }
        });

        isPklCheckbox.addEventListener('change', function() {
            if (this.checked) {
                isGuruCheckbox.checked = false;
            }
            togglePklFields();
        });

        togglePklFields(); // Initial state

        // Auto calculate age from birth date
        const tanggalLahir = document.getElementById('tanggal_lahir');
        const umur = document.getElementById('umur');

        tanggalLahir.addEventListener('change', function() {
            if (this.value) {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                umur.value = age;
            }
        });
    });
</script>
@endpush
