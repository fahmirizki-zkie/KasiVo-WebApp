<?php

namespace App\Livewire\Concerns;

use App\Models\Business;
use App\Services\Transactions\DuplicateTransactionChecker;
use Illuminate\Support\Facades\Cache;

trait ValidatesTransactionDraft
{
    /**
     * Flag penanda modal peringatan transaksi kembar ditampilkan.
     */
    public bool $showDuplicateModal = false;

    /**
     * Rincian data transaksi sebelumnya yang terindikasi kembar.
     *
     * @var array<string, mixed>|null
     */
    public ?array $duplicateInfo = null;

    /**
     * Flag jika pengguna secara sadar mengonfirmasi bahwa ini bukan duplikat (transaksi baru).
     */
    public bool $duplicateConfirmed = false;

    /**
     * Data hasil rekonsiliasi matematika item vs total.
     *
     * @var array<string, mixed>|null
     */
    public ?array $mathReconciliation = null;

    /**
     * Flag anti-spam internal Livewire untuk mencegah double submission saat simpan.
     */
    public bool $isSavingTx = false;

    /**
     * Hitung ulang status rekonsiliasi matematika item vs total transaksi.
     */
    public function recalculateMathReconciliation(): void
    {
        $amount = (float) ($this->draft['amount'] ?? 0);
        $items = $this->draft['items'] ?? [];

        /** @var DuplicateTransactionChecker $checker */
        $checker = app(DuplicateTransactionChecker::class);
        $this->mathReconciliation = $checker->checkMathReconciliation($amount, $items);
    }

    /**
     * Aksi cepat pedagang: menyelaraskan total_amount dengan jumlah subtotal rincian barang.
     */
    public function syncTotalWithItemsSum(): void
    {
        if ($this->mathReconciliation && isset($this->mathReconciliation['items_sum'])) {
            $this->draft['amount'] = (float) $this->mathReconciliation['items_sum'];
            $this->recalculateMathReconciliation();
        }
    }

    /**
     * Tutup modal dan batalkan proses simpan (nol data tersimpan).
     */
    public function dismissDuplicateModal(): void
    {
        $this->showDuplicateModal = false;
        $this->isSavingTx = false;
    }

    /**
     * Pengguna sadar dan mengonfirmasi bahwa ini adalah transaksi baru (uang keluar 2 kali).
     */
    public function confirmDuplicateAndSave(): void
    {
        $this->duplicateConfirmed = true;
        $this->showDuplicateModal = false;

        if (method_exists($this, 'saveTransaction')) {
            app()->call([$this, 'saveTransaction']);
        }
    }

    /**
     * Evaluasi potensi duplikasi sebelum transaksi disimpan.
     * Mengembalikan true jika transaksi aman disimpan, false jika tertahan modal konfirmasi.
     */
    protected function checkDuplicateSafety(Business $business): bool
    {
        if ($this->duplicateConfirmed) {
            return true;
        }

        /** @var DuplicateTransactionChecker $checker */
        $checker = app(DuplicateTransactionChecker::class);
        $existing = $checker->findPotentialDuplicate($business, $this->draft, 10);

        if ($existing) {
            $this->duplicateInfo = [
                'id' => $existing->id,
                'time_ago' => $existing->created_at ? $existing->created_at->diffForHumans() : 'beberapa saat lalu',
                'time_exact' => $existing->created_at ? $existing->created_at->format('H:i') : '',
                'amount' => (float) $existing->amount,
                'formatted_amount' => 'Rp ' . number_format((float) $existing->amount, 0, ',', '.'),
                'category' => $existing->category,
                'notes' => $existing->notes,
            ];

            $this->showDuplicateModal = true;
            $this->isSavingTx = false;

            return false;
        }

        return true;
    }
}
