<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] ?? 'Profil Usaha & Ikhtisar Arus Kas' }} — {{ $business['name'] ?? 'KasiVo' }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 10mm 12mm 12mm 12mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.8pt;
            line-height: 1.5;
            color: #1E293B;
            background-color: #FFFFFF;
        }

        /* Utility */
        .w-full { width: 100%; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        table.layout-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        table.layout-table td {
            vertical-align: top;
            padding: 0;
        }

        /* 1. HERO EXECUTIVE HEADER (DARK NAVY & CORAL) */
        .hero-header {
            background-color: #0F172A;
            color: #FFFFFF;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 16px;
        }

        .brand-badge {
            display: inline-block;
            background-color: #E87559;
            color: #FFFFFF;
            font-size: 13pt;
            font-weight: 800;
            padding: 4px 9px;
            border-radius: 6px;
            letter-spacing: -0.5px;
            line-height: 1;
        }

        .brand-title {
            display: inline-block;
            font-size: 18pt;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.5px;
            vertical-align: middle;
            margin-left: 6px;
        }

        .brand-title span {
            color: #E87559;
        }

        .brand-subtitle {
            font-size: 7.2pt;
            font-weight: 700;
            color: #94A3B8;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-top: 4px;
        }

        .doc-heading {
            font-size: 14pt;
            font-weight: 800;
            color: #FFFFFF;
            letter-spacing: -0.3px;
            margin-top: 8px;
            text-transform: uppercase;
        }

        .doc-sub {
            font-size: 8pt;
            color: #94A3B8;
            margin-top: 2px;
        }

        .header-meta-box {
            background-color: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 7.5pt;
            line-height: 1.6;
            color: #E2E8F0;
        }

        .meta-lbl {
            color: #94A3B8;
            font-weight: 600;
            display: inline-block;
            width: 80px;
        }

        .meta-val {
            color: #FFFFFF;
            font-weight: 700;
        }

        /* 2. BUSINESS PROFILE SECTION */
        .card-box {
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            background-color: #FFFFFF;
            overflow: hidden;
            margin-bottom: 16px;
        }

        .card-header-bar {
            background-color: #F8FAFC;
            border-bottom: 1px solid #E2E8F0;
            padding: 9px 16px;
            font-size: 8pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #0F172A;
        }

        .card-content {
            padding: 14px 16px;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.info-table td {
            padding: 5px 8px;
            font-size: 8.5pt;
            vertical-align: top;
        }

        table.info-table td.col-l {
            width: 115px;
            color: #64748B;
            font-weight: 500;
        }

        table.info-table td.col-v {
            color: #0F172A;
            font-weight: 700;
        }

        .badge-pill-cat {
            display: inline-block;
            background-color: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
            padding: 2.5px 8px;
            border-radius: 4px;
            font-size: 7.2pt;
            font-weight: 700;
        }

        /* 3. THREE FINANCIAL KPI HIGHLIGHTS */
        table.kpi-row {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 16px;
        }

        table.kpi-row td.kpi-cell {
            width: 33.33%;
            border-radius: 10px;
            padding: 14px 16px;
            vertical-align: top;
        }

        .kpi-in {
            background-color: #F0FDF4;
            border: 1.5px solid #BBF7D0;
            border-top: 4.5px solid #10B981;
        }

        .kpi-out {
            background-color: #FFF1F2;
            border: 1.5px solid #FECDD3;
            border-top: 4.5px solid #E87559;
        }

        .kpi-net {
            background-color: #F8FAFC;
            border: 1.5px solid #CBD5E1;
            border-top: 4.5px solid #0F172A;
        }

        .kpi-tag-label {
            font-size: 7.8pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 6px;
        }

        .kpi-num-large {
            font-size: 16.5pt;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
            line-height: 1.15;
        }

        .kpi-sub-desc {
            font-size: 7.5pt;
            color: #64748B;
        }

        .status-badge-green {
            display: inline-block;
            background-color: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 7.2pt;
            font-weight: 700;
            margin-top: 6px;
        }

        .status-badge-red {
            display: inline-block;
            background-color: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 7.2pt;
            font-weight: 700;
            margin-top: 6px;
        }

        /* 4. BREAKDOWN SIDE-BY-SIDE PANELS */
        table.breakdown-two-col {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 16px;
        }

        table.breakdown-two-col td.panel-col {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .table-panel {
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            background-color: #FFFFFF;
            overflow: hidden;
            min-height: 155px;
        }

        .table-panel-header-in {
            background-color: #10B981;
            color: #FFFFFF;
            padding: 9px 14px;
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .table-panel-header-out {
            background-color: #E87559;
            color: #FFFFFF;
            padding: 9px 14px;
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        table.data-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.2pt;
        }

        table.data-grid th {
            padding: 7px 10px;
            background-color: #F8FAFC;
            color: #475569;
            font-weight: 700;
            font-size: 7.2pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #E2E8F0;
        }

        table.data-grid td {
            padding: 7px 10px;
            border-bottom: 1px solid #F1F5F9;
            color: #1E293B;
            vertical-align: middle;
        }

        table.data-grid tr:nth-child(even) td {
            background-color: #FAFAFC;
        }

        table.data-grid tr.total-bar td {
            font-weight: 800;
            background-color: #F1F5F9;
            border-top: 1.5px solid #CBD5E1;
            border-bottom: none;
            color: #0F172A;
            padding: 8px 10px;
        }

        .empty-notice-container {
            padding: 30px 16px;
            text-align: center;
        }

        .empty-notice-title {
            color: #475569;
            font-size: 8.5pt;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .empty-notice-sub {
            color: #94A3B8;
            font-size: 7.5pt;
            line-height: 1.45;
        }

        /* 5. AI INTELLIGENCE SUMMARY CARD */
        .ai-intelligence-box {
            background-color: #FFFDF7;
            border: 1px solid #FDE68A;
            border-left: 5px solid #E87559;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 16px;
        }

        .ai-pill-label {
            display: inline-block;
            background-color: #FEF3C7;
            color: #B45309;
            border: 1px solid #FCD34D;
            padding: 2.5px 8px;
            border-radius: 4px;
            font-size: 7.2pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 6px;
        }

        .ai-title-bold {
            font-size: 10.5pt;
            font-weight: 800;
            color: #0F172A;
            margin-bottom: 6px;
        }

        .ai-body-text {
            font-size: 8.5pt;
            line-height: 1.6;
            color: #334155;
            text-align: justify;
        }

        /* 6. LEGAL COMPLIANCE & DIGITAL VERIFICATION SEAL (DoD KV-050) */
        table.legal-seal-row {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 14px;
        }

        table.legal-seal-row td.col-disclaimer {
            width: 66%;
            vertical-align: top;
            padding: 0;
        }

        table.legal-seal-row td.col-stamp {
            width: 34%;
            vertical-align: top;
            padding: 0;
        }

        .disclaimer-panel {
            background-color: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            padding: 12px 14px;
            min-height: 102px;
        }

        .disclaimer-panel-title {
            font-size: 7.5pt;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 4px;
        }

        .disclaimer-panel-text {
            font-size: 7.2pt;
            line-height: 1.5;
            color: #64748B;
            text-align: justify;
        }

        .digital-seal-panel {
            background-color: #F8FAFC;
            border: 1px dashed #94A3B8;
            border-radius: 10px;
            padding: 12px 14px;
            text-align: center;
            min-height: 102px;
        }

        .seal-pill {
            display: inline-block;
            background-color: #E2E8F0;
            color: #1E293B;
            font-size: 7.2pt;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 4px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .seal-name {
            font-size: 9pt;
            font-weight: 800;
            color: #0F172A;
            margin-top: 2px;
        }

        .seal-firm {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 1px;
        }

        .seal-date {
            font-size: 6.8pt;
            color: #94A3B8;
            line-height: 1.4;
            margin-top: 5px;
        }

        /* 7. FOOTER NOTE */
        .page-footer-note {
            border-top: 1px solid #E2E8F0;
            padding-top: 8px;
            font-size: 7.2pt;
            color: #94A3B8;
        }
    </style>
</head>
<body>

    <!-- 1. HERO EXECUTIVE HEADER (DARK NAVY & CORAL ACCENT) -->
    <div class="hero-header">
        <table class="layout-table">
            <tr>
                <td style="width: 55%; vertical-align: middle;">
                    <div>
                        <span class="brand-badge">KV</span>
                        <span class="brand-title">Kasi<span>Vo</span></span>
                    </div>
                    <div class="brand-subtitle">Smart Business POS & Financial OS</div>
                    <div class="doc-heading">{{ $document['title'] }}</div>
                    <div class="doc-sub">Laporan Profil Usaha & Rekapitulasi Arus Kas Mandiri UMKM</div>
                </td>
                <td style="width: 45%; vertical-align: middle;">
                    <div class="header-meta-box" style="margin-left: auto; width: 95%;">
                        <div><span class="meta-lbl">No. Dokumen:</span> <span class="meta-val">{{ $document['document_number'] }}</span></div>
                        <div><span class="meta-lbl">Tanggal Cetak:</span> <span class="meta-val">{{ $document['formatted_generated_at'] }}</span></div>
                        <div><span class="meta-lbl">Periode:</span> <span class="meta-val">{{ $period['label'] }}</span></div>
                        <div><span class="meta-lbl">Rentang:</span> <span class="meta-val">{{ $period['formatted_range'] }}</span></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 2. IDENTITAS PROFIL USAHA & PEMILIK (KV-010) -->
    <div class="card-box">
        <div class="card-header-bar">
            Identitas Profil Usaha & Periode Laporan
        </div>
        <div class="card-content">
            <table class="layout-table">
                <tr>
                    <td style="width: 50%; padding-right: 14px;">
                        <table class="info-table">
                            <tr>
                                <td class="col-l">Nama Usaha</td>
                                <td class="col-v">: {{ $business['name'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-l">Jenis Usaha</td>
                                <td class="col-v">: <span class="badge-pill-cat">{{ $business['business_type_label'] }}</span></td>
                            </tr>
                            <tr>
                                <td class="col-l">Terdaftar Sejak</td>
                                <td class="col-v">: {{ $business['registered_date'] }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; padding-left: 14px; border-left: 1px solid #F1F5F9;">
                        <table class="info-table">
                            <tr>
                                <td class="col-l">Nama Pemilik</td>
                                <td class="col-v">: {{ $business['owner_name'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-l">Email Akun</td>
                                <td class="col-v">: {{ $business['owner_email'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-l">Periode Laporan</td>
                                <td class="col-v">: {{ $period['label'] }} ({{ $period['formatted_range'] }})</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 3. TIGA KARTU RINGKASAN FINANSIAL ARUS KAS (KV-038) -->
    <table class="kpi-row">
        <tr>
            <!-- Kas Masuk -->
            <td class="kpi-cell kpi-in">
                <div class="kpi-tag-label" style="color: #15803D;">TOTAL KAS MASUK</div>
                <div class="kpi-num-large" style="color: #15803D;">{{ $cash_flow['formatted_cash_in'] }}</div>
                <div class="kpi-sub-desc">{{ $cash_flow['cash_in_count'] }} transaksi pemasukan</div>
            </td>

            <!-- Kas Keluar -->
            <td class="kpi-cell kpi-out">
                <div class="kpi-tag-label" style="color: #BE123C;">TOTAL KAS KELUAR</div>
                <div class="kpi-num-large" style="color: #BE123C;">{{ $cash_flow['formatted_cash_out'] }}</div>
                <div class="kpi-sub-desc">{{ $cash_flow['cash_out_count'] }} transaksi belanja/beban</div>
            </td>

            <!-- Arus Kas Bersih -->
            <td class="kpi-cell kpi-net">
                <div class="kpi-tag-label" style="color: #0F172A;">ARUS KAS BERSIH</div>
                <div class="kpi-num-large" style="color: #0F172A;">{{ $cash_flow['formatted_net'] }}</div>
                <div>
                    <span class="{{ $cash_flow['is_negative'] ? 'status-badge-red' : 'status-badge-green' }}">
                        {{ $cash_flow['status_label'] }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- 4. RINCIAN KATEGORI PEMASUKAN & PENGELUARAN (DUA PANEL BERDAMPINGAN) -->
    <table class="breakdown-two-col">
        <tr>
            <!-- Panel Pemasukan -->
            <td class="panel-col">
                <div class="table-panel">
                    <div class="table-panel-header-in">Rincian Pemasukan</div>
                    <table class="data-grid">
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 50%;">Kategori</th>
                                <th class="text-right" style="width: 32%;">Total</th>
                                <th class="text-right" style="width: 18%;">Porsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories['cash_in'] as $inCat)
                                <tr>
                                    <td>{{ $inCat['label'] }}</td>
                                    <td class="text-right font-bold" style="color: #15803D;">{{ $inCat['formatted_total'] }}</td>
                                    <td class="text-right" style="color: #64748B;">{{ $inCat['percentage'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="empty-notice-container">
                                        <div class="empty-notice-title">Belum ada data kas masuk pada periode ini.</div>
                                        <div class="empty-notice-sub">Pencatatan omzet penjualan akan otomatis direkapitulasi di sini.</div>
                                    </td>
                                </tr>
                            @endforelse
                            @if (!empty($categories['cash_in']))
                                <tr class="total-bar">
                                    <td>Total Pemasukan</td>
                                    <td class="text-right" style="color: #15803D;">{{ $cash_flow['formatted_cash_in'] }}</td>
                                    <td class="text-right">100%</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </td>

            <!-- Panel Pengeluaran -->
            <td class="panel-col">
                <div class="table-panel">
                    <div class="table-panel-header-out">Rincian Pengeluaran</div>
                    <table class="data-grid">
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 50%;">Kategori</th>
                                <th class="text-right" style="width: 32%;">Total</th>
                                <th class="text-right" style="width: 18%;">Porsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories['cash_out'] as $outCat)
                                <tr>
                                    <td>{{ $outCat['label'] }}</td>
                                    <td class="text-right font-bold" style="color: #BE123C;">{{ $outCat['formatted_total'] }}</td>
                                    <td class="text-right" style="color: #64748B;">{{ $outCat['percentage'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="empty-notice-container">
                                        <div class="empty-notice-title">Belum ada data kas keluar pada periode ini.</div>
                                        <div class="empty-notice-sub">Pencatatan belanja & beban usaha akan otomatis direkapitulasi di sini.</div>
                                    </td>
                                </tr>
                            @endforelse
                            @if (!empty($categories['cash_out']))
                                <tr class="total-bar">
                                    <td>Total Pengeluaran</td>
                                    <td class="text-right" style="color: #BE123C;">{{ $cash_flow['formatted_cash_out'] }}</td>
                                    <td class="text-right">100%</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- 5. RINGKASAN NARASI EKSEKUTIF CERDAS AI -->
    @if (!empty($ai_narrative))
        <div class="ai-intelligence-box">
            <div class="ai-pill-label">✦ Ringkasan Narasi Cerdas KasiVo</div>
            <div class="ai-title-bold">{{ $ai_narrative['headline'] ?? 'Ikhtisar Operasional KasiVo' }}</div>
            <div class="ai-body-text">{{ $ai_narrative['narrative'] ?? $ai_narrative['content'] ?? '' }}</div>
        </div>
    @endif

    <!-- 6. PERNYATAAN KEPATUHAN & LEMBAR INTEGRITAS DATA (DoD Wajib KV-050) -->
    <table class="legal-seal-row">
        <tr>
            <td class="col-disclaimer">
                <div class="disclaimer-panel">
                    <div class="disclaimer-panel-title">Pernyataan Kepatuhan & Batasan Dokumen (Disclaimer)</div>
                    <div class="disclaimer-panel-text">
                        {{ $disclaimer }}
                    </div>
                </div>
            </td>
            <td class="col-stamp">
                <div class="digital-seal-panel">
                    <span class="seal-pill">Integritas Data Digital</span>
                    <div class="seal-name">{{ $business['owner_name'] }}</div>
                    <div class="seal-firm">{{ $business['name'] }}</div>
                    <div class="seal-date">
                        Tercatat pada Sistem KasiVo POS<br>
                        {{ $document['formatted_generated_at'] }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- 7. FOOTER BAR -->
    <table class="layout-table page-footer-note">
        <tr>
            <td style="width: 60%;">
                Dokumen resmi profil usaha mandiri ini digenerate secara digital oleh Sistem KasiVo Smart POS & Kasir Online.
            </td>
            <td style="width: 40%; text-align: right;">
                Halaman 1 dari 1 • Validasi Digital Mandiri
            </td>
        </tr>
    </table>

</body>
</html>
