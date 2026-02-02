@extends('layouts.app')

@section('title', 'Laporan Psikotest - ' . $applicant->nama_lengkap)

@push('styles')
<style>
    /* Web view styles */
    .section-label {
        font-size: 12px;
        font-weight: 600;
        vertical-align: middle;
    }
    .aspek-table td {
        text-align: center;
        vertical-align: middle;
    }
    .aspek-table td:nth-child(2) {
        text-align: left;
    }

    @media print {
        @page {
            size: 215mm 330mm;
            margin: 10mm;
        }
        body {
            font-size: 10.5px !important;
            line-height: 1.1 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .no-print { display: none !important; }
        .container { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .card { border: none !important; box-shadow: none !important; margin: 0 !important; }
        .card-body { padding: 3px !important; }
        .card-header { background-color: #ffffff !important; color: #000000 !important; padding: 3px !important; }
        .card-header h4 { font-size: 14px !important; margin: 0 !important; }
        h5 { font-size: 11px !important; margin-bottom: 3px !important; padding-bottom: 2px !important; }
        .table { font-size: 10px !important;  }
        .table th, .table td { padding: 2px !important; }
        .aspek-table th, .aspek-table td { padding: 2px !important; }
        .mb-4 { margin-bottom: 3px !important; }
        .mb-3 { margin-bottom: 2px !important; }
        .mt-4 { margin-top: 3px !important; }
        .mt-3 { margin-top: 2px !important; }
        .p-3 { padding: 5px !important; }
        .alert { display: none !important; }
        .row { margin: 0 !important; }
        .table-borderless th, .table-borderless td { padding: 1px 0 !important; }
        .keterangan-skala { font-size: 8px !important; margin: 2px 0 !important; }
        .page-one-content { page-break-inside: avoid; }
        .kesimpulan-box {
            padding: 10px !important;
            font-size: 10px !important;
            min-height: 80px !important;
            page-break-inside: auto !important;
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
        @if(Auth::user()->role === 'admin')
        <a href="{{ route('psikotest.edit', $applicant) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endif
        <a href="{{ route('applicants.show', $applicant) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header text-center">
        <h4 class="mb-0">LAPORAN PSIKOTEST</h4>
    </div>
    <div class="card-body">
        <div class="page-one-content">
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
                            <td>: {{ $applicant->tanggal_test ? \Carbon\Carbon::parse($applicant->tanggal_test)->format('d F Y') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- IQ Section --}}
            <h5 class="border-bottom pb-2 mb-3 text-primary" style="color: #000 !important;">
                <span class="w-100 d-block text-center" style="color: #000 !important;">KATEGORI</span>
            </h5>
            <div class="row mb-4">
                <div class="col-12">
                    <table class="table table-bordered" style="width:100%;table-layout:fixed;margin-bottom:0;">
                        <thead>
                            <tr>
                                <th style="width:14%;text-align:left; vertical-align:top;">Taraf</th>
                                <th style="width:12%;text-align:center;">Borderline<br>70-79</th>
                                <th style="width:12%;text-align:center;">Dibawah Rata-Rata<br>80-89</th>
                                <th style="width:12%;text-align:center;">Rata-Rata<br>90-109</th>
                                <th style="width:12%;text-align:center;">Diatas Rata-Rata<br>110-119</th>
                                <th style="width:12%;text-align:center;">Superior<br>120-139</th>
                                <th style="width:12%;text-align:center;">Very Superior<br>140-169</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align:left;font-weight:bold;">Intelligence Quotient</td>
                                <td style="text-align:center;font-weight:bold;">@if($report->iq_category == 'borderline') {{ $report->iq_score }} @endif</td>
                                <td style="text-align:center;font-weight:bold;">@if($report->iq_category == 'dibawah_rata_rata') {{ $report->iq_score }} @endif</td>
                                <td style="text-align:center;font-weight:bold;">@if($report->iq_category == 'rata_rata') {{ $report->iq_score }} @endif</td>
                                <td style="text-align:center;font-weight:bold;">@if($report->iq_category == 'diatas_rata_rata') {{ $report->iq_score }} @endif</td>
                                <td style="text-align:center;font-weight:bold;">@if($report->iq_category == 'superior') {{ $report->iq_score }} @endif</td>
                                <td style="text-align:center;font-weight:bold;">@if($report->iq_category == 'very_superior') {{ $report->iq_score }} @endif</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Aspek Psikologis Table --}}
            @php
                $reportType = $report->report_type ?? '34';
                $aspekAFields = \App\Models\PsikotestReport::getAspekAFields();
                $aspekBFields = $reportType == '38'
                    ? \App\Models\PsikotestReport::getAspekBFields38()
                    : \App\Models\PsikotestReport::getAspekBFields();
                $aspekCFields = $reportType == '38'
                    ? \App\Models\PsikotestReport::getAspekCFields38()
                    : \App\Models\PsikotestReport::getAspekCFields();
                $aspekACount = count($aspekAFields);
                $aspekBCount = count($aspekBFields);
                $aspekCCount = count($aspekCFields);
            @endphp
            <div class="table-responsive">
                <table class="table table-bordered aspek-table">
                    <thead class="table-light">
                        <tr>
                            <th width="15%"></th>
                            <th width="35%">ASPEK PSIKOLOGIS</th>
                            <th width="8%" style="text-align:center;">A<br><small>1</small></th>
                            <th width="8%" style="text-align:center;">B<br><small>2</small></th>
                            <th width="8%" style="text-align:center;">C<br><small>3</small></th>
                            <th width="8%" style="text-align:center;">D<br><small>4</small></th>
                            <th width="8%" style="text-align:center;">E<br><small>5</small></th>
                            <th width="10%" style="text-align:center;">N/A</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- A. Kemampuan Intelektual Umum --}}
                        @php $first = true; @endphp
                        @foreach($aspekAFields as $field => $label)
                        <tr>
                            @if($first)
                            <td rowspan="{{ $aspekACount }}" class="section-label" style="font-size:14px;font-weight:600;vertical-align:middle;">A. Kemampuan<br>Intelektual Umum</td>
                            @php $first = false; @endphp
                            @endif
                            <td>{{ $label }}</td>
                            @for($i = 1; $i <= 5; $i++)
                            <td style="text-align:center;">@if($report->$field == $i) X @endif</td>
                            @endfor
                            <td style="text-align:center;">@if($report->$field === null) X @endif</td>
                        </tr>
                        @endforeach
                        <tr class="section-score">
                            <td colspan="7" class="text-end">XA</td>
                            <td class="text-center"><strong>{{ $report->xa_score ?? '-' }}</strong></td>
                        </tr>

                        {{-- B. Kemampuan Khusus --}}
                        @php $first = true; @endphp
                        @foreach($aspekBFields as $field => $label)
                        <tr>
                            @if($first)
                            <td rowspan="{{ $aspekBCount }}" class="section-label" style="font-size:14px;font-weight:600;vertical-align:middle;">B. Kemampuan<br>Khusus</td>
                            @php $first = false; @endphp
                            @endif
                            <td>{{ $label }}</td>
                            @for($i = 1; $i <= 5; $i++)
                            <td style="text-align:center;">@if($report->$field == $i) X @endif</td>
                            @endfor
                            <td style="text-align:center;">@if($report->$field === null) X @endif</td>
                        </tr>
                        @endforeach
                        <tr class="section-score">
                            <td colspan="7" class="text-end">XB</td>
                            <td class="text-center"><strong>{{ $report->xb_score ?? '-' }}</strong></td>
                        </tr>

                        {{-- C. Kepribadian Dan Sikap Kerja --}}
                        @php $first = true; @endphp
                        @foreach($aspekCFields as $field => $label)
                        <tr>
                            @if($first)
                            <td rowspan="{{ $aspekCCount }}" class="section-label" style="font-size:14px;font-weight:600;vertical-align:middle;">C. Kepribadian Dan<br>Sikap Kerja</td>
                            @php $first = false; @endphp
                            @endif
                            <td>{{ $label }}</td>
                            @for($i = 1; $i <= 5; $i++)
                            <td style="text-align:center;">@if($report->$field == $i) X @endif</td>
                            @endfor
                            <td style="text-align:center;">@if($report->$field === null) X @endif</td>
                        </tr>
                        @endforeach
                        <tr class="section-score">
                            <td colspan="7" class="text-end">XC</td>
                            <td class="text-center"><strong>{{ $report->xc_score ?? '-' }}</strong></td>
                        </tr>

                        {{-- Total Score --}}
                        <tr class="section-score">
                            <td colspan="7" class="text-end"><strong>XT</strong></td>
                            <td class="text-center"><strong>{{ $report->xt_score ?? '-' }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {{-- Keterangan Skala --}}
            <div class="mt-3 mb-4 keterangan-skala">
                <strong>Keterangan Skala:</strong>
                <table style="font-size: inherit; margin-top: 2px;">
                    <tr>
                        <td style="padding-right: 16px;">A = 1</td>
                        <td style="padding-right: 16px;">Kurang</td>
                        <td style="padding-right: 16px;">C = 3</td>
                        <td style="padding-right: 16px;">Cukup</td>
                        <td style="padding-right: 16px;">E = 5</td>
                        <td>Baik</td>
                    </tr>
                    <tr>
                        <td style="padding-right: 16px;">B = 2</td>
                        <td style="padding-right: 16px;">Hampir Cukup</td>
                        <td style="padding-right: 16px;">D = 4</td>
                        <td style="padding-right: 16px;">Cukup Baik</td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>
        {{-- Kesimpulan dan Saran --}}
        <h5 class="border-bottom pb-2 mb-3 text-primary" style="color: #000 !important;">
            Kesimpulan dan Saran
        </h5>
        <div class="p-3 bg-light border rounded kesimpulan-box">
            @if($report->kesimpulan_saran)
                <p class="mb-0">{!! nl2br(e($report->kesimpulan_saran)) !!}</p>
            @else
                <p class="text-muted mb-0">Belum ada kesimpulan dan saran.</p>
            @endif
        </div>
    </div>
</div>
@endsection
