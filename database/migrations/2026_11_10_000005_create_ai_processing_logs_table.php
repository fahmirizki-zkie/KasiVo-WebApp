<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_processing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('raw_input_type', 30);
            $table->string('model_used', 100);
            $table->text('raw_payload')->nullable();
            $table->jsonb('parsed_json')->nullable();
            $table->jsonb('confidence_flags')->nullable();
            $table->string('status', 30)->default('pending');
            $table->integer('processing_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_processing_logs');
    }
};

