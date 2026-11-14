<?php

namespace App\Livewire;

use App\Models\Business;
use App\Services\Ai\GeminiService;
use App\Services\Ai\GroqService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CreateIncomeTransaction extends Component
{
    use WithFileUploads;

    public $amount = '';
    public string $category = 'penjualan_tunai';
    public string $transaction_date = '';
    public string $notes = '';
    public string $source_type = 'text';

    public function mount(): void
    {
        $this->transaction_date = now()->toDateString();
    }

    /**
     * Ekstraksi foto nota penjualan menggunakan Gemini Vision dengan fallback ke Groq.
     *
     * @return array<string, mixed>
     */
    public function extractPhotoAi(string $base64Image, GeminiService $geminiService, GroqService $groqService): array
    {
        $business = auth()->user()?->businesses()->first();
        $mimeType = 'image/jpeg';

        if (str_starts_with($base64Image, 'data:image/')) {
            $end = strpos($base64Image, ';');
            if ($end !== false) {
                $mimeType = substr($base64Image, 5, $end - 5);
            }
        }

        $context = "Pencatatan pemasukan/penjualan kas masuk untuk usaha: " . ($business?->name ?? 'UMKM F&B');
        $extracted = null;

        if ($geminiService->isConfigured()) {
            try {
                $extracted = $geminiService->extractFromImage(
                    base64Image: $base64Image,
                    mimeType: $mimeType,
                    additionalContext: $context,
                    businessType: $business?->business_type,
                    businessId: $business?->id,
                    userId: auth()->id()
                );
            } catch (\Throwable $e) {
                Log::warning('Gemini vision income extraction failed: ' . $e->getMessage());
            }
        }

        if ($extracted === null) {
            try {
                $extracted = $groqService->extractFromImage(
                    base64Image: $base64Image,
                    mimeType: $mimeType,
                    additionalContext: $context,
                    businessType: $business?->business_type,
                    businessId: $business?->id,
                    userId: auth()->id()
                );
            } catch (\Throwable $e) {
                Log::warning('Groq vision income extraction failed: ' . $e->getMessage());
            }
        }

        $amount = (float) ($extracted['total_amount'] ?? 0);
        $items = $extracted['items'] ?? [];

        // Jika total_amount 0 tapi ada subtotal item, hitung otomatis jumlah subtotalnya
        if ($amount <= 0 && !empty($items)) {
            $sum = 0;
            foreach ($items as $it) {
                $sum += (float) ($it['subtotal'] ?? 0);
            }
            if ($sum > 0) {
                $amount = $sum;
            }
        }
        $merchant = trim((string) ($extracted['merchant_name'] ?? ''));
        $notes = !empty($extracted['notes']) ? $extracted['notes'] : ($merchant ? "Penjualan di {$merchant}" : 'Penjualan Nota Struk');
        $category = 'penjualan_tunai';
        if (!empty($extracted['category']) && in_array($extracted['category'], ['penjualan_tunai', 'penjualan_qris', 'penjualan'])) {
            $category = $extracted['category'];
        }

        $this->amount = $amount > 0 ? $amount : '';
        $this->notes = $notes;
        $this->category = $category;
        $this->transaction_date = $extracted['transaction_date'] ?? now()->toDateString();
        $this->source_type = 'photo';

        return [
            'amount' => $amount,
            'notes' => $notes,
            'category' => $category,
            'date' => $this->transaction_date,
            'items' => $extracted['items'] ?? [],
        ];
    }

    /**
     * Ekstraksi teks bebas penjualan menggunakan Groq / Gemini NLP.
     *
     * @return array<string, mixed>
     */
    public function extractTextAi(string $text, GroqService $groqService, GeminiService $geminiService): array
    {
        $business = auth()->user()?->businesses()->first();
        $extracted = null;

        try {
            $extracted = $groqService->extractFromText(
                text: $text,
                businessType: $business?->business_type,
                businessId: $business?->id,
                userId: auth()->id()
            );
        } catch (\Throwable $e) {
            if ($geminiService->isConfigured()) {
                try {
                    $extracted = $geminiService->extractFromText(
                        text: $text,
                        businessType: $business?->business_type,
                        businessId: $business?->id,
                        userId: auth()->id()
                    );
                } catch (\Throwable $ge) {
                    Log::warning('Gemini text income extraction failed: ' . $ge->getMessage());
                }
            }
        }

        $amount = (float) ($extracted['total_amount'] ?? 0);
        $items = $extracted['items'] ?? [];

        // Jika total_amount 0 tapi ada subtotal item, hitung otomatis jumlah subtotalnya
        if ($amount <= 0 && !empty($items)) {
            $sum = 0;
            foreach ($items as $it) {
                $sum += (float) ($it['subtotal'] ?? 0);
            }
            if ($sum > 0) {
                $amount = $sum;
            }
        }
        $notes = !empty($extracted['notes']) ? $extracted['notes'] : $text;
        $category = 'penjualan_tunai';
        if (!empty($extracted['category']) && in_array($extracted['category'], ['penjualan_tunai', 'penjualan_qris', 'penjualan'])) {
            $category = $extracted['category'];
        }

        $this->amount = $amount > 0 ? $amount : '';
        $this->notes = $notes;
        $this->category = $category;
        $this->transaction_date = $extracted['transaction_date'] ?? now()->toDateString();
        $this->source_type = 'text';

        return [
            'amount' => $amount,
            'notes' => $notes,
            'category' => $category,
            'date' => $this->transaction_date,
            'items' => $extracted['items'] ?? [],
        ];
    }

    /**
     * Tombol cepat penambah nominal (quick-add pills).
     */
    public function addAmount(int $val): void
    {
        $current = is_numeric($this->amount) ? (float) $this->amount : 0;
        $this->amount = $current + $val;
    }

    /**
     * Set nominal langsung.
     */
    public function setAmount(int $val): void
    {
        $this->amount = $val;
    }

    protected function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1000|max:100000000',
            'category' => 'required|string|in:penjualan,penjualan_tunai,penjualan_qris,lainnya',
            'transaction_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ];
    }

    protected array $messages = [
        'amount.required' => 'Nominal omzet penjualan wajib diisi.',
        'amount.numeric' => 'Nominal omzet harus berupa angka.',
        'amount.min' => 'Nominal omzet minimal Rp 1.000.',
        'amount.max' => 'Nominal omzet melebihi batas maksimum Rp 100.000.000.',
        'transaction_date.required' => 'Tanggal penjualan wajib diisi.',
        'transaction_date.date' => 'Format tanggal tidak valid.',
        'transaction_date.before_or_equal' => 'Tanggal transaksi tidak boleh melebihi hari ini.',
        'category.required' => 'Pilih kategori penjualan.',
        'category.in' => 'Kategori penjualan yang dipilih tidak valid.',
        'notes.max' => 'Catatan maksimal 500 karakter.',
    ];

    public function save()
    {
        $this->validate();

        $business = auth()->user()?->businesses()->first();

        if (!$business) {
            session()->flash('message', 'Silakan daftarkan profil usaha terlebih dahulu.');
            return redirect()->route('onboarding');
        }

        $business->transactions()->create([
            'type' => 'cash_in',
            'category' => $this->category,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date,
            'source_type' => $this->source_type,
            'notes' => trim($this->notes) ?: 'Rekap penjualan harian warung',
            'is_cash_settled' => true,
        ]);

        session()->flash('message', 'Penjualan sebesar Rp ' . number_format((float) $this->amount, 0, ',', '.') . ' berhasil dicatat!');

        return $this->redirect(route('transactions.index'), navigate: true);
    }

    public function render()
    {
        $business = auth()->user()?->businesses()->first();

        return view('livewire.create-income-transaction', [
            'business' => $business,
        ]);
    }
}


