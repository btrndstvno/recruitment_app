<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Psikotest - {{ $applicant->nama_lengkap }}</title>
    @php
        $reportType = $report->report_type ?? '34';
        $isType38 = $reportType == '38';
        // Both types fit 1 F4 page — type 38 slightly smaller due to more rows
        $bodyFont = $isType38 ? '9.5px' : '10px';
        $bodyLine = $isType38 ? '1.15' : '1.2';
        $titleFont = $isType38 ? '13px' : '14px';
        $headerFont = $isType38 ? '9px' : '9.5px';
        $headerNameFont = $isType38 ? '11px' : '12px';
        $headerPad = $isType38 ? '1.5px 0' : '2px 0';
        $iqFont = $isType38 ? '8px' : '8.5px';
        $iqPad = $isType38 ? '2px 3px' : '2.5px 3px';
        $aspekFont = $isType38 ? '9px' : '9.5px';
        $aspekPad = $isType38 ? '2px 3px' : '2.5px 3px';
        $sectionLabelFont = $isType38 ? '8.5px' : '9px';
        $sectionTitleFont = $isType38 ? '9.5px' : '10px';
        $ketFont = $isType38 ? '8px' : '8.5px';
        $kesimpulanFont = $isType38 ? '9px' : '9.5px';
        $kesimpulanHeight = $isType38 ? '10mm' : '12mm';
        $kesimpulanPad = $isType38 ? '5px' : '6px';
        $pageMargin = $isType38 ? '3mm 8mm 5mm 8mm' : '4mm 10mm 8mm 10mm';
    @endphp
    <style>
        @page {
            size: 215mm 330mm;
            margin: {{ $pageMargin }};
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: {{ $bodyFont }};
            line-height: {{ $bodyLine }};
            font-weight: 600;
            color: #000;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-title {
            font-size: {{ $titleFont }};
            font-weight: 700;
            text-align: center;
            margin: 0 0 2px 0;
        }
        .section-title {
            font-size: {{ $sectionTitleFont }};
            font-weight: 700;
            margin: 3px 0 2px 0;
            padding-bottom: 1px;
            border-bottom: 1px solid #000;
            text-align: center;
        }
        .kesimpulan-title {
            font-size: {{ $sectionTitleFont }};
            font-weight: 700;
            margin: 3px 0 2px 0;
            padding-bottom: 1px;
            border-bottom: 1px solid #000;
        }

        /* Header info */
        .header-table { margin-bottom: 2px; }
        .header-table td, .header-table th {
            padding: {{ $headerPad }};
            font-size: {{ $headerFont }};
            font-weight: 600;
            border: none;
            vertical-align: top;
        }
        .header-table th { text-align: left; width: 25%; }
        .applicant-name { font-size: {{ $headerNameFont }}; font-weight: 700; }

        /* IQ Table */
        .iq-table { margin-bottom: 3px; table-layout: fixed; }
        .iq-table th, .iq-table td {
            border: 1px solid #000;
            padding: {{ $iqPad }};
            font-size: {{ $iqFont }};
            font-weight: 600;
            text-align: center;
            vertical-align: top;
        }
        .iq-table th { background-color: #f0f0f0; font-weight: 700; }

        /* Aspek Table */
        .aspek-table { margin-bottom: 2px; table-layout: fixed; }
        .aspek-table th {
            border: 1px solid #000;
            padding: {{ $aspekPad }};
            font-size: {{ $aspekFont }};
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
            background-color: #f0f0f0;
        }
        .aspek-table td {
            border: 1px solid #000;
            padding: {{ $aspekPad }};
            font-size: {{ $aspekFont }};
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
        }
        .section-label {
            font-size: {{ $sectionLabelFont }} !important;
            font-weight: 700;
            text-align: left !important;
            vertical-align: middle;
        }
        .aspek-name { text-align: left !important; }
        .section-score td { background-color: #e9ecef; font-weight: 700; }
        .mark-x { font-weight: 700; }

        /* Keterangan */
        .keterangan-skala { font-size: {{ $ketFont }}; font-weight: 600; margin: 2px 0; }
        .keterangan-skala table td { border: none; padding: 0px 10px 0px 0; font-size: {{ $ketFont }}; }

        /* Kesimpulan */
        .kesimpulan-box {
            border: 1px solid #ccc;
            padding: {{ $kesimpulanPad }};
            font-size: {{ $kesimpulanFont }};
            font-weight: 600;
            min-height: {{ $kesimpulanHeight }};
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    @php
        $aspekAFields = \App\Models\PsikotestReport::getAspekAFields();
        $aspekBFields = $isType38
            ? \App\Models\PsikotestReport::getAspekBFields38()
            : \App\Models\PsikotestReport::getAspekBFields();
        $aspekCFields = $isType38
            ? \App\Models\PsikotestReport::getAspekCFields38()
            : \App\Models\PsikotestReport::getAspekCFields();
        $aspekACount = count($aspekAFields);
        $aspekBCount = count($aspekBFields);
        $aspekCCount = count($aspekCFields);
    @endphp

    <div class="report-title">LAPORAN PSIKOTEST</div>

    {{-- Header Info --}}
    <table class="header-table">
        <tr><th>Nama</th><td class="applicant-name">: <strong>{{ $applicant->nama_lengkap }}</strong></td></tr>
        <tr><th>Alamat</th><td>: {{ $applicant->alamat }}</td></tr>
        <tr><th>No. Handphone</th><td>: {{ $applicant->no_hp_1 }}</td></tr>
        <tr><th>Tempat / Tanggal Lahir</th><td>: {{ $applicant->ttl }}</td></tr>
        <tr><th>Pendidikan</th><td>: {{ $applicant->pendidikan }}</td></tr>
        <tr><th>Tanggal Test</th><td>: {{ $applicant->tanggal_test ? \Carbon\Carbon::parse($applicant->tanggal_test)->format('d F Y') : '-' }}</td></tr>
    </table>

    {{-- Kategori --}}
    <div class="section-title">KATEGORI</div>
    <table class="iq-table">
        <thead>
            <tr>
                <th style="width:22%; text-align:left;">Taraf</th>
                <th style="width:13%;">Borderline<br>70-79</th>
                <th style="width:13%;">Dibawah Rata-Rata<br>80-89</th>
                <th style="width:13%;">Rata-Rata<br>90-109</th>
                <th style="width:13%;">Diatas Rata-Rata<br>110-119</th>
                <th style="width:13%;">Superior<br>120-139</th>
                <th style="width:13%;">Very Superior<br>140-169</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:left; font-weight:700;">Intelligence Quotient</td>
                <td style="font-weight:700;">@if($report->iq_category == 'borderline'){{ $report->iq_score }}@endif</td>
                <td style="font-weight:700;">@if($report->iq_category == 'dibawah_rata_rata'){{ $report->iq_score }}@endif</td>
                <td style="font-weight:700;">@if($report->iq_category == 'rata_rata'){{ $report->iq_score }}@endif</td>
                <td style="font-weight:700;">@if($report->iq_category == 'diatas_rata_rata'){{ $report->iq_score }}@endif</td>
                <td style="font-weight:700;">@if($report->iq_category == 'superior'){{ $report->iq_score }}@endif</td>
                <td style="font-weight:700;">@if($report->iq_category == 'very_superior'){{ $report->iq_score }}@endif</td>
            </tr>
        </tbody>
    </table>

    {{-- Aspek Psikologis --}}
    <table class="aspek-table">
        <thead>
            <tr>
                <th style="width:14%;"></th>
                <th style="width:50%;">ASPEK PSIKOLOGIS</th>
                <th style="width:5%;">A<br><small>1</small></th>
                <th style="width:5%;">B<br><small>2</small></th>
                <th style="width:5%;">C<br><small>3</small></th>
                <th style="width:5%;">D<br><small>4</small></th>
                <th style="width:5%;">E<br><small>5</small></th>
                <th style="width:5%;">N/A</th>
            </tr>
        </thead>
        <tbody>
            {{-- A. Kemampuan Intelektual Umum --}}
            @php $first = true; @endphp
            @foreach($aspekAFields as $field => $label)
            <tr>
                @if($first)
                <td rowspan="{{ $aspekACount }}" class="section-label">A. Kemampuan<br>Intelektual Umum</td>
                @php $first = false; @endphp
                @endif
                <td class="aspek-name">{{ $label }}</td>
                @for($i = 1; $i <= 5; $i++)
                <td>@if($report->$field == $i)<span class="mark-x">X</span>@endif</td>
                @endfor
                <td></td>
            </tr>
            @endforeach
            <tr class="section-score">
                <td colspan="7" style="text-align:right;">XA (Rata-rata Aspek A)</td>
                <td style="text-align:center;"><strong>{{ $report->xa_score ?? '-' }}</strong></td>
            </tr>

            {{-- B. Kemampuan Khusus --}}
            @php $first = true; @endphp
            @foreach($aspekBFields as $field => $label)
            <tr>
                @if($first)
                <td rowspan="{{ $aspekBCount }}" class="section-label">B. Kemampuan<br>Khusus</td>
                @php $first = false; @endphp
                @endif
                <td class="aspek-name">{{ $label }}</td>
                @for($i = 1; $i <= 5; $i++)
                <td>@if($report->$field == $i)<span class="mark-x">X</span>@endif</td>
                @endfor
                <td></td>
            </tr>
            @endforeach
            <tr class="section-score">
                <td colspan="7" style="text-align:right;">XB (Rata-rata Aspek B)</td>
                <td style="text-align:center;"><strong>{{ $report->xb_score ?? '-' }}</strong></td>
            </tr>

            {{-- C. Kepribadian Dan Sikap Kerja --}}
            @php $first = true; @endphp
            @foreach($aspekCFields as $field => $label)
            <tr>
                @if($first)
                <td rowspan="{{ $aspekCCount }}" class="section-label">C. Kepribadian Dan<br>Sikap Kerja</td>
                @php $first = false; @endphp
                @endif
                <td class="aspek-name">{{ $label }}</td>
                @for($i = 1; $i <= 5; $i++)
                <td>@if($report->$field == $i)<span class="mark-x">X</span>@endif</td>
                @endfor
                <td></td>
            </tr>
            @endforeach
            <tr class="section-score">
                <td colspan="7" style="text-align:right;">XC (Rata-rata Aspek C)</td>
                <td style="text-align:center;"><strong>{{ $report->xc_score ?? '-' }}</strong></td>
            </tr>

            {{-- Total --}}
            <tr class="section-score">
                <td colspan="7" style="text-align:right;"><strong>XT (TOTAL)</strong></td>
                <td style="text-align:center;"><strong>{{ $report->xt_score ?? '-' }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Keterangan Skala --}}
    <div class="keterangan-skala">
        <strong>Keterangan Skala:</strong>
        <table>
            <tr>
                <td>A = 1</td><td>Kurang</td>
                <td>C = 3</td><td>Cukup</td>
                <td>E = 5</td><td>Baik</td>
            </tr>
            <tr>
                <td>B = 2</td><td>Hampir Cukup</td>
                <td>D = 4</td><td>Cukup Baik</td>
                <td></td><td></td>
            </tr>
        </table>
    </div>

    {{-- Kesimpulan dan Saran --}}
    <div class="kesimpulan-title">Kesimpulan dan Saran</div>
    <div class="kesimpulan-box">
        @if($report->kesimpulan_saran)
            {!! nl2br(e($report->kesimpulan_saran)) !!}
        @else
            Belum ada kesimpulan dan saran.
        @endif
    </div>
</body>
</html>
