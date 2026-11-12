<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Transactions\DuplicateTransactionChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateTransactionCheckerTest extends TestCase
{
    use RefreshDatabase;

    private DuplicateTransactionChecker $checker;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new DuplicateTransactionChecker();

        $user = User::factory()->create();
        $this->business = Business::create([
            'user_id' => $user->id,
            'name' => 'Warung Uji Duplikasi',
            'business_type' => 'food_beverage',
        ]);
    }

    public function test_it_detects_potential_duplicate_within_threshold(): void
    {
        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
        ]);
        $tx->created_at = now()->subMinutes(3);
        $tx->saveQuietly();

        $draft = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
        ];

        $duplicate = $this->checker->findPotentialDuplicate($this->business, $draft, 10);

        $this->assertNotNull($duplicate);
        $this->assertEquals(50000, (float) $duplicate->amount);
        $this->assertEquals('bahan_baku', $duplicate->category);
    }

    public function test_it_ignores_transaction_older_than_threshold(): void
    {
        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
        ]);
        $tx->created_at = now()->subMinutes(15);
        $tx->saveQuietly();

        $draft = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
        ];

        $duplicate = $this->checker->findPotentialDuplicate($this->business, $draft, 10);

        $this->assertNull($duplicate);
    }

    public function test_it_ignores_transaction_with_different_nominal_or_category(): void
    {
        $tx = Transaction::create([
            'business_id' => $this->business->id,
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 50000,
            'transaction_date' => now()->toDateString(),
            'source_type' => 'text',
        ]);
        $tx->created_at = now()->subMinutes(2);
        $tx->saveQuietly();

        // Beda nominal
        $draftDifferentAmount = [
            'type' => 'cash_out',
            'category' => 'bahan_baku',
            'amount' => 75000,
        ];
        $this->assertNull($this->checker->findPotentialDuplicate($this->business, $draftDifferentAmount, 10));

        // Beda kategori
        $draftDifferentCategory = [
            'type' => 'cash_out',
            'category' => 'operasional',
            'amount' => 50000,
        ];
        $this->assertNull($this->checker->findPotentialDuplicate($this->business, $draftDifferentCategory, 10));
    }

    public function test_math_reconciliation_detects_exact_match(): void
    {
        $items = [
            ['item_name' => 'Beras 5kg', 'quantity' => 1, 'unit_price' => 70000, 'subtotal' => 70000],
            ['item_name' => 'Minyak Goreng 2L', 'quantity' => 1, 'unit_price' => 35000, 'subtotal' => 35000],
        ];

        $result = $this->checker->checkMathReconciliation(105000, $items);

        $this->assertTrue($result['has_items']);
        $this->assertTrue($result['is_matched']);
        $this->assertEquals(105000, $result['items_sum']);
        $this->assertEquals(0, $result['difference']);
    }

    public function test_math_reconciliation_detects_discrepancy(): void
    {
        $items = [
            ['item_name' => 'Beras 5kg', 'quantity' => 1, 'unit_price' => 70000, 'subtotal' => 70000],
            ['item_name' => 'Minyak Goreng 2L', 'quantity' => 1, 'unit_price' => 35000, 'subtotal' => 35000],
        ];

        // Total transaksi tertulis 100000 (selisih 5000 dengan items 105000)
        $result = $this->checker->checkMathReconciliation(100000, $items);

        $this->assertTrue($result['has_items']);
        $this->assertFalse($result['is_matched']);
        $this->assertEquals(105000, $result['items_sum']);
        $this->assertEquals(5000, $result['difference']);
        $this->assertTrue($result['is_over']);
    }

    public function test_math_reconciliation_without_items_is_valid(): void
    {
        $result = $this->checker->checkMathReconciliation(50000, []);

        $this->assertFalse($result['has_items']);
        $this->assertTrue($result['is_matched']);
    }
}
