<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] ?? 'Profil Usaha & Ikhtisar Arus Kas' }} — {{ $business['name'] ?? 'KasiVo' }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 16mm 14mm 16mm 14mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.45;
            color: #252525;
            background-color: #FFFFFF;
        }

        /* Utility Helpers */
        .w-full { width: 100%; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* Typography & Colors */
        .color-primary { color: #E87559; }
        .color-success { color: #2E7D68; }
        .color-dark { color: #252525; }
        .color-muted { color: #666666; }
        .color-light-muted { color: #888888; }

        /* Header Layout Table */
        table.layout-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        table.layout-table td {
            vertical-align: top;
            padding: 0;
        }

        .header-box {
            padding-bottom: 12px;
            border-bottom: 2px solid #E87559;
            margin-bottom: 14px;
        }

        .brand-title {
            font-size: 16pt;
            font-weight: bold;
            color: #E87559;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            font-size: 8pt;
            font-weight: 600;
            color: #777777;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #252525;
            margin-top: 6px;
        }

        .doc-meta {
            font-size: 8pt;
            color: #666666;
            line-height: 1.5;
        }

        .doc-meta-label {
            font-weight: bold;
            color: #252525;
        }

        /* Section Containers */
        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #252525;
            padding-bottom: 4px;
            margin-bottom: 6px;
            border-bottom: 1px solid #E5E7EB;
        }

        .card-box {
            background-color: #FBFBFC;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        /* Info Grid Table */
        table.info-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.info-table td {
            padding: 3px 4px;
            font-size: 8.5pt;
            vertical-align: top;
        }

        table.info-table td.label {
            width: 32%;
            color: #666666;
            font-weight: normal;
        }

        table.info-table td.value {
            width: 68%;
            color: #252525;
            font-weight: bold;
        }

        /* Financial 3-Card Summary Grid */
        table.stat-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 12px;
        }

        table.stat-grid td {
            vertical-align: top;
            padding: 10px 12px;
            border-radius: 8px;
            width: 33.33%;
        }

        .card-stat-in {
            background-color: #F0F9F5;
            border: 1px solid #CDE9DE;
            border-top: 3px solid #43A58A;
        }

        .card-stat-out {
            background-color: #FFF5F2;
            border: 1px solid #FCD8D0;
            border-top: 3px solid #E87559;
        }

        .card-stat-net {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-top: 3px solid #252525;
        }

        .stat-card-title {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666666;
            margin-bottom: 4px;
        }

        .stat-card-amount {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }

        .stat-card-desc {
            font-size: 7.5pt;
            color: #777777;
        }

        .badge-status {
            display: inline-block;
            font-size: 7pt;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 4px;
            margin-top: 4px;
        }

        .badge-surplus {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .badge-deficit {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        /* Breakdown Tables Layout */
        table.two-col-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 12px;
        }

        table.two-col-grid td.col-half {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            overflow: hidden;
        }

        table.data-table th {
            padding: 6px 8px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #E5E7EB;
        }

        table.data-table th.th-in {
            background-color: #E2F2ED;
            color: #1E6652;
        }

        table.data-table th.th-out {
            background-color: #FCEDEA;
            color: #B5452B;
        }

        table.data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #F3F4F6;
            color: #252525;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #FBFBFC;
        }

        table.data-table tr.total-row td {
            font-weight: bold;
            background-color: #F3F4F6;
            border-top: 1px solid #E5E7EB;
            border-bottom: none;
        }

        /* AI Narrative Box */
        .ai-narrative-box {
            background-color: #FFF9F7;
            border: 1px solid #FCD8D0;
            border-left: 4px solid #E87559;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .ai-badge {
            display: inline-block;
            font-size: 7pt;
            font-weight: bold;
            background-color: #FCEDEA;
            color: #E87559;
            padding: 2px 6px;
            border-radius: 4px;
            margin-bottom: 4px;
        }

        .ai-headline {
            font-size: 9.5pt;
            font-weight: bold;
            color: #252525;
            margin-bottom: 4px;
        }

        .ai-text {
            font-size: 8pt;
            line-height: 1.45;
            color: #444444;
        }

        /* Disclaimer Section (DoD Wajib KV-050) */
        .disclaimer-box {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-top: 8px;
        }

        .disclaimer-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .disclaimer-text {
            font-size: 7pt;
            line-height: 1.4;
            color: #64748B;
            text-align: justify;
        }

        /* Footer Note */
        .footer-note {
            margin-top: 8px;
            font-size: 7pt;
            color: #94A3B8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- 1. HEADER DOKUMEN & IDENTITAS KASIVO -->
    <div class="header-box">
        <table class="layout-table">
            <tr>
                <td style="width: 58%;">
                    <div class="brand-title">KasiVo</div>
                    <div class="brand-subtitle">Smart Business POS & Kasir Online</div>
                    <div class="doc-title">{{ $document['title'] }}</div>
                </td>
                <td style="width: 42%; text-align: right;" class="doc-meta">
                    <div><span class="doc-meta-label">No. Dokumen:</span> {{ $document['document_number'] }}</div>
                    <div><span class="doc-meta-label">Tanggal Cetak:</span> {{ $document['formatted_generated_at'] }}</div>
                    <div><span class="doc-meta-label">Periode:</span> {{ $period['label'] }}</div>
                    <div><span class="doc-meta-label">Rentang:</span> {{ $period['formatted_range'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- 2. IDENTITAS USAHA & PEMILIK (KV-010) -->
    <div class="card-box">
        <div class="section-title">Identitas Usaha & Periode Laporan</div>
        <table class="layout-table">
            <tr>
                <td style="width: 50%; padding-right: 8px;">
                    <table class="info-table">
                        <tr>
                            <td class="label">Nama Usaha</td>
                            <td class="value">: {{ $business['name'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Jenis Usaha</td>
                            <td class="value">: {{ $business['business_type_label'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Terdaftar Sejak</td>
                            <td class="value">: {{ $business['registered_date'] }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; padding-left: 8px; border-left: 1px solid #EEEEEE;">
                    <table class="info-table">
                        <tr>
                            <td class="label">Nama Pemilik</td>
                            <td class="value">: {{ $business['owner_name'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Email Akun</td>
                            <td class="value">: {{ $business['owner_email'] }}</td>
                        </tr>
                        <tr>
                            <td class="label">Periode Laporan</td>
                            <td class="value">: {{ $period['label'] }} ({{ $period['formatted_range'] }})</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- 3. RINGKASAN FINANSIAL ARUS KAS (KV-038) -->
    <table class="stat-grid">
        <tr>
            <!-- Kas Masuk -->
            <td class="card-stat-in">
                <div class="stat-card-title">Total Kas Masuk</div>
                <div class="stat-card-amount color-success">{{ $cash_flow['formatted_cash_in'] }}</div>
                <div class="stat-card-desc">{{ $cash_flow['cash_in_count'] }} transaksi pemasukan</div>
            </td>

            <!-- Kas Keluar -->
            <td class="card-stat-out">
                <div class="stat-card-title">Total Kas Keluar</div>
                <div class="stat-card-amount color-primary">{{ $cash_flow['formatted_cash_out'] }}</div>
                <div class="stat-card-desc">{{ $cash_flow['cash_out_count'] }} transaksi belanja/beban</div>
            </td>

            <!-- Arus Kas Bersih -->
            <td class="card-stat-net">
                <div class="stat-card-title">Arus Kas Bersih</div>
                <div class="stat-card-amount color-dark">{{ $cash_flow['formatted_net'] }}</div>
                <div>
                    <span class="badge-status {{ $cash_flow['is_negative'] ? 'badge-deficit' : 'badge-surplus' }}">
                        {{ $cash_flow['status_label'] }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- 4. RINCIAN KATEGORI PEMASUKAN & PENGELUARAN -->
    <table class="two-col-grid">
        <tr>
            <!-- Kolom Pemasukan -->
            <td class="col-half">
                <div class="section-title" style="color: #1E6652;">Rincian Pemasukan</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-in text-left">Kategori</th>
                            <th class="th-in text-right">Total</th>
                            <th class="th-in text-right" style="width: 20%;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories['cash_in'] as $inCat)
                            <tr>
                                <td>{{ $inCat['label'] }}</td>
                                <td class="text-right font-bold">{{ $inCat['formatted_total'] }}</td>
                                <td class="text-right">{{ $inCat['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center color-muted" style="padding: 10px;">
                                    Belum ada data kas masuk pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                        @if (!empty($categories['cash_in']))
                            <tr class="total-row">
                                <td>Total Pemasukan</td>
                                <td class="text-right">{{ $cash_flow['formatted_cash_in'] }}</td>
                                <td class="text-right">100%</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </td>

            <!-- Kolom Pengeluaran -->
            <td class="col-half">
                <div class="section-title" style="color: #B5452B;">Rincian Pengeluaran</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-out text-left">Kategori</th>
                            <th class="th-out text-right">Total</th>
                            <th class="th-out text-right" style="width: 20%;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories['cash_out'] as $outCat)
                            <tr>
                                <td>{{ $outCat['label'] }}</td>
                                <td class="text-right font-bold">{{ $outCat['formatted_total'] }}</td>
                                <td class="text-right">{{ $outCat['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center color-muted" style="padding: 10px;">
                                    Belum ada data kas keluar pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                        @if (!empty($categories['cash_out']))
                            <tr class="total-row">
                                <td>Total Pengeluaran</td>
                                <td class="text-right">{{ $cash_flow['formatted_cash_out'] }}</td>
                                <td class="text-right">100%</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- 5. CATATAN RINGKASAN NARASI USAHA (AI NARRATIVE) -->
    @if (!empty($ai_narrative))
        <div class="ai-narrative-box">
            <div class="ai-badge">Ringkasan Narasi Cerdas KasiVo</div>
            <div class="ai-headline">{{ $ai_narrative['headline'] ?? 'Ikhtisar Operasional KasiVo' }}</div>
            <div class="ai-text">{{ $ai_narrative['narrative'] ?? $ai_narrative['content'] ?? '' }}</div>
        </div>
    @endif

    <!-- 6. PERNYATAAN KEPATUHAN & BATASAN DOKUMEN (DISCLAIMER RESMI KV-050) -->
    <div class="disclaimer-box">
        <div class="disclaimer-title">Pernyataan Kepatuhan & Batasan Dokumen (Disclaimer)</div>
        <div class="disclaimer-text">
            {{ $disclaimer }}
        </div>
    </div>

    <!-- 7. FOOTER IDENTITAS SISTEM -->
    <div class="footer-note">
        Dokumen ini dibuat otomatis oleh KasiVo Smart Business POS & Kasir Online pada {{ $document['formatted_generated_at'] }}. Halaman 1 dari 1.
    </div>

</body>
</html>
