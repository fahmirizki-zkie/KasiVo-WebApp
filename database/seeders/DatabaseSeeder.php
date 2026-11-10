<?php

namespace Database\Seeders;

use App\Models\AiProcessingLog;
use App\Models\Business;
use App\Models\RestockInsight;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed data demo realistis untuk KasiVo (UMKM F&B).
     */
    public function run(): void
    {
        // 1. Akun Pengguna Demo
        $user = User::firstOrCreate(
            ['email' => 'demo@kasivo.id'],
            [
                'name' => 'Fahmi Rizki',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Profil Usaha Warung
        $business = Business::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Warung Makan Berkah Bahari',
                'business_type' => 'Warung Makan Pecel Lele & Ayam Goreng',
                'description' => 'Spesialis olahan pecel lele, ayam goreng krispi, bebek goreng bumbu hitam, dan aneka sambal Nusantara sejak 2022.',
            ]
        );

        // 3. Buat Transaksi Riil 14 Hari Terakhir
        $now = Carbon::now();

        for ($day = 13; $day >= 0; $day--) {
            $currentDate = $now->copy()->subDays($day);

            // A. KAS MASUK (Penjualan Harian)
            // Omzet antara Rp 900.000 s/d Rp 1.650.000 per hari
            $dailyIncome = rand(90, 165) * 10000;
            Transaction::create([
                'business_id' => $business->id,
                'type' => 'cash_in',
                'category' => 'penjualan',
                'amount' => $dailyIncome,
                'transaction_date' => $currentDate->toDateString(),
                'source_type' => ($day % 2 === 0) ? 'digital_import' : 'text',
                'notes' => 'Rekap penjualan harian warung',
                'is_cash_settled' => true,
            ]);

            // B. KAS KELUAR (Belanja Bahan Baku Tiap 2-3 Hari Sekali)
            if ($day % 2 === 0) {
                // Belanja Ayam & Daging
                $ayamQty = rand(5, 7);
                $ayamSubtotal = $ayamQty * 32000;

                $leleQty = rand(3, 5);
                $leleSubtotal = $leleQty * 26000;

                $totalBelanja = $ayamSubtotal + $leleSubtotal;

                $t = Transaction::create([
                    'business_id' => $business->id,
                    'type' => 'cash_out',
                    'category' => 'bahan_baku',
                    'amount' => $totalBelanja,
                    'transaction_date' => $currentDate->toDateString(),
                    'source_type' => 'photo',
                    'notes' => 'Nota belanja pasar daging & ayam',
                    'is_cash_settled' => true,
                ]);

                TransactionItem::create([
                    'transaction_id' => $t->id,
                    'item_name' => 'Ayam Potong Broiler',
                    'quantity' => $ayamQty,
                    'unit' => 'kg',
                    'unit_price' => 32000,
                    'subtotal' => $ayamSubtotal,
                ]);

                TransactionItem::create([
                    'transaction_id' => $t->id,
                    'item_name' => 'Ikan Lele Segar',
                    'quantity' => $leleQty,
                    'unit' => 'kg',
                    'unit_price' => 26000,
                    'subtotal' => $leleSubtotal,
                ]);
            }

            if ($day % 3 === 0) {
                // Belanja Minyak, Cabai & Bumbu
                $cabaiQty = rand(1, 3);
                $cabaiSubtotal = $cabaiQty * 35000;

                $minyakQty = rand(2, 4);
                $minyakSubtotal = $minyakQty * 17000;

                $totalBumbu = $cabaiSubtotal + $minyakSubtotal;

                $tBumbu = Transaction::create([
                    'business_id' => $business->id,
                    'type' => 'cash_out',
                    'category' => 'bahan_baku',
                    'amount' => $totalBumbu,
                    'transaction_date' => $currentDate->toDateString(),
                    'source_type' => 'voice',
                    'notes' => 'Belanja cabai rawit dan minyak goreng',
                    'is_cash_settled' => true,
                ]);

                TransactionItem::create([
                    'transaction_id' => $tBumbu->id,
                    'item_name' => 'Cabai Rawit Merah',
                    'quantity' => $cabaiQty,
                    'unit' => 'kg',
                    'unit_price' => 35000,
                    'subtotal' => $cabaiSubtotal,
                ]);

                TransactionItem::create([
                    'transaction_id' => $tBumbu->id,
                    'item_name' => 'Minyak Goreng Sawit',
                    'quantity' => $minyakQty,
                    'unit' => 'liter',
                    'unit_price' => 17000,
                    'subtotal' => $minyakSubtotal,
                ]);
            }

            // C. BIAYA OPERASIONAL (Gas LPG seminggu sekali)
            if ($day === 7 || $day === 1) {
                Transaction::create([
                    'business_id' => $business->id,
                    'type' => 'cash_out',
                    'category' => 'operasional',
                    'amount' => 44000,
                    'transaction_date' => $currentDate->toDateString(),
                    'source_type' => 'text',
                    'notes' => 'Tukar 2 tabung gas LPG 3 kg',
                    'is_cash_settled' => true,
                ]);
            }
        }

        // 4. Contoh Log Audit AI (Human-in-the-Loop)
        AiProcessingLog::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'raw_input_type' => 'photo',
            'model_used' => 'llama-3.2-11b-vision-preview',
            'raw_payload' => 'Foto struk pasar tradisional Pasar Pabean',
            'parsed_json' => [
                'type' => 'cash_out',
                'category' => 'bahan_baku',
                'total_amount' => 192000,
                'items' => [
                    ['item_name' => 'Ayam Potong Broiler', 'quantity' => 6.0, 'unit' => 'kg', 'unit_price' => 32000, 'subtotal' => 192000],
                ],
            ],
            'confidence_flags' => null,
            'status' => 'approved',
            'processing_time_ms' => 480,
            'created_at' => now()->subDay(),
        ]);

        AiProcessingLog::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'raw_input_type' => 'voice',
            'model_used' => 'whisper-large-v3',
            'raw_payload' => 'beli cabai rawit merah dua kilo tujuh puluh ribu',
            'parsed_json' => [
                'type' => 'cash_out',
                'category' => 'bahan_baku',
                'total_amount' => 70000,
                'items' => [
                    ['item_name' => 'Cabai Rawit Merah', 'quantity' => 2.0, 'unit' => 'kg', 'unit_price' => 35000, 'subtotal' => 70000],
                ],
            ],
            'confidence_flags' => null,
            'status' => 'edited',
            'processing_time_ms' => 320,
            'created_at' => now()->subHours(6),
        ]);

        // 5. Pola Saran Restock Bahan Baku
        RestockInsight::create([
            'business_id' => $business->id,
            'item_name' => 'Ayam Potong Broiler',
            'buying_frequency_days' => 2.0,
            'last_purchase_date' => now()->subDays(1)->toDateString(),
            'ai_explanation' => 'Bahan Ayam Potong rutin kamu beli rata-rata setiap 2 hari sekali sebanyak 5–7 kg. Terakhir belanja kemarin, disarankan periksa sisa persediaan untuk persiapan besok.',
            'status' => 'sufficient',
        ]);

        RestockInsight::create([
            'business_id' => $business->id,
            'item_name' => 'Cabai Rawit Merah',
            'buying_frequency_days' => 3.0,
            'last_purchase_date' => now()->subDays(2)->toDateString(),
            'ai_explanation' => 'Cabai Rawit biasanya dibeli tiap 3 hari sekali. Terakhir belanja 2 hari yang lalu, stok kemungkinan mulai menipis besok.',
            'status' => 'sufficient',
        ]);

        RestockInsight::create([
            'business_id' => $business->id,
            'item_name' => 'Minyak Goreng Sawit',
            'buying_frequency_days' => 4.0,
            'last_purchase_date' => now()->subDays(1)->toDateString(),
            'ai_explanation' => 'Minyak Goreng dibeli berkala tiap 4 hari sekali. Persediaan saat ini terpantau aman.',
            'status' => 'sufficient',
        ]);
    }
}
