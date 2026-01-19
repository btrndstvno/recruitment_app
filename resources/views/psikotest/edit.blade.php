@extends('layouts.app')

@section('title', 'Edit Laporan Psikotest - ' . $applicant->nama_lengkap)

@push('styles')
<style>
    .aspek-table th, .aspek-table td {
        text-align: center;
        vertical-align: middle;
    }
    .aspek-table td:first-child {
        text-align: left;
    }
    .aspek-table td:not(:first-child) {
        cursor: pointer;
    }
    .aspek-table td:not(:first-child):hover {
        background-color: #e9ecef;
    }
    .score-input {
        width: 50px;
        text-align: center;
    }
    .section-score {
        background-color: #e9ecef;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square me-2"></i>Edit Laporan Psikotest</h2>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-secondary no-print">{{ $reportType }} Pertanyaan</span>
        <a href="{{ route('applicants.show', $applicant) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header text-center">
        LAPORAN PSIKOTEST
    </div>
    <div class="card-body">
        {{-- Header Info --}}
        <div class="row mb-4">
            <div class="col-md-8">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th width="30%">Nama</th>
                        <td>: {{ $applicant->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>: {{ $applicant->alamat }}</td>
                    </tr>
                    <tr>
                        <th>No. Handphone</th>
                        <td>: {{ $applicant->no_hp_1 }}</td>
                    </tr>
                    <tr>
                        <th>Tempat / Tanggal Lahir</th>
                        <td>: {{ $applicant->ttl }}</td>
                    </tr>
                    <tr>
                        <th>Pendidikan</th>
                        <td>: {{ $applicant->pendidikan }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <form action="{{ route('psikotest.update', $applicant) }}" method="POST" data-loading="true">
            @csrf
            @method('PUT')

            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="tanggal_test" class="form-label">Tanggal Test <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('tanggal_test') is-invalid @enderror" 
                           id="tanggal_test" name="tanggal_test" 
                           value="{{ old('tanggal_test', $report->tanggal_test->format('Y-m-d')) }}" required>
                    @error('tanggal_test')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- IQ Section --}}
            <h5 class="border-bottom pb-2 mb-3 text-primary">
                <i class="bi bi-lightbulb me-2"></i>Intelligence Quotient (IQ)
            </h5>
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="iq_score" class="form-label">Skor IQ</label>
                    <input type="number" class="form-control @error('iq_score') is-invalid @enderror" 
                           id="iq_score" name="iq_score" value="{{ old('iq_score', $report->iq_score) }}" min="50" max="200">
                    @error('iq_score')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-5">
                    <label for="iq_category" class="form-label">Kategori</label>
                    <select class="form-select @error('iq_category') is-invalid @enderror" id="iq_category" name="iq_category">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="borderline" {{ old('iq_category', $report->iq_category) == 'borderline' ? 'selected' : '' }}>Borderline (70-79)</option>
                        <option value="dibawah_rata_rata" {{ old('iq_category', $report->iq_category) == 'dibawah_rata_rata' ? 'selected' : '' }}>Dibawah Rata-Rata (80-89)</option>
                        <option value="rata_rata" {{ old('iq_category', $report->iq_category) == 'rata_rata' ? 'selected' : '' }}>Rata-Rata (90-109)</option>
                        <option value="diatas_rata_rata" {{ old('iq_category', $report->iq_category) == 'diatas_rata_rata' ? 'selected' : '' }}>Diatas Rata-Rata (110-119)</option>
                        <option value="superior" {{ old('iq_category', $report->iq_category) == 'superior' ? 'selected' : '' }}>Superior (120-139)</option>
                        <option value="very_superior" {{ old('iq_category', $report->iq_category) == 'very_superior' ? 'selected' : '' }}>Very Superior (140-169)</option>
                    </select>
                    @error('iq_category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Aspek Psikologis Table --}}
            <h5 class="border-bottom pb-2 mb-3 text-primary">
                <i class="bi bi-table me-2"></i>Aspek Psikologis
            </h5>

            <div class="alert alert-info">
                <strong>Keterangan Skala:</strong> A = 1 = Kurang | B = 2 = Hampir Cukup | C = 3 = Cukup | D = 4 = Cukup Baik | E = 5 = Baik | N/A = Tidak Ada
            </div>

            <div class="table-responsive">
                <table class="table table-bordered aspek-table">
                    <thead class="table-light">
                        <tr>
                            <th width="50%">ASPEK PSIKOLOGIS</th>
                            <th width="8%">A<br><small>1</small></th>
                            <th width="8%">B<br><small>2</small></th>
                            <th width="8%">C<br><small>3</small></th>
                            <th width="8%">D<br><small>4</small></th>
                            <th width="8%">E<br><small>5</small></th>
                            <th width="10%">N/A</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- A. Kemampuan Intelektual Umum --}}
                        <tr class="table-secondary">
                            <td colspan="7"><strong>A. Kemampuan Intelektual Umum</strong></td>
                        </tr>
                        @foreach(\App\Models\PsikotestReport::getAspekAFields() as $field => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            @for($i = 1; $i <= 5; $i++)
                            <td>
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="radio" name="{{ $field }}" value="{{ $i }}" 
                                           {{ old($field, $report->$field) == $i ? 'checked' : '' }}>
                                </div>
                            </td>
                            @endfor
                            <td>
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="radio" name="{{ $field }}" value="" 
                                           {{ old($field, $report->$field) === null ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="section-score">
                            <td colspan="6" class="text-end">XA (Rata-rata Aspek A)</td>
                            <td class="text-center" id="xa_display">{{ $report->xa_score ?? '-' }}</td>
                        </tr>

                        {{-- B. Kemampuan Khusus --}}
                        <tr class="table-secondary">
                            <td colspan="7"><strong>B. Kemampuan Khusus</strong></td>
                        </tr>
                        @php
                            $aspekBFields = $reportType == '38' 
                                ? \App\Models\PsikotestReport::getAspekBFields38() 
                                : \App\Models\PsikotestReport::getAspekBFields();
                        @endphp
                        @foreach($aspekBFields as $field => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            @for($i = 1; $i <= 5; $i++)
                            <td>
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="radio" name="{{ $field }}" value="{{ $i }}" 
                                           {{ old($field, $report->$field) == $i ? 'checked' : '' }}>
                                </div>
                            </td>
                            @endfor
                            <td>
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="radio" name="{{ $field }}" value="" 
                                           {{ old($field, $report->$field) === null ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="section-score">
                            <td colspan="6" class="text-end">XB (Rata-rata Aspek B)</td>
                            <td class="text-center" id="xb_display">{{ $report->xb_score ?? '-' }}</td>
                        </tr>

                        {{-- C. Kepribadian Dan Sikap Kerja --}}
                        <tr class="table-secondary">
                            <td colspan="7"><strong>C. Kepribadian Dan Sikap Kerja</strong></td>
                        </tr>
                        @php
                            $aspekCFields = $reportType == '38' 
                                ? \App\Models\PsikotestReport::getAspekCFields38() 
                                : \App\Models\PsikotestReport::getAspekCFields();
                        @endphp
                        @foreach($aspekCFields as $field => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            @for($i = 1; $i <= 5; $i++)
                            <td>
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="radio" name="{{ $field }}" value="{{ $i }}" 
                                           {{ old($field, $report->$field) == $i ? 'checked' : '' }}>
                                </div>
                            </td>
                            @endfor
                            <td>
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="radio" name="{{ $field }}" value="" 
                                           {{ old($field, $report->$field) === null ? 'checked' : '' }}>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="section-score">
                            <td colspan="6" class="text-end">XC (Rata-rata Aspek C)</td>
                            <td class="text-center" id="xc_display">{{ $report->xc_score ?? '-' }}</td>
                        </tr>

                        {{-- Total Score --}}
                        <tr class="table-dark">
                            <td colspan="6" class="text-end"><strong>XT (TOTAL)</strong></td>
                            <td class="text-center" id="xt_display"><strong>{{ $report->xt_score ?? '-' }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Kesimpulan dan Saran --}}
            <h5 class="border-bottom pb-2 mb-3 mt-4 text-primary">
                <i class="bi bi-chat-left-text me-2"></i>Kesimpulan dan Saran
            </h5>
            <div class="mb-4">
                <textarea class="form-control @error('kesimpulan_saran') is-invalid @enderror" 
                          id="kesimpulan_saran" name="kesimpulan_saran" rows="4" 
                          placeholder="Contoh: Subyek DIPERTIMBANGKAN sebagai Operator">{{ old('kesimpulan_saran', $report->kesimpulan_saran) }}</textarea>
                @error('kesimpulan_saran')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
    const aspekAFields = @json(array_keys(\App\Models\PsikotestReport::getAspekAFields()));
    const aspekBFields = @json(array_keys(\App\Models\PsikotestReport::getAspekBFields()));
    const aspekCFields = @json(array_keys(\App\Models\PsikotestReport::getAspekCFields()));

    function calculateAverage(fields) {
        let sum = 0;
        let count = 0;
        fields.forEach(field => {
            const checked = document.querySelector(`input[name="${field}"]:checked`);
            if (checked && checked.value) {
                sum += parseInt(checked.value);
                count++;
            }
        });
        return count > 0 ? (sum / count).toFixed(2) : '-';
    }

    function updateScores() {
        const xa = calculateAverage(aspekAFields);
        const xb = calculateAverage(aspekBFields);
        const xc = calculateAverage(aspekCFields);
        
        document.getElementById('xa_display').textContent = xa;
        document.getElementById('xb_display').textContent = xb;
        document.getElementById('xc_display').textContent = xc;

        // Calculate total
        const allFields = [...aspekAFields, ...aspekBFields, ...aspekCFields];
        const xt = calculateAverage(allFields);
        document.getElementById('xt_display').textContent = xt;
    }

    // Add event listeners to all radio buttons
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', updateScores);
    });

    // Auto-detect IQ category based on score
    const iqScore = document.getElementById('iq_score');
    const iqCategory = document.getElementById('iq_category');

    iqScore.addEventListener('change', function() {
        const score = parseInt(this.value);
        if (score >= 140) {
            iqCategory.value = 'very_superior';
        } else if (score >= 120) {
            iqCategory.value = 'superior';
        } else if (score >= 110) {
            iqCategory.value = 'diatas_rata_rata';
        } else if (score >= 90) {
            iqCategory.value = 'rata_rata';
        } else if (score >= 80) {
            iqCategory.value = 'dibawah_rata_rata';
        } else if (score >= 70) {
            iqCategory.value = 'borderline';
        }
    });

    // Make entire table cell clickable for radio buttons
    document.querySelectorAll('.aspek-table td').forEach(td => {
        const radio = td.querySelector('input[type="radio"]');
        if (radio) {
            td.addEventListener('click', function(e) {
                if (e.target !== radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            });
        }
    });
});
</script>
@endpush
