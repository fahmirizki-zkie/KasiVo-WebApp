<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] ?? 'Profil Usaha & Ikhtisar Arus Kas' }} — {{ $business['name'] ?? 'KasiVo' }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 14mm 16mm 14mm 16mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 7.5pt;
            line-height: 1.35;
            color: #1E293B;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
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

        /* 1. LETTERHEAD FORMAL & BERSIH */
        .letterhead-box {
            padding-bottom: 6px;
            margin-bottom: 8px;
            border-bottom: 1.5px solid #0F172A;
        }

        .header-subline {
            font-size: 6.5pt;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 2px;
            font-weight: 600;
        }

        .meta-table {
            font-size: 6.8pt;
            line-height: 1.35;
            color: #334155;
            margin-left: auto;
        }

        .meta-table td {
            padding: 1px 0;
        }

        .meta-label {
            color: #64748B;
            font-weight: 600;
            padding-right: 6px;
            text-align: right;
            white-space: nowrap;
        }

        .meta-value {
            color: #0F172A;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        /* 2. DOKUMEN TITLE */
        .doc-header-box {
            text-align: center;
            margin-bottom: 8px;
            padding: 2px 0;
        }

        .doc-title {
            font-size: 11.5pt;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .doc-subtitle {
            font-size: 7pt;
            color: #64748B;
            margin-top: 2px;
            font-weight: 500;
        }

        /* 3. SECTION HEADINGS */
        .section-heading {
            font-size: 7.5pt;
            font-weight: 700;
            color: #0F172A;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding-bottom: 2px;
            margin-top: 6px;
            margin-bottom: 4px;
            border-bottom: 1px solid #CBD5E1;
        }

        /* 4. IDENTITAS USAHA TABLE */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt;
            margin-bottom: 6px;
            border: 1px solid #E2E8F0;
        }

        .info-table td {
            padding: 3.5px 6px;
            vertical-align: top;
            border-bottom: 1px solid #F1F5F9;
        }

        .info-label {
            width: 20%;
            color: #64748B;
            font-weight: 600;
            background-color: #F8FAFC;
            border-right: 1px solid #E2E8F0;
        }

        .info-value {
            width: 30%;
            color: #0F172A;
            font-weight: 600;
        }

        /* 5. KPI METRICS (3 CARDS) */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 6px;
        }

        .kpi-card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: left;
        }

        .kpi-card.income {
            border-top: 2.5px solid #10B981;
        }

        .kpi-card.expense {
            border-top: 2.5px solid #E87559;
        }

        .kpi-card.net {
            border-top: 2.5px solid #0F172A;
        }

        .kpi-title {
            font-size: 6.2pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748B;
            margin-bottom: 1px;
        }

        .kpi-amount {
            font-size: 10pt;
            font-weight: 800;
            line-height: 1.15;
        }

        .kpi-amount.income { color: #047857; }
        .kpi-amount.expense { color: #B91C1C; }
        .kpi-amount.net { color: #0F172A; }

        .kpi-desc {
            font-size: 6.2pt;
            color: #64748B;
            margin-top: 1px;
        }

        .kpi-badge {
            display: inline-block;
            font-size: 5.8pt;
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 2px;
            margin-top: 2px;
        }

        .kpi-badge.surplus {
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .kpi-badge.deficit {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* 6. TABEL RINCIAN KATEGORI */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
        }

        .data-table th {
            background-color: #F8FAFC;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 3.5px 5px;
            border-top: 1px solid #CBD5E1;
            border-bottom: 1px solid #CBD5E1;
            font-size: 6.2pt;
        }

        .data-table td {
            padding: 3px 5px;
            border-bottom: 1px solid #E2E8F0;
            color: #1E293B;
        }

        .data-table tr.total-row td {
            font-weight: 800;
            background-color: #F8FAFC;
            border-top: 1px solid #CBD5E1;
            border-bottom: 1px solid #0F172A;
            color: #0F172A;
            padding: 3.5px 5px;
        }

        /* 7. AI SMART BRIEF / NARASI */
        .narrative-box {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-left: 2.5px solid #E87559;
            border-radius: 4px;
            padding: 5px 8px;
            margin-top: 6px;
            margin-bottom: 5px;
        }

        .narrative-title {
            font-size: 7pt;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 2px;
        }

        .narrative-text {
            font-size: 6.8pt;
            line-height: 1.35;
            color: #334155;
        }

        /* 8. DISCLAIMER & KEPATUHAN */
        .disclaimer-box {
            background-color: #FFFFFF;
            border: 1px dashed #CBD5E1;
            border-radius: 4px;
            padding: 4px 7px;
            margin-top: 5px;
            margin-bottom: 6px;
        }

        .disclaimer-title {
            font-size: 6.2pt;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 1px;
        }

        .disclaimer-text {
            font-size: 6.2pt;
            line-height: 1.3;
            color: #64748B;
        }

        /* 9. SIGNATURE / PENGESAHAN */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
            margin-top: 5px;
        }

        .sign-table td {
            vertical-align: top;
            padding: 0;
        }

        .signature-line {
            width: 140px;
            border-bottom: 1px solid #0F172A;
            margin-top: 22px;
            margin-bottom: 2px;
        }

        /* 10. FOOTER */
        .footer-note {
            font-size: 6pt;
            color: #94A3B8;
            border-top: 1px solid #E2E8F0;
            padding-top: 4px;
            margin-top: 6px;
            text-align: center;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/kasivo-logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
    @endphp

    <!-- 1. LETTERHEAD FORMAL DENGAN LOGO ASLI KASIVO -->
    <div class="letterhead-box">
        <table class="layout-table">
            <tr>
                <td style="width: 50%;">
                    @if ($logoBase64)
                        <img src="{{ $logoBase64 }}" style="height: 30px; width: auto;" alt="KasiVo" />
                    @else
                        <div style="font-size: 16pt; font-weight: 800; color: #E87559; letter-spacing: -0.5px;">KasiVo</div>
                    @endif
                    <div class="header-subline">Sistem POS Cerdas &amp; Rekapitulasi Finansial UMKM</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">No. Dokumen:</td>
                            <td class="meta-value">{{ $document['document_number'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Tanggal Cetak:</td>
                            <td class="meta-value">{{ $document['formatted_generated_at'] ?? now()->translatedFormat('d F Y, H:i') . ' WIB' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Periode Laporan:</td>
                            <td class="meta-value">{{ $period['label'] ?? 'Bulan Ini' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Rentang Waktu:</td>
                            <td class="meta-value">{{ $period['formatted_range'] ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- 2. JUDUL DOKUMEN FORMAL -->
    <div class="doc-header-box">
        <div class="doc-title">LAPORAN KEUANGAN &amp; PROFIL USAHA</div>
        <div class="doc-subtitle">Ikhtisar Kinerja Operasional dan Ringkasan Arus Kas Masuk &amp; Keluar</div>
    </div>

    <!-- 3. IDENTITAS USAHA & PEMILIK -->
    <div class="section-heading">1. Identitas Usaha &amp; Pemilik</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Usaha</td>
            <td class="info-value">{{ $business['name'] ?? '-' }}</td>
            <td class="info-label">Nama Pemilik</td>
            <td class="info-value">{{ $business['owner_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Jenis Usaha</td>
            <td class="info-value">{{ $business['business_type_label'] ?? 'UMKM F&B' }}</td>
            <td class="info-label">Email Akun</td>
            <td class="info-value">{{ $business['owner_email'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Terdaftar Sejak</td>
            <td class="info-value">{{ $business['registered_date'] ?? '-' }}</td>
            <td class="info-label">Basis Pencatatan</td>
            <td class="info-value">Kas Masuk &amp; Kas Keluar (Cash-Basis)</td>
        </tr>
    </table>

    <!-- 4. IKHTISAR ARUS KAS (3 KPI BOXES FORMAL) -->
    <div class="section-heading">2. Ikhtisar Arus Kas Periode {{ $period['label'] ?? 'Bulan Ini' }}</div>
    <table class="kpi-table">
        <tr>
            <td style="width: 33.33%;">
                <div class="kpi-card income">
                    <div class="kpi-title">Total Kas Masuk</div>
                    <div class="kpi-amount income">{{ $cash_flow['formatted_cash_in'] ?? 'Rp 0' }}</div>
                    <div class="kpi-desc">{{ $cash_flow['cash_in_count'] ?? 0 }} Transaksi Penjualan / Masuk</div>
                </div>
            </td>
            <td style="width: 33.33%;">
                <div class="kpi-card expense">
                    <div class="kpi-title">Total Kas Keluar</div>
                    <div class="kpi-amount expense">{{ $cash_flow['formatted_cash_out'] ?? 'Rp 0' }}</div>
                    <div class="kpi-desc">{{ $cash_flow['cash_out_count'] ?? 0 }} Transaksi Belanja &amp; Beban</div>
                </div>
            </td>
            <td style="width: 33.33%;">
                <div class="kpi-card net">
                    <div class="kpi-title">Arus Kas Bersih</div>
                    <div class="kpi-amount net">{{ $cash_flow['formatted_net'] ?? 'Rp 0' }}</div>
                    <span class="kpi-badge {{ ($cash_flow['is_negative'] ?? false) ? 'deficit' : 'surplus' }}">
                        {{ $cash_flow['status_label'] ?? 'Surplus Arus Kas Bersih' }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- 5. RINCIAN PER KATEGORI (2 TABEL BERDAMPINGAN) -->
    <div class="section-heading">3. Rincian Pemasukan &amp; Pengeluaran per Kategori</div>
    <table class="layout-table" style="margin-bottom: 4px;">
        <tr>
            <!-- Tabel Pemasukan -->
            <td style="width: 49%; vertical-align: top;">
                <div style="font-size: 6.8pt; font-weight: 700; color: #047857; text-transform: uppercase; margin-bottom: 2px;">
                    Rincian Kas Masuk (Penerimaan)
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-left" style="width: 50%;">Kategori</th>
                            <th class="text-right" style="width: 30%;">Total</th>
                            <th class="text-right" style="width: 20%;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories['cash_in'] as $cat)
                            <tr>
                                <td class="text-left">{{ $cat['label'] }}</td>
                                <td class="text-right font-bold">{{ $cat['formatted_total'] }}</td>
                                <td class="text-right">{{ $cat['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="color: #94A3B8; padding: 6px 0;">
                                    Belum ada data kas masuk pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td class="text-left">TOTAL PENERIMAAN</td>
                            <td class="text-right font-bold">{{ $cash_flow['formatted_cash_in'] ?? 'Rp 0' }}</td>
                            <td class="text-right font-bold">{{ count($categories['cash_in']) > 0 ? '100%' : '0%' }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <td style="width: 2%;"></td>

            <!-- Tabel Pengeluaran -->
            <td style="width: 49%; vertical-align: top;">
                <div style="font-size: 6.8pt; font-weight: 700; color: #B91C1C; text-transform: uppercase; margin-bottom: 2px;">
                    Rincian Kas Keluar (Pengeluaran)
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-left" style="width: 50%;">Kategori</th>
                            <th class="text-right" style="width: 30%;">Total</th>
                            <th class="text-right" style="width: 20%;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories['cash_out'] as $cat)
                            <tr>
                                <td class="text-left">{{ $cat['label'] }}</td>
                                <td class="text-right font-bold">{{ $cat['formatted_total'] }}</td>
                                <td class="text-right">{{ $cat['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="color: #94A3B8; padding: 6px 0;">
                                    Belum ada data kas keluar pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td class="text-left">TOTAL PENGELUARAN</td>
                            <td class="text-right font-bold">{{ $cash_flow['formatted_cash_out'] ?? 'Rp 0' }}</td>
                            <td class="text-right font-bold">{{ count($categories['cash_out']) > 0 ? '100%' : '0%' }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- 6. IKHTISAR OPERASIONAL (AI NARRATIVE BRIEF) -->
    <div class="narrative-box">
        <div class="narrative-title">
            {{ $ai_narrative['headline'] ?? 'Ikhtisar Aktivitas Usaha' }}
        </div>
        <div class="narrative-text">
            {{ $ai_narrative['narrative'] ?? 'Belum ada ringkasan aktivitas transaksi pada periode ini.' }}
        </div>
    </div>

    <!-- 7. PERNYATAAN KEPATUHAN & BATASAN DOKUMEN (DISCLAIMER MANDAT KV-050) -->
    <div class="disclaimer-box">
        <div class="disclaimer-title">Pernyataan Kepatuhan & Batasan Dokumen (Disclaimer)</div>
        <div class="disclaimer-text">
            {{ $disclaimer ?? 'Dokumen ini merupakan profil ringkasan operasional dan catatan arus kas internal usaha berdasarkan pencatatan transaksi mandiri oleh pemilik usaha. Dokumen ini BUKAN merupakan surat jaminan, rekomendasi pinjaman, atau bukti kelayakan kredit perbankan/lembaga pembiayaan.' }}
        </div>
    </div>

    <!-- 8. PENGESAHAN / TANDA TANGAN DOKUMEN -->
    <table class="sign-table">
        <tr>
            <td style="width: 60%; vertical-align: bottom;">
                <div style="font-size: 6.5pt; color: #64748B; line-height: 1.35;">
                    Dokumen ini dihasilkan secara otomatis oleh sistem KasiVo Smart Business POS.<br>
                    Integritas data diverifikasi berdasarkan catatan pembukuan mandiri pengguna.
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: bottom;">
                <div style="font-size: 6.8pt; color: #334155; margin-bottom: 1px;">
                    Pemilik Usaha / Penanggung Jawab,
                </div>
                <div class="signature-line" style="margin-left: auto;"></div>
                <div style="font-size: 7.2pt; font-weight: 700; color: #0F172A;">
                    {{ $business['owner_name'] ?? 'Pemilik Usaha' }}
                </div>
                <div style="font-size: 6.5pt; color: #64748B;">
                    {{ $business['name'] ?? '-' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- 9. FOOTER RESMI -->
    <div class="footer-note">
        Dokumen Resmi Profil Usaha &amp; Ikhtisar Arus Kas &bull; Dicetak melalui KasiVo POS pada {{ $document['formatted_generated_at'] ?? now()->translatedFormat('d F Y, H:i') . ' WIB' }} &bull; Halaman 1 dari 1
    </div>

</body>
</html>
