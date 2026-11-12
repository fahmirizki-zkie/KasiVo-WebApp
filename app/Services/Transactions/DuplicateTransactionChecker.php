<?php

namespace App\Services\Transactions;

use App\Models\Business;
use App\Models\Transaction;

class DuplicateTransactionChecker
{
    /**
     * Cari transaksi kembar yang berpotensi merupakan input ganda tak sengaja.
     * Kriteria: business sama, tipe sama, nominal sama, kategori sama, dibuat dalam X menit terakhir.
     *
     * @param  array<string, mixed>  $draft
     */
    public function findPotentialDuplicate(Business $business, array $draft, int $thresholdMinutes = 10): ?Transaction
    {
        $amount = (float) ($draft['amount'] ?? 0);
        $type = $draft['type'] ?? 'cash_out';
        $category = $draft['category'] ?? '';

        if ($amount <= 0) {
            return null;
        }

        return Transaction::query()
            ->where('business_id', $business->id)
            ->where('type', $type)
            ->where('amount', $amount)
            ->where('category', $category)
            ->where('created_at', '>=', now()->subMinutes($thresholdMinutes))
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Hitung rekonsiliasi matematika antara jumlah subtotal rincian item dengan total transaksi.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function checkMathReconciliation(float $totalAmount, array $items): array
    {
        $itemsSum = 0.0;
        $hasItems = false;

        foreach ($items as $item) {
            $name = trim($item['item_name'] ?? '');
            if ($name !== '') {
                $hasItems = true;
                $qty = max(0.01, (float) ($item['quantity'] ?? 1));
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $subtotal = isset($item['subtotal']) && (float) $item['subtotal'] > 0
                    ? (float) $item['subtotal']
                    : ($qty * $unitPrice);

                $itemsSum += $subtotal;
            }
        }

        $difference = $itemsSum - $totalAmount;
        $isMatched = ! $hasItems || abs($difference) < 0.01;

        return [
            'has_items' => $hasItems,
            'is_matched' => $isMatched,
            'items_sum' => $itemsSum,
            'total_amount' => $totalAmount,
            'difference' => $difference,
            'is_over' => $difference > 0,
            'is_under' => $difference < 0,
            'formatted_items_sum' => 'Rp ' . number_format($itemsSum, 0, ',', '.'),
            'formatted_total_amount' => 'Rp ' . number_format($totalAmount, 0, ',', '.'),
            'formatted_difference' => 'Rp ' . number_format(abs($difference), 0, ',', '.'),
        ];
    }
}

