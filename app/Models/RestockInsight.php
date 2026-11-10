<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestockInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'item_name',
        'buying_frequency_days',
        'last_purchase_date',
        'ai_explanation',
        'status',
    ];

    protected $casts = [
        'buying_frequency_days' => 'decimal:1',
        'last_purchase_date' => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

