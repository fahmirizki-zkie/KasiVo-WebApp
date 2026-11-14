@php
    $logoPath = public_path('images/kasivo-logo.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] }} — {{ $business['name'] }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 14mm 12mm 14mm;
        }

        * {
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1E293B;
            font-size: 8.5pt;
            line-height: 1.4;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

        /* Utility & Grid Tables (Dompdf Flex/Grid Replacement) */
        table.layout-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        table.layout-table td {
            vertical-align: top;
            padding: 0;
        }

        /* Header Bar */
        .header-container {
            border-bottom: 2px solid #E87559;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .brand-logo {
            height: 28px;
            width: auto;
            vertical-align: middle;
        }

        .brand-text {
            font-size: 16pt;
            font-weight: 800;
            color: #E87559;
            letter-spacing: -0.5px;
            line-height: 1;
        }

        .brand-text .dark {
            color: #1E293B;
        }

        .brand-tagline {
            font-size: 7pt;
            color: #64748B;
            margin-top: 3px;
            font-weight: 500;
        }

        .doc-badge {
            display: inline-block;
            background-color: #FCEDEA;
            color: #E87559;
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 8px;
            border-radius: 4px;
            margin-bottom: 4px;
        }

        .doc-meta {
            font-size: 7.5pt;
            color: #64748B;
            line-height: 1.35;
        }

        .doc-meta strong {
            color: #1E293B;
        }

        /* Hero / Document Title Banner */
        .hero-banner {
            background-color: #FFF7F5;
            border: 1px solid #FED7AA;
            border-left: 4px solid #E87559;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }

        .hero-title {
            font-size: 13pt;
            font-weight: 800;
            color: #1E293B;
            letter-spacing: -0.3px;
            margin: 0 0 2px 0;
            text-transform: uppercase;
        }

        .hero-subtitle {
            font-size: 7.5pt;
            color: #64748B;
            margin: 0;
        }

        .period-card {
            background-color: #FFFFFF;
            border: 1px solid #FED7AA;
            border-radius: 5px;
            padding: 6px 10px;
            text-align: right;
        }

        .period-badge-label {
            font-size: 6.5pt;
            text-transform: uppercase;
            font-weight: 700;
            color: #E87559;
            letter-spacing: 0.5px;
        }

        .period-value {
            font-size: 9.5pt;
            font-weight: 800;
            color: #1E293B;
            margin-top: 1px;
        }

        .period-dates {
            font-size: 7pt;
            color: #64748B;
            margin-top: 1px;
        }

        /* Section Headings */
        .section-header {
            margin-bottom: 6px;
            margin-top: 12px;
        }

        .section-title {
            font-size: 8.5pt;
            font-weight: 800;
            color: #1E293B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #E2E8F0;
        }

        .section-title span.number {
            color: #E87559;
            margin-right: 4px;
        }

        /* Info Card: Identitas Usaha */
        .info-card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 8pt;
        }

        .data-label {
            color: #64748B;
            width: 32%;
            font-weight: 500;
        }

        .data-sep {
            width: 4%;
            color: #94A3B8;
        }

        .data-value {
            color: #1E293B;
            font-weight: 600;
            width: 64%;
        }

        /* Metric Cards: Ringkasan Arus Kas (3 Kolom) */
        .metric-card {
            border-radius: 6px;
            padding: 10px 10px;
            page-break-inside: avoid;
        }

        .metric-card-income {
            background-color: #F0FDF4;
            border: 1px solid #BBF7D0;
            border-top: 3px solid #10B981;
        }

        .metric-card-expense {
            background-color: #FFF1F2;
            border: 1px solid #FECDD3;
            border-top: 3px solid #F43F5E;
        }

        .metric-card-net {
            background-color: #FFF7ED;
            border: 1px solid #FED7AA;
            border-top: 3px solid #E87559;
        }

        .metric-card-net.deficit {
            background-color: #FFF1F2;
            border: 1px solid #FECDD3;
            border-top: 3px solid #F43F5E;
        }

        .metric-label {
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .metric-card-income .metric-label { color: #047857; }
        .metric-card-expense .metric-label { color: #BE123C; }
        .metric-card-net .metric-label { color: #C2410C; }
        .metric-card-net.deficit .metric-label { color: #BE123C; }

        .metric-value {
            font-size: 13pt;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .metric-card-income .metric-value { color: #065F46; }
        .metric-card-expense .metric-value { color: #9F1239; }
        .metric-card-net .metric-value { color: #9A3412; }
        .metric-card-net.deficit .metric-value { color: #9F1239; }

        .metric-sub {
            font-size: 7pt;
            color: #64748B;
            font-weight: 500;
        }

        .metric-status-badge {
            display: inline-block;
            font-size: 6.5pt;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 3px;
            margin-top: 2px;
        }

        .status-surplus {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .status-deficit {
            background-color: #FFE4E6;
            color: #9F1239;
        }

        /* Category Tables */
        .cat-box {
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            background-color: #FFFFFF;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .cat-box-header {
            padding: 6px 10px;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #E2E8F0;
        }

        .cat-box-header.income {
            background-color: #ECFDF5;
            color: #047857;
            border-left: 3px solid #10B981;
        }

        .cat-box-header.expense {
            background-color: #FFF1F2;
            color: #BE123C;
            border-left: 3px solid #F43F5E;
        }

        table.breakdown-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        table.breakdown-table th {
            background-color: #F8FAFC;
            color: #64748B;
            font-weight: 700;
            text-align: left;
            padding: 5px 8px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 6.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table.breakdown-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #F1F5F9;
            color: #1E293B;
            vertical-align: middle;
        }

        table.breakdown-table tr:last-child td {
            border-bottom: none;
        }

        .pct-badge {
            display: inline-block;
            background-color: #F1F5F9;
            color: #475569;
            font-weight: 700;
            font-size: 6.5pt;
            padding: 1px 5px;
            border-radius: 3px;
        }

        /* AI Narrative Card (KV-051) */
        .narrative-card {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-left: 4px solid #E87559;
            border-radius: 6px;
            padding: 10px 12px;
            margin-top: 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .narrative-badge {
            display: inline-block;
            background-color: #FCEDEA;
            color: #E87559;
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-bottom: 4px;
        }

        .narrative-headline {
            font-size: 9pt;
            font-weight: 800;
            color: #1E293B;
            margin: 0 0 4px 0;
        }

        .narrative-text {
            font-size: 8pt;
            color: #334155;
            line-height: 1.45;
            margin: 0 0 6px 0;
        }

        .highlights-list {
            margin: 4px 0 6px 0;
            padding-left: 16px;
            font-size: 7.5pt;
            color: #475569;
        }

        .highlights-list li {
            margin-bottom: 2px;
        }

        .compliance-note-sm {
            font-size: 6.5pt;
            color: #94A3B8;
            font-style: italic;
            margin: 0;
        }

        /* Disclaimer Card (DoD KV-050 Wajib: Penafian Kredit) */
        .disclaimer-card {
            background-color: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-left: 4px solid #64748B;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .disclaimer-title {
            font-size: 7pt;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .disclaimer-text {
            font-size: 7pt;
            color: #475569;
            line-height: 1.4;
            margin: 0;
            text-align: justify;
        }

        /* Footer */
        .footer-container {
            border-top: 1px solid #E2E8F0;
            padding-top: 6px;
            margin-top: 10px;
            font-size: 6.5pt;
            color: #94A3B8;
        }
    </style>
</head>
<body>

    <!-- Header Dokumen -->
    <div class="header-container">
        <table class="layout-table">
            <tr>
                <td style="width: 58%;">
                    @if ($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="KasiVo" class="brand-logo">
                    @else
                        <span class="brand-text">Kasi<span class="dark">Vo</span></span>
                    @endif
                    <div class="brand-tagline">Sistem Kasir Pintar & Manajemen Keuangan UMKM Indonesia</div>
                </td>
                <td style="width: 42%; text-align: right;">
                    <div class="doc-badge">Dokumen Profil Resmi</div>
                    <div class="doc-meta">
                        <div>No. Dokumen: <strong>{{ $document['document_number'] }}</strong></div>
                        <div>Tanggal Cetak: <strong>{{ $document['formatted_generated_at'] }}</strong></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Hero / Document Title & Period Banner -->
    <div class="hero-banner">
        <table class="layout-table">
            <tr>
                <td style="width: 62%; vertical-align: middle;">
                    <h1 class="hero-title">{{ $document['title'] }}</h1>
                    <p class="hero-subtitle">Ringkasan profil identitas usaha dan ikhtisar catatan arus kas operasional internal.</p>
                </td>
                <td style="width: 38%; vertical-align: middle;">
                    <div class="period-card">
                        <div class="period-badge-label">Periode Laporan</div>
                        <div class="period-value">{{ $period['label'] }}</div>
                        <div class="period-dates">{{ $period['formatted_range'] }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Bagian 1: Identitas Usaha & Pemilik (KV-010) -->
    <div class="section-header">
        <h2 class="section-title"><span class="number">1.</span> Identitas Usaha & Periode Laporan</h2>
    </div>
    <div class="info-card">
        <table class="layout-table">
            <tr>
                <td style="width: 48%; padding-right: 10px;">
                    <table class="data-table">
                        <tr>
                            <td class="data-label">Nama Usaha</td>
                            <td class="data-sep">:</td>
                            <td class="data-value">{{ $business['name'] }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Jenis Usaha</td>
                            <td class="data-sep">:</td>
                            <td class="data-value">{{ $business['business_type_label'] }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Tanggal Bergabung</td>
                            <td class="data-sep">:</td>
                            <td class="data-value">{{ $business['registered_date'] }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; padding-left: 10px;">
                    <table class="data-table">
                        <tr>
                            <td class="data-label">Nama Pemilik</td>
                            <td class="data-sep">:</td>
                            <td class="data-value">{{ $business['owner_name'] }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Email Terdaftar</td>
                            <td class="data-sep">:</td>
                            <td class="data-value">{{ $business['owner_email'] }}</td>
                        </tr>
                        <tr>
                            <td class="data-label">Periode Laporan</td>
                            <td class="data-sep">:</td>
                            <td class="data-value">{{ $period['label'] }} ({{ $period['formatted_range'] }})</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Bagian 2: Ringkasan Arus Kas Utama (KV-038) -->
    <div class="section-header">
        <h2 class="section-title"><span class="number">2.</span> Ringkasan Arus Kas</h2>
    </div>
    <table class="layout-table" style="margin-bottom: 12px;">
        <tr>
            <!-- Kas Masuk -->
            <td style="width: 32%;">
                <div class="metric-card metric-card-income">
                    <div class="metric-label">Total Kas Masuk</div>
                    <div class="metric-value">{{ $cash_flow['formatted_cash_in'] }}</div>
                    <div class="metric-sub">{{ $cash_flow['cash_in_count'] }} transaksi</div>
                </div>
            </td>
            <!-- Spacer -->
            <td style="width: 2%;"></td>
            <!-- Kas Keluar -->
            <td style="width: 32%;">
                <div class="metric-card metric-card-expense">
                    <div class="metric-label">Total Kas Keluar</div>
                    <div class="metric-value">{{ $cash_flow['formatted_cash_out'] }}</div>
                    <div class="metric-sub">{{ $cash_flow['cash_out_count'] }} transaksi</div>
                </div>
            </td>
            <!-- Spacer -->
            <td style="width: 2%;"></td>
            <!-- Arus Kas Bersih -->
            <td style="width: 32%;">
                <div class="metric-card metric-card-net {{ $cash_flow['is_negative'] ? 'deficit' : '' }}">
                    <div class="metric-label">Arus Kas Bersih</div>
                    <div class="metric-value">{{ $cash_flow['formatted_net'] }}</div>
                    <div class="metric-sub">
                        <span class="metric-status-badge {{ $cash_flow['is_negative'] ? 'status-deficit' : 'status-surplus' }}">
                            {{ $cash_flow['status_label'] }}
                        </span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bagian 3: Rincian Kategori Pemasukan & Pengeluaran -->
    <div class="section-header">
        <h2 class="section-title"><span class="number">3.</span> Rincian Kategori</h2>
    </div>
    <table class="layout-table" style="margin-bottom: 8px;">
        <tr>
            <!-- Tabel Pemasukan -->
            <td style="width: 49%;">
                <div class="cat-box">
                    <div class="cat-box-header income">Pemasukan</div>
                    <table class="breakdown-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Kategori</th>
                                <th style="width: 32%; text-align: right;">Total</th>
                                <th style="width: 18%; text-align: right;">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories['cash_in'] as $inCat)
                                <tr>
                                    <td style="font-weight: 600;">{{ $inCat['label'] }}</td>
                                    <td style="text-align: right; font-weight: 700; color: #047857;">{{ $inCat['formatted_total'] }}</td>
                                    <td style="text-align: right;"><span class="pct-badge">{{ $inCat['percentage'] }}%</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #94A3B8; font-style: italic; padding: 12px 8px;">
                                        Belum ada data kas masuk pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
            <!-- Spacer -->
            <td style="width: 2%;"></td>
            <!-- Tabel Pengeluaran -->
            <td style="width: 49%;">
                <div class="cat-box">
                    <div class="cat-box-header expense">Pengeluaran</div>
                    <table class="breakdown-table">
                        <thead>
                            <tr>
                                <th style="width: 50%;">Kategori</th>
                                <th style="width: 32%; text-align: right;">Total</th>
                                <th style="width: 18%; text-align: right;">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories['cash_out'] as $outCat)
                                <tr>
                                    <td style="font-weight: 600;">{{ $outCat['label'] }}</td>
                                    <td style="text-align: right; font-weight: 700; color: #BE123C;">{{ $outCat['formatted_total'] }}</td>
                                    <td style="text-align: right;"><span class="pct-badge">{{ $outCat['percentage'] }}%</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #94A3B8; font-style: italic; padding: 12px 8px;">
                                        Belum ada data kas keluar pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bagian 4: Slot Ringkasan Narasi AI (KV-051) -->
    @if (!empty($ai_narrative))
        <div class="narrative-card">
            <div class="narrative-badge">Catatan Ringkasan Narasi Usaha</div>
            <h4 class="narrative-headline">{{ $ai_narrative['headline'] ?? 'Ikhtisar Operasional KasiVo' }}</h4>
            <p class="narrative-text">{{ $ai_narrative['narrative'] ?? $ai_narrative['content'] ?? '' }}</p>
            @if (!empty($ai_narrative['highlights']) && is_array($ai_narrative['highlights']))
                <ul class="highlights-list">
                    @foreach ($ai_narrative['highlights'] as $highlight)
                        <li>{{ $highlight }}</li>
                    @endforeach
                </ul>
            @endif
            @if (!empty($ai_narrative['compliance_note']))
                <p class="compliance-note-sm">{{ $ai_narrative['compliance_note'] }}</p>
            @endif
        </div>
    @endif

    <!-- Bagian 5: Pernyataan Kepatuhan & Disclaimer (Wajib DoD KV-050: Tidak Menyebut Bukti Kelayakan Kredit) -->
    <div class="disclaimer-card">
        <h3 class="disclaimer-title">Pernyataan Kepatuhan & Batasan Dokumen (Disclaimer)</h3>
        <p class="disclaimer-text">{{ $disclaimer }}</p>
    </div>

    <!-- Footer Dokumen -->
    <div class="footer-container">
        <table class="layout-table">
            <tr>
                <td style="width: 70%;">
                    KasiVo Smart POS & Financial Management • Dicetak otomatis untuk dokumentasi internal operasional usaha (kasivo.id)
                </td>
                <td style="width: 30%; text-align: right;">
                    Halaman 1 dari 1 • Dokumen Sah Internal
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
