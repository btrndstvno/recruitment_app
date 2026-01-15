@extends('layouts.app')

@section('title', 'Laporan Psikotest - ' . $applicant->nama_lengkap)

@push('styles')
<style>
    .aspek-table th, .aspek-table td {
        text-align: center;
        vertical-align: middle;
    }
    .aspek-table td:first-child {
        text-align: left;
    }
    .section-score {
        background-color: #e9ecef;
        font-weight: bold;
    }
    .score-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #0d6efd;
        color: white;
        font-weight: bold;
        font-size: 12px;
    }
    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            font-size: 10px !important;
            line-height: 1.1 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .no-print {
            display: none !important;
        }
        .container {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
        }
        .card-body {
            padding: 3px !important;
        }
        .card-header {
            background-color: #000 !important;
            color: #fff !important;
            padding: 3px !important;
        }
        .card-header h4 {
            font-size: 14px !important;
            margin: 0 !important;
        }
        h5 {
            font-size: 11px !important;
            margin-bottom: 3px !important;
            padding-bottom: 2px !important;
        }
        .table {
            font-size: 8px !important;
            margin-bottom: 3px !important;
        }
        .table th, .table td {
            padding: 1px 2px !important;
        }
        .aspek-table th, .aspek-table td {
            padding: 1px 2px !important;
        }
        .score-circle {
            width: 14px;
            height: 14px;
            font-size: 8px;
        }
        .mb-4 {
            margin-bottom: 3px !important;
        }
        .mb-3 {
            margin-bottom: 2px !important;
        }
        .mt-4 {
            margin-top: 3px !important;
        }
        .mt-3 {
            margin-top: 2px !important;
        }
        .p-3 {
            padding: 5px !important;
        }
        .alert {
            display: none !important;
        }
        .row {
            margin: 0 !important;
        }
        .table-borderless th,
        .table-borderless td {
            padding: 1px 0 !important;
        }
        .keterangan-skala {
            font-size: 8px !important;
            margin: 2px 0 !important;
        }
        .kesimpulan-box {
            padding: 10px !important;
            font-size: 10px !important;
            min-height: 80px !important;
        }
        footer {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h2><i class="bi bi-file-text me-2"></i>Laporan Psikotest</h2>
    <div>
        <button onclick="window.print()" class="btn btn-outline-primary me-2">
            <i class="bi bi-printer me-1"></i>Cetak
        </button>
        <a href="{{ route('psikotest.edit', $applicant) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('applicants.show', $applicant) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-dark text-white text-center">
        <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>LAPORAN PSIKOTEST</h4>
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
                    <tr>
                        <th>Tanggal Test</th>
                        <td>: {{ $report->tanggal_test->format('d F Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- IQ Section --}}
        <h5 class="border-bottom pb-2 mb-3 text-primary">
            <i class="bi bi-lightbulb me-2"></i>Intelligence Quotient (IQ)
        </h5>
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Skor IQ</th>
                        <td>{{ $report->iq_score ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kategori</th>
                        <td>{{ $report->iq_category_label }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Aspek Psikologis Table --}}
        <h5 class="border-bottom pb-2 mb-3 text-primary">
            <i class="bi bi-table me-2"></i>Aspek Psikologis
        </h5>

        
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
                            @if($report->$field == $i)
                                <span class="score-circle">{{ $i }}</span>
                            @endif
                        </td>
                        @endfor
                        <td>
                            @if($report->$field === null)
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr class="section-score">
                        <td colspan="6" class="text-end">XA (Rata-rata Aspek A)</td>
                        <td class="text-center"><strong>{{ $report->xa_score ?? '-' }}</strong></td>
                    </tr>

                    {{-- B. Kemampuan Khusus --}}
                    <tr class="table-secondary">
                        <td colspan="7"><strong>B. Kemampuan Khusus</strong></td>
                    </tr>
                    @php
                        $reportType = $report->report_type ?? '34';
                        $aspekBFields = $reportType == '38' 
                            ? \App\Models\PsikotestReport::getAspekBFields38() 
                            : \App\Models\PsikotestReport::getAspekBFields();
                    @endphp
                    @foreach($aspekBFields as $field => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        @for($i = 1; $i <= 5; $i++)
                        <td>
                            @if($report->$field == $i)
                                <span class="score-circle">{{ $i }}</span>
                            @endif
                        </td>
                        @endfor
                        <td>
                            @if($report->$field === null)
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr class="section-score">
                        <td colspan="6" class="text-end">XB (Rata-rata Aspek B)</td>
                        <td class="text-center"><strong>{{ $report->xb_score ?? '-' }}</strong></td>
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
                            @if($report->$field == $i)
                                <span class="score-circle">{{ $i }}</span>
                            @endif
                        </td>
                        @endfor
                        <td>
                            @if($report->$field === null)
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr class="section-score">
                        <td colspan="6" class="text-end">XC (Rata-rata Aspek C)</td>
                        <td class="text-center"><strong>{{ $report->xc_score ?? '-' }}</strong></td>
                    </tr>

                    {{-- Total Score --}}
                    <tr class="table-dark">
                        <td colspan="6" class="text-end"><strong>XT (TOTAL)</strong></td>
                        <td class="text-center"><strong>{{ $report->xt_score ?? '-' }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Keterangan Skala --}}
        <div class="mt-3 mb-4 keterangan-skala">
            <strong>Keterangan Skala:</strong>
            A = 1 = Kurang | B = 2 = Hampir Cukup | C = 3 = Cukup | D = 4 = Cukup Baik | E = 5 = Baik
        </div>

        {{-- Kesimpulan dan Saran --}}
        <h5 class="border-bottom pb-2 mb-3 text-primary">
            <i class="bi bi-chat-left-text me-2"></i>Kesimpulan dan Saran
        </h5>
        <div class="p-3 bg-light border rounded kesimpulan-box">
            @if($report->kesimpulan_saran)
                <p class="mb-0">{{ $report->kesimpulan_saran }}</p>
            @else
                <p class="text-muted mb-0">Belum ada kesimpulan dan saran.</p>
            @endif
        </div>
    </div>
</div>
@endsection
