<?php

namespace App\Livewire;

use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TransactionHistory extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dateRange = 'all';
    public string $type = 'all';
    public string $category = 'all';
    public ?int $confirmingDeletionId = null;

    // State untuk Detail & Koreksi Transaksi (KV-017)
    public ?int $selectedTransactionId = null;
    public bool $isEditing = false;
    public $edit_amount = '';
    public string $edit_category = '';
    public string $edit_transaction_date = '';
    public string $edit_notes = '';

    /**
     * Reset pagination saat filter berubah.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDateRange(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    /**
     * Tampilkan detail transaksi.
     */
    public function showDetail(int $id): void
    {
        $business = $this->getActiveBusiness();
        if (!$business) {
            return;
        }

        $transaction = $business->transactions()->with(['items', 'attachments'])->findOrFail($id);

        $this->selectedTransactionId = $transaction->id;
        $this->isEditing = false;

        $this->edit_amount = (float) $transaction->amount;
        $this->edit_category = $transaction->category;
        $this->edit_transaction_date = Carbon::parse($transaction->transaction_date)->format('Y-m-d');
        $this->edit_notes = $transaction->notes ?? '';
    }

    /**
     * Buka mode koreksi data.
     */
    public function startEditing(): void
    {
        $this->isEditing = true;
    }

    /**
     * Batalkan mode edit / tutup modal detail.
     */
    public function cancelDetail(): void
    {
        $this->selectedTransactionId = null;
        $this->isEditing = false;
        $this->resetValidation();
    }

    /**
     * Simpan koreksi data transaksi (Audit Timestamp).
     */
    public function updateTransaction(): void
    {
        if (!$this->selectedTransactionId) {
            return;
        }

        $this->validate([
            'edit_amount' => 'required|numeric|min:1000|max:100000000',
            'edit_category' => 'required|string',
            'edit_transaction_date' => 'required|date|before_or_equal:today',
            'edit_notes' => 'nullable|string|max:500',
        ], [
            'edit_amount.required' => 'Nominal transaksi wajib diisi.',
            'edit_amount.min' => 'Nominal transaksi minimal Rp 1.000.',
            'edit_amount.numeric' => 'Nominal harus berupa angka.',
            'edit_transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'edit_transaction_date.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
            'edit_category.required' => 'Kategori transaksi wajib dipilih.',
            'edit_notes.max' => 'Catatan maksimal 500 karakter.',
        ]);

        $business = $this->getActiveBusiness();
        if (!$business) {
            abort(403, 'Bisnis tidak ditemukan.');
        }

        $transaction = $business->transactions()->findOrFail($this->selectedTransactionId);

        $transaction->update([
            'amount' => $this->edit_amount,
            'category' => $this->edit_category,
            'transaction_date' => $this->edit_transaction_date,
            'notes' => trim($this->edit_notes) ?: null,
        ]);

        $this->isEditing = false;
        session()->flash('message', 'Transaksi berhasil dikoreksi dan tercatat di riwayat.');
    }

    /**
     * Tampilkan modal konfirmasi hapus transaksi.
     */
    public function confirmDelete(int $id): void
    {
        $this->confirmingDeletionId = $id;
    }

    /**
     * Batalkan dialog konfirmasi hapus.
     */
    public function cancelDelete(): void
    {
        $this->confirmingDeletionId = null;
    }

    /**
     * Eksekusi penghapusan transaksi (dengan pengecekan otorisasi).
     */
    public function deleteTransaction(): void
    {
        if (!$this->confirmingDeletionId) {
            return;
        }

        $business = $this->getActiveBusiness();
        if (!$business) {
            abort(403, 'Bisnis tidak ditemukan.');
        }

        $transaction = $business->transactions()->findOrFail($this->confirmingDeletionId);
        $transaction->delete();

        $this->confirmingDeletionId = null;
        if ($this->selectedTransactionId === $transaction->id) {
            $this->selectedTransactionId = null;
        }
        session()->flash('message', 'Transaksi berhasil dihapus dari pembukuan.');
    }

    /**
     * Ambil bisnis aktif milik pengguna yang sedang login.
     */
    protected function getActiveBusiness(): ?Business
    {
        return auth()->user()?->businesses()->first();
    }

    public function render()
    {
        $business = $this->getActiveBusiness();

        if (!$business) {
            return view('livewire.transaction-history', [
                'transactions' => collect(),
                'totalCashIn' => 0,
                'totalCashOut' => 0,
                'netCashFlow' => 0,
                'hasBusiness' => false,
                'transactionToDelete' => null,
                'selectedTransaction' => null,
            ]);
        }

        // Query dasar transaksi milik bisnis
        $baseQuery = $business->transactions()->with(['items', 'attachments']);

        // 1. Filter Rentang Tanggal
        if ($this->dateRange === 'today') {
            $baseQuery->whereDate('transaction_date', Carbon::today());
        } elseif ($this->dateRange === 'last_7_days') {
            $baseQuery->where('transaction_date', '>=', Carbon::today()->subDays(6));
        } elseif ($this->dateRange === 'this_month') {
            $baseQuery->where('transaction_date', '>=', Carbon::today()->startOfMonth());
        }

        // 2. Filter Tipe Kas (In / Out)
        if ($this->type !== 'all') {
            $baseQuery->where('type', $this->type);
        }

        // 3. Filter Kategori
        if ($this->category !== 'all') {
            $baseQuery->where('category', $this->category);
        }

        // 4. Filter Pencarian Teks
        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $baseQuery->where(function ($q) use ($term) {
                $q->where('notes', 'like', $term)
                  ->orWhereHas('items', function ($itemQuery) use ($term) {
                      $itemQuery->where('item_name', 'like', $term);
                  });
            });
        }

        // Hitung Mini Summary Bar berdasarkan filter aktif (sebelum paginasi)
        $summaryQuery = clone $baseQuery;
        $totalCashIn = (clone $summaryQuery)->where('type', 'cash_in')->sum('amount');
        $totalCashOut = (clone $summaryQuery)->where('type', 'cash_out')->sum('amount');
        $netCashFlow = $totalCashIn - $totalCashOut;

        // Ambil data terpaginasi (10 data per halaman, tanggal terbaru di atas)
        $transactions = $baseQuery
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Ambil objek transaksi yang sedang ingin dihapus untuk ditampilkan di modal
        $transactionToDelete = $this->confirmingDeletionId
            ? $business->transactions()->find($this->confirmingDeletionId)
            : null;

        // Ambil objek transaksi yang sedang dilihat detailnya
        $selectedTransaction = $this->selectedTransactionId
            ? $business->transactions()->with(['items', 'attachments'])->find($this->selectedTransactionId)
            : null;

        return view('livewire.transaction-history', [
            'transactions' => $transactions,
            'totalCashIn' => (float) $totalCashIn,
            'totalCashOut' => (float) $totalCashOut,
            'netCashFlow' => (float) $netCashFlow,
            'hasBusiness' => true,
            'transactionToDelete' => $transactionToDelete,
            'selectedTransaction' => $selectedTransaction,
        ]);
    }
}
