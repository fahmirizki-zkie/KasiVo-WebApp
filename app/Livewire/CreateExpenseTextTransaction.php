<?php

namespace App\Livewire;

use App\Models\Business;
use App\Models\Transaction;
use App\Services\Ai\GroqService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateExpenseTextTransaction extends Component
{
    public string $sourceType = 'text';

    /**
     * Input teks bebas dari pedagang.
     */
    public string $rawText = '';

    /**
     * Alur antarmuka: 'input' (mengetik teks) | 'review' (konfirmasi draft).
     */
    public string $step = 'input';

    /**
     * Indikator animasi loading pemrosesan AI.
     */
    public bool $isProcessing = false;

    /**
     * Data draft transaksi hasil ekstraksi AI.
     *
     * @var array<string, mixed>
     */
    public array $draft = [
        'type' => 'cash_out',
        'category' => 'bahan_baku',
        'amount' => 0,
        'transaction_date' => '',
        'merchant_name' => '',
        'notes' => '',
        'confidence_score' => 1.0,
        'needs_review' => false,
        'unclear_fields' => [],
        'review_reason' => null,
        'items' => [],
    ];

    /**
     * Pesan kesalahan jika terjadi kendala.
     */
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->draft['transaction_date'] = now()->toDateString();
    }

    /**
     * Ambil profil usaha aktif milik pengguna yang terautentikasi.
     */
    protected function getActiveBusiness(): ?Business
    {
        return auth()->user()?->businesses()->first();
    }

    /**
     * Isi cepat textarea dengan template contoh kalimat warung.
     */
    public function applyTemplate(string $template): void
    {
        $this->rawText = $template;
        $this->errorMessage = null;
    }

    /**
     * Proses teks bebas pengguna menggunakan Groq LLM.
     */
    public function processText(GroqService $groqService): void
    {
        $this->errorMessage = null;

        $this->validate([
            'rawText' => 'required|string|min:5|max:1000',
        ], [
            'rawText.required' => 'Tuliskan catatan transaksi terlebih dahulu.',
            'rawText.min' => 'Catatan transaksi minimal 5 karakter (contoh: "Beli beras 50rb").',
            'rawText.max' => 'Catatan transaksi maksimal 1.000 karakter.',
        ]);

        $business = $this->getActiveBusiness();
        if (!$business) {
            session()->flash('message', 'Silakan daftarkan profil usaha terlebih dahulu.');
            $this->redirect(route('onboarding'), navigate: true);
            return;
        }

        $this->isProcessing = true;

        try {
            // Ekstraksi data terstruktur via GroqService
            $extracted = $groqService->extractFromText(
                text: trim($this->rawText),
                businessType: $business->business_type,
                businessId: $business->id,
                userId: auth()->id()
            );

            // Normalisasi items
            $items = [];
            foreach ($extracted['items'] ?? [] as $it) {
                $qty = (float) ($it['quantity'] ?? 1);
                $unitPrice = (float) ($it['price_per_unit'] ?? $it['unit_price'] ?? 0);
                $subtotal = (float) ($it['subtotal'] ?? ($qty * $unitPrice));

                $items[] = [
                    'item_name' => $it['item_name'] ?? 'Barang',
                    'quantity' => $qty,
                    'unit' => $it['unit'] ?? 'pcs',
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            $type = in_array($extracted['type'] ?? '', ['cash_out', 'cash_in'], true)
                ? $extracted['type']
                : 'cash_out';

            $defaultCategory = $type === 'cash_in' ? 'penjualan' : 'bahan_baku';
            $category = !empty($extracted['category']) ? $extracted['category'] : $defaultCategory;

            $this->draft = [
                'type' => $type,
                'category' => $category,
                'amount' => (float) ($extracted['total_amount'] ?? 0),
                'transaction_date' => $extracted['transaction_date'] ?? now()->toDateString(),
                'merchant_name' => $extracted['merchant_name'] ?? '',
                'notes' => $extracted['notes'] ?? trim($this->rawText),
                'confidence_score' => (float) ($extracted['confidence_score'] ?? 0.95),
                'needs_review' => (bool) ($extracted['needs_review'] ?? false),
                'unclear_fields' => $extracted['unclear_fields'] ?? [],
                'review_reason' => $extracted['review_reason'] ?? null,
                'items' => $items,
            ];

            // Pindah ke tampilan review
            $this->step = 'review';
        } catch (\Throwable $e) {
            Log::error('Text Transaction Extraction Failed: ' . $e->getMessage(), [
                'text' => $this->rawText,
            ]);
            $this->errorMessage = 'Gagal memproses catatan dengan AI: ' . $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Tambah baris barang baru di layar konfirmasi.
     */
    public function addItem(): void
    {
        $this->draft['items'][] = [
            'item_name' => '',
            'quantity' => 1,
            'unit' => 'pcs',
            'unit_price' => 0,
            'subtotal' => 0,
        ];
    }

    /**
     * Hapus baris barang.
     */
    public function removeItem(int $index): void
    {
        if (isset($this->draft['items'][$index])) {
            unset($this->draft['items'][$index]);
            $this->draft['items'] = array_values($this->draft['items']);
            $this->recalculateTotal();
        }
    }

    /**
     * Hitung ulang subtotal item ketika kuantitas atau harga satuan diubah.
     */
    public function updateItemSubtotal(int $index): void
    {
        if (isset($this->draft['items'][$index])) {
            $qty = (float) ($this->draft['items'][$index]['quantity'] ?? 0);
            $price = (float) ($this->draft['items'][$index]['unit_price'] ?? 0);
            $this->draft['items'][$index]['subtotal'] = $qty * $price;
            $this->recalculateTotal();
        }
    }

    /**
     * Hitung ulang total transaksi dari akumulasi subtotal barang.
     */
    public function recalculateTotal(): void
    {
        $sum = 0.0;
        foreach ($this->draft['items'] as $item) {
            $sum += (float) ($item['subtotal'] ?? 0);
        }

        if ($sum > 0) {
            $this->draft['amount'] = $sum;
        }
    }

    /**
     * Kembali ke formulir input teks.
     */
    public function resetToInput(): void
    {
        $this->step = 'input';
        $this->errorMessage = null;
        $this->draft = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 0,
            'transaction_date' => now()->toDateString(),
            'merchant_name' => '',
            'notes' => '',
            'confidence_score' => 1.0,
            'needs_review' => false,
            'unclear_fields' => [],
            'review_reason' => null,
            'items' => [],
        ];
    }

    /**
     * Simpan transaksi ke database.
     */
    public function saveTransaction()
    {
        $business = $this->getActiveBusiness();

        if (!$business) {
            session()->flash('message', 'Silakan daftarkan profil usaha terlebih dahulu.');
            return redirect()->route('onboarding');
        }

        $this->validate([
            'draft.type' => 'required|in:cash_out,cash_in',
            'draft.amount' => 'required|numeric|min:100',
            'draft.category' => 'required|string',
            'draft.transaction_date' => 'required|date|before_or_equal:today',
            'draft.notes' => 'nullable|string|max:500',
        ], [
            'draft.type.required' => 'Pilih jenis transaksi (kas keluar / kas masuk).',
            'draft.amount.required' => 'Nominal transaksi wajib diisi.',
            'draft.amount.min' => 'Nominal transaksi minimal Rp 100.',
            'draft.category.required' => 'Pilih kategori transaksi.',
            'draft.transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'draft.transaction_date.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
        ]);

        DB::transaction(function () use ($business) {
            $type = $this->draft['type'];
            $defaultNote = $type === 'cash_in' ? 'Pemasukan Kas via Teks' : 'Pengeluaran Kas via Teks';

            $transaction = $business->transactions()->create([
                'type' => $type,
                'category' => $this->draft['category'],
                'amount' => (float) $this->draft['amount'],
                'transaction_date' => $this->draft['transaction_date'],
                'source_type' => 'text',
                'notes' => trim($this->draft['notes'] ?: $this->draft['merchant_name'] ?: $defaultNote),
                'is_cash_settled' => true,
            ]);

            foreach ($this->draft['items'] as $item) {
                $name = trim($item['item_name'] ?? '');
                if ($name !== '') {
                    $transaction->items()->create([
                        'item_name' => $name,
                        'quantity' => max(0.01, (float) ($item['quantity'] ?? 1)),
                        'unit' => $item['unit'] ?? 'pcs',
                        'unit_price' => (float) ($item['unit_price'] ?? 0),
                        'subtotal' => (float) ($item['subtotal'] ?? 0),
                    ]);
                }
            }
        });

        $label = $this->draft['type'] === 'cash_in' ? 'Pemasukan' : 'Pengeluaran';
        session()->flash('message', "{$label} sebesar Rp " . number_format((float) $this->draft['amount'], 0, ',', '.') . ' berhasil dicatat!');

        return redirect()->route('transactions.index');
    }

    public function render()
    {
        $business = $this->getActiveBusiness();

        return view('livewire.create-expense-text-transaction', [
            'hasBusiness' => (bool) $business,
            'business' => $business,
        ]);
    }
}

