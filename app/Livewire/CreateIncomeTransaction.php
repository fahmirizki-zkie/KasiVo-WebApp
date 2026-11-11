<?php

namespace App\Livewire;

use App\Models\Business;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CreateIncomeTransaction extends Component
{
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

        return redirect()->route('transactions.index');
    }

    public function render()
    {
        $business = auth()->user()?->businesses()->first();

        return view('livewire.create-income-transaction', [
            'business' => $business,
        ]);
    }
}

