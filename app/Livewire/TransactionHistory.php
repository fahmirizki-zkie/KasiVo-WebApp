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

        // Pastikan transaksi yang dihapus hanya milik bisnis pengguna yang aktif
        $transaction = $business->transactions()->findOrFail($this->confirmingDeletionId);
        $transaction->delete();

        $this->confirmingDeletionId = null;
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

        return view('livewire.transaction-history', [
            'transactions' => $transactions,
            'totalCashIn' => (float) $totalCashIn,
            'totalCashOut' => (float) $totalCashOut,
            'netCashFlow' => (float) $netCashFlow,
            'hasBusiness' => true,
            'transactionToDelete' => $transactionToDelete,
        ]);
    }
}
