<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restock_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('item_name')->index();
            $table->decimal('buying_frequency_days', 5, 1)->nullable();
            $table->date('last_purchase_date')->nullable();
            $table->text('ai_explanation')->nullable();
            $table->string('status', 30)->default('insufficient_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restock_insights');
    }
};

