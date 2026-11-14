<?php

namespace App\Services;

use App\Exceptions\AiQuotaExceededException;
use App\Services\Ai\GroqService;
use App\Services\Ai\TransactionJsonSchema;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class DigitalSalesParserService
{
    public function __construct(
        protected GroqService $groqService,
        protected AttachmentService $attachmentService
    ) {}

    /**
     * Parsing tangkapan layar (screenshot) aplikasi merchant menggunakan Vision AI.
     *
     * @param UploadedFile|string $file
     * @return array<string, mixed>
     */
    public function parseScreenshot(
        UploadedFile|string $file,
        string $platformHint = 'auto',
        ?int $businessId = null,
        ?int $userId = null
    ): array {
        $base64Image = '';
        $mimeType = 'image/jpeg';

        if ($file instanceof UploadedFile) {
            $base64Image = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType() ?: 'image/jpeg';
        } elseif (is_string($file)) {
            if (!file_exists($file)) {
                throw new InvalidArgumentException("Berkas gambar tidak ditemukan: {$file}");
            }
            $base64Image = base64_encode(file_get_contents($file));
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mimeType = match ($ext) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
        }

        $systemPrompt = TransactionJsonSchema::getDigitalSalesPrompt($platformHint);
        $userPromptText = "Ekstrak ringkasan penjualan harian atau mutasi dari screenshot aplikasi merchant ini sesuai skema.";
        if ($platformHint && $platformHint !== 'auto') {
            $userPromptText .= "\nPlatform terpilih pengguna: {$platformHint}.";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $userPromptText],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mimeType};base64,{$base64Image}",
                        ],
                    ],
                ],
            ],
        ];

        try {
            $rawParsed = $this->groqService->chat(
                $messages,
                model: config('services.groq.vision_model', 'llama-3.2-11b-vision-preview'),
                jsonMode: true
            );

            $normalized = TransactionJsonSchema::validateAndNormalizeDigitalSales($rawParsed);

            // Jika pengguna secara eksplisit memilih platform di awal dan hasil AI 'other', prioritaskan pilihan pengguna
            if ($platformHint !== 'auto' && $normalized['platform'] === 'other') {
                $normalized['platform'] = $platformHint;
            }

            // Tambahkan baris rekap ringkas ke array rows
            $normalized['rows'] = [
                [
                    'date' => $normalized['transaction_date'],
                    'amount' => $normalized['total_amount'],
                    'gross_amount' => $normalized['gross_amount'],
                    'fee_amount' => $normalized['fee_amount'],
                    'platform' => $normalized['platform'],
                    'notes' => $normalized['notes'],
                ],
            ];

            return $normalized;
        } catch (AiQuotaExceededException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Vision AI Digital Sales Parsing Failed', [
                'error' => $e->getMessage(),
                'platform_hint' => $platformHint,
            ]);

            throw new RuntimeException("Gagal mengekstrak data dari screenshot: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Parsing berkas spreadsheet (CSV atau XLSX).
     *
     * @param UploadedFile|string $file
     * @return array<string, mixed>
     */
    public function parseSpreadsheet(
        UploadedFile|string $file,
        ?string $extension = null,
        string $platformHint = 'auto'
    ): array {
        $filePath = '';
        $ext = '';

        if ($file instanceof UploadedFile) {
            $filePath = $file->getRealPath();
            $ext = strtolower($extension ?: $file->getClientOriginalExtension());
        } elseif (is_string($file)) {
            $filePath = $file;
            $ext = strtolower($extension ?: pathinfo($file, PATHINFO_EXTENSION));
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new InvalidArgumentException("Berkas spreadsheet tidak dapat dibaca: {$filePath}");
        }

        if ($ext === 'csv') {
            return $this->parseCsv($filePath, $platformHint);
        }

        if (in_array($ext, ['xlsx', 'xls'], true)) {
            return $this->parseXlsx($filePath, $platformHint);
        }

        throw new InvalidArgumentException("Ekstensi spreadsheet '{$ext}' tidak didukung. Gunakan CSV atau XLSX.");
    }

    /**
     * Parser berkas CSV dengan pendeteksian otomatis pemisah (comma vs semicolon vs tab).
     *
     * @return array<string, mixed>
     */
    public function parseCsv(string $filePath, string $platformHint = 'auto'): array
    {
        $content = file_get_contents($filePath);
        if ($content === false || trim($content) === '') {
            throw new InvalidArgumentException('Berkas CSV kosong atau tidak memiliki data.');
        }

        // Hapus BOM UTF-8 jika ada
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^{$bom}/", '', $content);

        // Deteksi delimiter
        $delimiter = $this->detectCsvDelimiter($content);

        $lines = explode("\n", $content);
        $rawRows = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            $data = str_getcsv($trimmed, $delimiter);
            if (!empty($data) && count(array_filter($data, fn($v) => trim((string)$v) !== '')) > 0) {
                $rawRows[] = $data;
            }
        }

        return $this->normalizeTabularRows($rawRows, $platformHint);
    }

    /**
     * Parser berkas XLSX native menggunakan ZipArchive dan SimpleXML (tanpa composer package eksternal).
     *
     * @return array<string, mixed>
     */
    public function parseXlsx(string $filePath, string $platformHint = 'auto'): array
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('PHP ZipArchive extension tidak tersedia di server.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new InvalidArgumentException('Gagal membuka berkas XLSX. Berkas mungkin rusak atau terproteksi.');
        }

        // 1. Baca shared strings jika ada (xl/sharedStrings.xml)
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = @simplexml_load_string($sharedStringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Baca worksheet pertama (xl/worksheets/sheet1.xml)
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new InvalidArgumentException('Lembar kerja sheet1.xml tidak ditemukan di dalam berkas XLSX.');
        }

        $xml = @simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) {
            throw new InvalidArgumentException('Struktur lembar kerja Excel kosong atau tidak valid.');
        }

        $rawRows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $type = (string) $c['t'];
                $val = isset($c->v) ? (string) $c->v : '';

                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $cellValue = $sharedStrings[(int) $val];
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $cellValue = (string) $c->is->t;
                } else {
                    $cellValue = $val;
                }

                $cells[] = trim($cellValue);
            }

            if (!empty($cells) && count(array_filter($cells, fn($v) => $v !== '')) > 0) {
                $rawRows[] = $cells;
            }
        }

        return $this->normalizeTabularRows($rawRows, $platformHint);
    }

    /**
     * Normalisasi baris data dari CSV/XLSX menjadi struktur transaksi KasiVo yang terpadu.
     *
     * @param array<int, array<int, string>> $rawRows
     * @return array<string, mixed>
     */
    protected function normalizeTabularRows(array $rawRows, string $platformHint = 'auto'): array
    {
        if (empty($rawRows)) {
            throw new InvalidArgumentException('Berkas tidak memiliki baris data untuk diekstrak.');
        }

        // 1. Deteksi Baris Header
        $headerIndex = -1;
        $dateCol = -1;
        $amountCol = -1;
        $grossCol = -1;
        $feeCol = -1;
        $notesCol = -1;
        $platformCol = -1;

        foreach ($rawRows as $idx => $row) {
            foreach ($row as $colIdx => $cell) {
                $clean = strtolower(trim((string) $cell));

                if ($dateCol === -1 && preg_match('/^(tanggal|date|tgl|waktu|settlement_date|created_at|transaksi_date)$/i', $clean)) {
                    $dateCol = $colIdx;
                }
                if ($amountCol === -1 && preg_match('/^(total|nominal|omzet|penjualan|net|amount|kredit|credit|jumlah|pendapatan|total_penjualan)$/i', $clean)) {
                    $amountCol = $colIdx;
                }
                if ($grossCol === -1 && preg_match('/^(gross|bruto|kotor|total_kotor|harga_kotor)$/i', $clean)) {
                    $grossCol = $colIdx;
                }
                if ($feeCol === -1 && preg_match('/^(fee|komisi|biaya|potongan|admin|mdr)$/i', $clean)) {
                    $feeCol = $colIdx;
                }
                if ($notesCol === -1 && preg_match('/^(keterangan|catatan|deskripsi|notes|description|menu|produk|item|nama_barang)$/i', $clean)) {
                    $notesCol = $colIdx;
                }
                if ($platformCol === -1 && preg_match('/^(platform|sumber|channel|tipe|metode)$/i', $clean)) {
                    $platformCol = $colIdx;
                }
            }

            // Jika menemukan setidaknya kolom tanggal atau nominal, jadikan baris ini sebagai header
            if ($dateCol !== -1 || $amountCol !== -1) {
                $headerIndex = $idx;
                break;
            }
        }

        // Jika tidak ada header yang cocok, asumsikan kolom 0 adalah tanggal (jika format tanggal) dan kolom 1 adalah nominal
        if ($headerIndex === -1) {
            $headerIndex = -1; // Data dimulai dari baris 0
            $dateCol = 0;
            $amountCol = 1;
            $notesCol = 2;
        }

        // 2. Iterasi Baris Data
        $parsedRows = [];
        $totalSum = 0.0;
        $grossSum = 0.0;
        $feeSum = 0.0;
        $uniqueDates = [];
        $detectedPlatform = $platformHint !== 'auto' ? $platformHint : 'other';

        $startIndex = $headerIndex + 1;
        for ($i = $startIndex; $i < count($rawRows); $i++) {
            $row = $rawRows[$i];
            if (empty($row)) {
                continue;
            }

            // Ambil nominal
            $amountVal = $amountCol >= 0 && isset($row[$amountCol]) ? $row[$amountCol] : '0';
            $amount = TransactionJsonSchema::sanitizeNumeric($amountVal);

            // Skip jika baris tidak memiliki nominal (misal baris pemisah kosong atau subheader)
            if ($amount <= 0) {
                continue;
            }

            // Ambil tanggal
            $dateVal = $dateCol >= 0 && isset($row[$dateCol]) ? trim((string) $row[$dateCol]) : '';
            $parsedDate = $this->parseDateString($dateVal);
            $uniqueDates[$parsedDate] = true;

            // Ambil gross & fee jika ada
            $grossVal = $grossCol >= 0 && isset($row[$grossCol]) ? TransactionJsonSchema::sanitizeNumeric($row[$grossCol]) : $amount;
            $feeVal = $feeCol >= 0 && isset($row[$feeCol]) ? TransactionJsonSchema::sanitizeNumeric($row[$feeCol]) : 0.0;

            // Ambil keterangan
            $notesVal = $notesCol >= 0 && isset($row[$notesCol]) ? trim((string) $row[$notesCol]) : '';

            // Ambil platform jika ada di kolom
            $rowPlatform = $detectedPlatform;
            if ($platformCol >= 0 && isset($row[$platformCol])) {
                $pClean = strtolower(trim((string) $row[$platformCol]));
                if (str_contains($pClean, 'go')) $rowPlatform = 'gofood';
                elseif (str_contains($pClean, 'grab')) $rowPlatform = 'grabfood';
                elseif (str_contains($pClean, 'shopee')) $rowPlatform = 'shopeefood';
                elseif (str_contains($pClean, 'qris')) $rowPlatform = 'qris';
                elseif (str_contains($pClean, 'pos') || str_contains($pClean, 'kasir')) $rowPlatform = 'pos';
            }

            $totalSum += $amount;
            $grossSum += ($grossVal ?: $amount);
            $feeSum += $feeVal;

            $parsedRows[] = [
                'date' => $parsedDate,
                'amount' => $amount,
                'gross_amount' => $grossVal ?: $amount,
                'fee_amount' => $feeVal,
                'platform' => $rowPlatform,
                'notes' => $notesVal ?: "Penjualan {$rowPlatform} {$parsedDate}",
            ];
        }

        if (empty($parsedRows)) {
            throw new InvalidArgumentException('Tidak ditemukan baris transaksi penjualan yang valid pada berkas spreadsheet ini.');
        }

        // Tentukan tanggal representatif (tanggal terbaru atau hari ini)
        $dateKeys = array_keys($uniqueDates);
        rsort($dateKeys);
        $primaryDate = $dateKeys[0] ?? Carbon::now()->toDateString();

        $platformLabels = [
            'gofood' => 'GoFood',
            'grabfood' => 'GrabFood',
            'shopeefood' => 'ShopeeFood',
            'qris' => 'QRIS',
            'pos' => 'Kasir POS',
            'other' => 'Penjualan Digital',
        ];
        $platformLabel = $platformLabels[$detectedPlatform] ?? 'Penjualan Digital';
        $summaryNotes = "Impor {$platformLabel} (" . count($parsedRows) . " baris transaksi)";

        return [
            'type' => TransactionJsonSchema::TYPE_CASH_IN,
            'category' => TransactionJsonSchema::CATEGORY_PENJUALAN,
            'platform' => $detectedPlatform,
            'transaction_date' => $primaryDate,
            'total_amount' => (float) $totalSum,
            'gross_amount' => (float) ($grossSum ?: $totalSum),
            'fee_amount' => (float) $feeSum,
            'net_amount' => (float) $totalSum,
            'order_count' => count($parsedRows),
            'merchant_name' => null,
            'notes' => $summaryNotes,
            'confidence_score' => 1.0,
            'needs_review' => false,
            'unclear_fields' => [],
            'review_reason' => null,
            'items' => [],
            'rows' => $parsedRows,
        ];
    }

    /**
     * Konversi variasi penulisan tanggal ke format YYYY-MM-DD.
     */
    protected function parseDateString(string $dateStr): string
    {
        $clean = trim($dateStr);
        if ($clean === '') {
            return Carbon::now()->toDateString();
        }

        // 1. Jika sudah YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $clean, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
        }

        // 2. Jika DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $clean, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }

        // 3. Fallback Carbon parse
        try {
            return Carbon::parse($clean)->toDateString();
        } catch (\Throwable) {
            return Carbon::now()->toDateString();
        }
    }

    /**
     * Deteksi otomatis pemisah CSV (, vs ; vs tab).
     */
    protected function detectCsvDelimiter(string $content): string
    {
        $firstLine = strtok($content, "\r\n") ?: '';
        
        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');
        $tabCount = substr_count($firstLine, "\t");

        if ($semicolonCount > $commaCount && $semicolonCount > $tabCount) {
            return ';';
        }

        if ($tabCount > $commaCount && $tabCount > $semicolonCount) {
            return "\t";
        }

        return ',';
    }
}

