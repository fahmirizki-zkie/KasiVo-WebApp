<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $document['title'] }} — {{ $business['name'] }}</title>
</head>
<body>

    {{-- 
        ======================================================================
        KERANGKA DATA TEMPLATE PROFIL USAHA (PDF A4)
        Catatan untuk Frontend / UI/UX (Putri & Angel):
        File ini merupakan kerangka data murni tanpa styling. Silakan rancang
        tata letak visual, CSS, font, warna, dan komponen sesuai mock-up Figma.
        ======================================================================
    --}}

    <!-- Header Dokumen -->
    <div>
        <h1>{{ $document['title'] }}</h1>
        <p>No. Dokumen: {{ $document['document_number'] }}</p>
        <p>Tanggal Cetak: {{ $document['formatted_generated_at'] }}</p>
    </div>

    <hr>

    <!-- Bagian 1: Identitas Usaha & Pemilik (KV-010) -->
    <div>
        <h2>Identitas Usaha & Periode Laporan</h2>
        <table>
            <tr>
                <td>Nama Usaha</td>
                <td>: {{ $business['name'] }}</td>
            </tr>
            <tr>
                <td>Jenis Usaha</td>
                <td>: {{ $business['business_type_label'] }}</td>
            </tr>
            <tr>
                <td>Nama Pemilik</td>
                <td>: {{ $business['owner_name'] }}</td>
            </tr>
            <tr>
                <td>Email Terdaftar</td>
                <td>: {{ $business['owner_email'] }}</td>
            </tr>
            <tr>
                <td>Tanggal Bergabung</td>
                <td>: {{ $business['registered_date'] }}</td>
            </tr>
            <tr>
                <td>Periode Laporan</td>
                <td>: {{ $period['label'] }} ({{ $period['formatted_range'] }})</td>
            </tr>
        </table>
    </div>

    <hr>

    <!-- Bagian 2: Ringkasan Arus Kas Utama (KV-038) -->
    <div>
        <h2>Ringkasan Arus Kas</h2>
        <table>
            <tr>
                <td>Total Kas Masuk</td>
                <td>: {{ $cash_flow['formatted_cash_in'] }} ({{ $cash_flow['cash_in_count'] }} transaksi)</td>
            </tr>
            <tr>
                <td>Total Kas Keluar</td>
                <td>: {{ $cash_flow['formatted_cash_out'] }} ({{ $cash_flow['cash_out_count'] }} transaksi)</td>
            </tr>
            <tr>
                <td>Arus Kas Bersih</td>
                <td>: {{ $cash_flow['formatted_net'] }}</td>
            </tr>
            <tr>
                <td>Status Arus Kas</td>
                <td>: {{ $cash_flow['status_label'] }}</td>
            </tr>
        </table>
    </div>

    <hr>

    <!-- Bagian 3: Rincian Kategori Pemasukan & Pengeluaran -->
    <div>
        <h2>Rincian Kategori</h2>
        
        <!-- Pemasukan -->
        <h3>Pemasukan</h3>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Total</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories['cash_in'] as $inCat)
                    <tr>
                        <td>{{ $inCat['label'] }}</td>
                        <td>{{ $inCat['formatted_total'] }}</td>
                        <td>{{ $inCat['percentage'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada data kas masuk pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pengeluaran -->
        <h3>Pengeluaran</h3>
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Total</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories['cash_out'] as $outCat)
                    <tr>
                        <td>{{ $outCat['label'] }}</td>
                        <td>{{ $outCat['formatted_total'] }}</td>
                        <td>{{ $outCat['percentage'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada data kas keluar pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <hr>

    <!-- Bagian 4: Slot Ringkasan Narasi AI (Akan diisi oleh KV-051) -->
    @if (!empty($ai_narrative))
        <div>
            <h2>Catatan Ringkasan Narasi Usaha</h2>
            <h4>{{ $ai_narrative['headline'] ?? 'Ikhtisar Operasional KasiVo' }}</h4>
            <p>{{ $ai_narrative['narrative'] ?? $ai_narrative['content'] ?? '' }}</p>
        </div>
        <hr>
    @endif

    <!-- Bagian 5: Pernyataan Kepatuhan & Disclaimer (Wajib DoD KV-050: Tidak Menyebut Bukti Kelayakan Kredit) -->
    <div>
        <h3>Pernyataan Kepatuhan & Batasan Dokumen (Disclaimer)</h3>
        <p>{{ $disclaimer }}</p>
    </div>

</body>
</html>
