<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // cash_in or cash_out
            $table->string('category', 50); // penjualan, bahan_baku, operasional
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date')->index();
            $table->string('source_type', 30); // photo, voice, text, digital_import
            $table->text('notes')->nullable();
            $table->boolean('is_cash_settled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

