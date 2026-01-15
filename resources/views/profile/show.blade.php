@extends('layouts.app')

@section('title', 'Profil - Recruitment App')

@section('content')
{{-- Page Header --}}
<div class="page-header fade-in-up">
    <h2 class="page-title">
        <i class="bi bi-person-circle"></i>
        Profil Saya
    </h2>
    <a href="{{ route('applicants.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Profile Card --}}
        <div class="section-card fade-in-up mb-4" style="animation-delay: 0.1s">
            <div class="section-header">
                <div class="section-header-icon primary">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h5 class="section-title">Informasi Profil</h5>
            </div>
            <div class="section-body">
                <form method="POST" action="{{ route('profile.update.name') }}" data-loading="true">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Nama Lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">Email</label>
                        <input type="email" class="form-control bg-light" id="email" value="{{ $user->email }}" disabled readonly>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Email tidak dapat diubah.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        {{-- Password Card --}}
        <div class="section-card fade-in-up" id="password" style="animation-delay: 0.2s">
            <div class="section-header">
                <div class="section-header-icon warning">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h5 class="section-title">Ubah Password</h5>
            </div>
            <div class="section-body">
                <form method="POST" action="{{ route('profile.update.password') }}" data-loading="true">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-medium">Password Saat Ini</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Minimal 8 karakter.</small>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-medium">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-check me-1"></i>Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
