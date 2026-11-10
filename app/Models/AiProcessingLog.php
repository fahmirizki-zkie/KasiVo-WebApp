<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProcessingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'raw_input_type',
        'model_used',
        'raw_payload',
        'parsed_json',
        'confidence_flags',
        'status',
        'processing_time_ms',
        'error_message',
    ];

    protected $casts = [
        'parsed_json' => 'array',
        'confidence_flags' => 'array',
        'processing_time_ms' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

